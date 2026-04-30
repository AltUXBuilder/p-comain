<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PrescriptionService
{
    public function __construct(
        private PrescriptionPdfService $pdfService
    ) {}

    // ── Build ─────────────────────────────────────────────────────────────────

    /**
     * Create a draft prescription from an approved consultation.
     * Called from ConsultationController::approve() — replaces the stub there.
     */
    public function buildFromConsultation(Consultation $consultation, Staff $prescriber): Prescription
    {
        $product = $consultation->product;

        return Prescription::create([
            'consultation_id'       => $consultation->id,
            'patient_id'            => $consultation->patient_id,
            'prescriber_id'         => $prescriber->id,
            'product_id'            => $consultation->product_id,
            'status'                => Prescription::STATUS_DRAFT,
            // Pre-fill dosage from product defaults (prescriber can edit before signing)
            'dosage_instructions'   => $product?->dosage_instructions ?? '',
            'quantity'              => $product?->default_quantity ?? '',
            'is_repeat'             => (bool) ($product?->is_subscription ?? false),
            'repeat_interval_days'  => $product?->repeat_interval_days ?? null,
        ]);
    }

    // ── Sign & approve ────────────────────────────────────────────────────────

    /**
     * Sign a prescription:
     *   1. Validate prescriber has a saved signature (or override PNG was provided)
     *   2. Generate the GPhC-compliant PDF
     *   3. Advance status to approved
     *   4. Schedule repeat if applicable
     */
    public function sign(
        Prescription $prescription,
        Staff $prescriber,
        ?string $signatureOverridePng = null  // base64 PNG from canvas re-draw
    ): Prescription {
        // Validate status
        if (! $prescription->canTransitionTo(Prescription::STATUS_APPROVED)) {
            throw new \RuntimeException('Prescription cannot be signed in its current status.');
        }

        // Handle signature override
        if ($signatureOverridePng) {
            $overridePath = $this->saveSignatureOverride($prescription, $signatureOverridePng, $prescriber);
            $prescription->update(['signature_override_path' => $overridePath]);

            AuditLog::record(
                staffId:    $prescriber->id,
                action:     'prescription_signature_override',
                entityType: 'prescription',
                entityId:   $prescription->id,
                metadata:   ['path' => $overridePath],
                request:    request()
            );
        }

        if (! $prescription->hasSignature()) {
            throw new \RuntimeException('No signature on file. Please save your signature in Profile Settings before signing prescriptions.');
        }

        // Generate PDF
        $pdfPath = $this->pdfService->generate($prescription);
        $prescription->update(['pdf_path' => $pdfPath]);

        // Advance to approved
        $prescription->transitionTo(Prescription::STATUS_APPROVED, $prescriber);

        // Schedule repeat if applicable
        if ($prescription->is_repeat && $prescription->repeat_interval_days) {
            $prescription->update([
                'next_repeat_due' => now()->addDays($prescription->repeat_interval_days),
            ]);
        }

        return $prescription->fresh();
    }

    // ── Batch sign ────────────────────────────────────────────────────────────

    /**
     * Sign multiple pending_review prescriptions at once.
     * Each must be individually signable (signature on file, correct status).
     */
    public function batchSign(array $prescriptionIds, Staff $prescriber): array
    {
        $results = ['signed' => [], 'failed' => []];

        foreach ($prescriptionIds as $id) {
            try {
                $rx = Prescription::findOrFail($id);
                $this->sign($rx, $prescriber);
                $results['signed'][] = $rx->prescription_number;
            } catch (\Throwable $e) {
                $results['failed'][] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        AuditLog::record(
            staffId:    $prescriber->id,
            action:     'prescription_batch_signed',
            entityType: 'prescription',
            entityId:   null,
            metadata:   $results,
            request:    request()
        );

        return $results;
    }

    // ── Send to dispense ──────────────────────────────────────────────────────

    public function sendToDispense(Prescription $prescription, Staff $actor): Prescription
    {
        $prescription->transitionTo(Prescription::STATUS_SENT_TO_DISPENSE, $actor);
        return $prescription;
    }

    // ── Repeat scheduling ─────────────────────────────────────────────────────

    /**
     * Generate the next repeat prescription from a dispensed one.
     * Called by the scheduler (App\Console\Commands\GenerateRepeatPrescriptions).
     */
    public function generateRepeat(Prescription $parent): Prescription
    {
        if (! $parent->is_repeat || ! $parent->repeat_interval_days) {
            throw new \RuntimeException('This prescription is not configured for repeats.');
        }

        $child = Prescription::create([
            'consultation_id'       => $parent->consultation_id,
            'patient_id'            => $parent->patient_id,
            'prescriber_id'         => $parent->prescriber_id,
            'product_id'            => $parent->product_id,
            'status'                => Prescription::STATUS_PENDING_REVIEW,
            'dosage_instructions'   => $parent->dosage_instructions,
            'quantity'              => $parent->quantity,
            'legal_wording'         => $parent->legal_wording,
            'is_repeat'             => true,
            'repeat_interval_days'  => $parent->repeat_interval_days,
            'repeat_parent_id'      => $parent->id,
            'next_repeat_due'       => now()->addDays($parent->repeat_interval_days),
        ]);

        AuditLog::record(
            staffId:    null,
            action:     'prescription_repeat_generated',
            entityType: 'prescription',
            entityId:   $child->id,
            metadata:   ['parent_id' => $parent->id, 'prescription_number' => $child->prescription_number],
            request:    request()
        );

        return $child;
    }

    // ── Private prescription register export ──────────────────────────────────

    /**
     * Export the private prescription register as CSV.
     * GPhC inspection ready: prescriber, GPhC number, patient, product, date.
     */
    public function exportRegister(
        ?\DateTime $from = null,
        ?\DateTime $to = null,
        ?int $prescriberId = null,
        ?int $categoryId = null
    ): string {
        $query = Prescription::with(['patient', 'prescriber', 'product.treatment.category'])
            ->whereIn('status', [
                Prescription::STATUS_APPROVED,
                Prescription::STATUS_SENT_TO_DISPENSE,
                Prescription::STATUS_DISPENSED,
                Prescription::STATUS_ARCHIVED,
            ])
            ->orderBy('signed_at', 'asc');

        if ($from) {
            $query->where('signed_at', '>=', $from);
        }
        if ($to) {
            $query->where('signed_at', '<=', Carbon::instance($to)->endOfDay());
        }
        if ($prescriberId) {
            $query->where('prescriber_id', $prescriberId);
        }
        if ($categoryId) {
            $query->whereHas('product.treatment.category', fn ($q) => $q->where('id', $categoryId));
        }

        $rows = [
            ['Prescription Number', 'Date Signed', 'Patient Name', 'Patient DOB', 'Medication', 'Strength/Form', 'Quantity', 'Dosage Instructions', 'Prescriber Name', 'Prescriber GPhC', 'Status', 'Repeat'],
        ];

        foreach ($query->cursor() as $rx) {
            $rows[] = [
                $rx->prescription_number,
                $rx->signed_at?->format('d/m/Y'),
                $rx->patient?->full_name,
                $rx->patient?->date_of_birth?->format('d/m/Y'),
                $rx->product?->name,
                ($rx->product?->strength ?? '') . ' ' . ($rx->product?->form ?? ''),
                $rx->quantity,
                $rx->dosage_instructions,
                $rx->prescriber_name,
                $rx->prescriber_gphc_number,
                $rx->statusLabel(),
                $rx->is_repeat ? 'Yes' : 'No',
            ];
        }

        // Build CSV string
        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        return $csv;
    }

    // ── Signature helpers ─────────────────────────────────────────────────────

    /**
     * Save a prescriber's signature PNG from base64 canvas data.
     * Used by the signature capture flow in Profile settings.
     */
    public function savePrescriberSignature(Staff $prescriber, string $base64Png): string
    {
        $path = "signatures/{$prescriber->id}.png";
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Png));
        Storage::disk('private')->put($path, $data);

        $prescriber->update([
            'signature_path'   => $path,
            'signature_set_at' => now(),
        ]);

        AuditLog::record(
            staffId:    $prescriber->id,
            action:     'signature_saved',
            entityType: 'staff',
            entityId:   $prescriber->id,
            request:    request()
        );

        return $path;
    }

    private function saveSignatureOverride(Prescription $prescription, string $base64Png, Staff $prescriber): string
    {
        $path = "signatures/overrides/{$prescriber->id}_{$prescription->id}_" . time() . '.png';
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Png));
        Storage::disk('private')->put($path, $data);
        return $path;
    }
}
