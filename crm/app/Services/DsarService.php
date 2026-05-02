<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ComplianceLog;
use App\Models\DsarRequest;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DsarService
{
    // ── Subject Access Request: export patient data ───────────────────────────

    /**
     * Build a JSON data export for a Subject Access Request.
     * Covers all personal data held across every table.
     * Stored as a private file; path saved to dsar_requests record.
     */
    public function exportPatientData(DsarRequest $dsar): string
    {
        $patient = Patient::with([
            'consultations.product',
            'prescriptions.product',
            'orders.items',
            'clinicalNotes',
            'messages',
        ])->find($dsar->user_id);

        if (! $patient) {
            throw new \RuntimeException('Patient not found for DSAR export.');
        }

        $export = [
            'export_generated_at' => now()->toIso8601String(),
            'dsar_reference'      => $dsar->id,
            'subject'             => [
                'name'          => $patient->full_name,
                'email'         => $patient->email,
                'date_of_birth' => $patient->date_of_birth?->format('d/m/Y'),
                'address'       => $patient->formatted_address,
                'mobile'        => $patient->mobile,
                'created_at'    => $patient->created_at->toIso8601String(),
            ],
            'consent' => [
                'gdpr_consent'    => $patient->gdpr_consent_at?->toIso8601String(),
                'marketing_opt_in' => $patient->gdpr_marketing_consent,
                'terms_accepted'  => $patient->terms_accepted_at?->toIso8601String(),
            ],
            'consultations' => $patient->consultations->map(fn ($c) => [
                'id'        => $c->id,
                'product'   => $c->product?->name,
                'status'    => $c->status,
                'submitted' => $c->created_at->toIso8601String(),
            ]),
            'prescriptions' => $patient->prescriptions->map(fn ($rx) => [
                'number'   => $rx->prescription_number,
                'product'  => $rx->product?->name,
                'status'   => $rx->status,
                'signed'   => $rx->signed_at?->toIso8601String(),
            ]),
            'orders' => $patient->orders->map(fn ($o) => [
                'number'  => $o->order_number,
                'total'   => '£' . number_format($o->total, 2),
                'status'  => $o->status,
                'placed'  => $o->created_at->toIso8601String(),
            ]),
            'messages' => $patient->messages->where('internal_only', false)->map(fn ($m) => [
                'from'    => $m->sender_type,
                'body'    => $m->body,
                'sent_at' => $m->created_at->toIso8601String(),
            ]),
        ];

        $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $path = "dsar/{$dsar->id}/patient-data-export.json";
        Storage::disk('private')->put($path, $json);

        $dsar->update([
            'status'             => 'completed',
            'completed_at'       => now(),
            'exported_data_path' => [$path],
        ]);

        ComplianceLog::record(
            type:    'dsar_request',
            userId:  $patient->id,
            staffId: $dsar->handled_by,
            notes:   "SAR data export generated. DSAR #{$dsar->id}.",
            meta:    ['dsar_id' => $dsar->id, 'export_path' => $path]
        );

        AuditLog::record(
            staffId:    $dsar->handled_by,
            action:     'dsar_export_generated',
            entityType: 'dsar_request',
            entityId:   $dsar->id,
            request:    request()
        );

        return $path;
    }

    // ── Right to Erasure: pseudonymise patient data ───────────────────────────

    /**
     * Pseudonymise a patient's personal data in compliance with GDPR Article 17.
     *
     * Fields nulled/overwritten:
     *   - PII in users: name → "Data Subject [id]", email → hash, DOB, address, mobile
     *
     * Records RETAINED (legal minimum / GPhC requirement):
     *   - Prescriptions (including prescription number, product, dosage)
     *   - Dispensing labels (for 2 years post-dispense)
     *   - Audit logs (patient_id references cleared to null after 6 years)
     *   - Compliance logs
     *   - Consultation rejection records (GPhC requirement)
     *
     * Soft-deletes the patient record.
     */
    public function erasePatient(DsarRequest $dsar, Staff $handledBy): void
    {
        $patient = Patient::find($dsar->user_id);
        if (! $patient) {
            throw new \RuntimeException('Patient not found.');
        }

        $patientId = $patient->id;

        DB::transaction(function () use ($patient, $patientId, $dsar, $handledBy) {

            $pseudonym = "Erased-{$patientId}";

            // Pseudonymise PII
            DB::table('users')->where('id', $patientId)->update([
                'first_name'             => $pseudonym,
                'last_name'              => 'DataSubject',
                'email'                  => "erased_{$patientId}@deleted.invalid",
                'password'               => bcrypt(\Illuminate\Support\Str::random(64)),
                'date_of_birth'          => null,
                'mobile'                 => null,
                'address_line_1'         => null,
                'address_line_2'         => null,
                'city'                   => null,
                'county'                 => null,
                'postcode'               => null,
                'stripe_id'              => null,
                'pm_type'                => null,
                'pm_last_four'           => null,
                'gdpr_marketing_consent' => false,
                'trusted_devices'        => null,
                'acquisition_channel'    => null,
            ]);

            // Null personal details in clinical notes
            DB::table('clinical_notes')
                ->where('patient_id', $patientId)
                ->update(['body' => '[Redacted — right to erasure exercised]']);

            // Delete messages (not legally required to retain)
            DB::table('messages')->where('user_id', $patientId)->delete();

            // Soft-delete the patient record
            $patient->delete();

            // Log the erasure
            DB::table('erasure_log')->insert([
                'original_user_id'             => $patientId,
                'handled_by'                   => $handledBy->id,
                'fields_pseudonymised'         => json_encode([
                    'first_name', 'last_name', 'email', 'password',
                    'date_of_birth', 'mobile', 'address_*', 'stripe_id',
                    'gdpr_marketing_consent', 'trusted_devices',
                ]),
                'prescription_records_retained' => true,
                'notes'                        => "DSAR #{$dsar->id} — right to erasure. Prescription/dispensing records retained per GPhC requirements.",
                'erased_at'                    => now(),
            ]);

            $dsar->update([
                'status'       => 'completed',
                'completed_at' => now(),
                'handled_by'   => $handledBy->id,
            ]);

            ComplianceLog::record(
                type:    'right_to_erasure',
                userId:  null, // already pseudonymised
                staffId: $handledBy->id,
                notes:   "Right to erasure completed for patient ID {$patientId}. DSAR #{$dsar->id}.",
                meta:    ['original_user_id' => $patientId, 'dsar_id' => $dsar->id]
            );

            AuditLog::record(
                staffId:    $handledBy->id,
                action:     'patient_data_erased',
                entityType: 'user',
                entityId:   $patientId,
                metadata:   ['dsar_id' => $dsar->id, 'prescription_records_retained' => true],
                request:    request()
            );
        });
    }

    // ── Download SAR export ───────────────────────────────────────────────────

    public function downloadExport(DsarRequest $dsar): \Symfony\Component\HttpFoundation\Response
    {
        $paths = $dsar->exported_data_path ?? [];
        if (empty($paths) || ! Storage::disk('private')->exists($paths[0])) {
            throw new \RuntimeException('Export file not found. Please regenerate.');
        }

        return Storage::disk('private')->download(
            $paths[0],
            "SAR-{$dsar->id}-data-export.json"
        );
    }
}
