<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DispensingLabel;
use App\Models\Prescription;
use App\Models\Staff;
use App\Models\StockBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DispensingLabelService
{
    // ── Create label from prescription ────────────────────────────────────────

    /**
     * Create a dispensing label record for a prescription.
     * The prescription must be in 'sent_to_dispense' status.
     *
     * @throws \RuntimeException
     */
    public function createLabel(
        Prescription $prescription,
        Staff        $dispenser,
        ?StockBatch  $batch  = null,
        string       $format = 'standard'
    ): DispensingLabel {
        if ($prescription->status !== Prescription::STATUS_SENT_TO_DISPENSE) {
            throw new \RuntimeException('Prescription must be in "Sent to Dispense" status before a label can be generated.');
        }

        $product  = $prescription->product;
        $patient  = $prescription->patient;
        $pharmacy = [
            'name'        => config('crm.pharmacy.name'),
            'address'     => config('crm.pharmacy.address'),
            'gphc_number' => config('crm.pharmacy.gphc_number'),
        ];

        $label = DispensingLabel::create([
            'prescription_id'     => $prescription->id,
            'product_id'          => $product->id,
            'dispensed_by'        => $dispenser->id,
            'patient_id'          => $patient->id,
            'stock_batch_id'      => $batch?->id,
            'batch_number'        => $batch?->batch_number,
            'expiry_date'         => $batch?->expiry_date,

            // Immutable content snapshot
            'patient_name'        => $patient->full_name,
            'medication_name'     => $product->name,
            'medication_strength' => $product->strength ?? null,
            'medication_form'     => $product->form     ?? null,
            'dosage_instructions' => $prescription->dosage_instructions,
            'dispensing_date'     => today(),
            'pharmacy_name'       => $pharmacy['name'],
            'pharmacy_address'    => $pharmacy['address'],
            'pharmacy_gphc_number' => $pharmacy['gphc_number'],
            'dispensed_by_name'   => $dispenser->full_name,
            'dispensed_by_gphc'   => $dispenser->gphc_number,
            'cold_chain'          => (bool) ($product->cold_chain ?? false),
            'format'              => $format,
            'dispensed_at'        => now(),
        ]);

        // Generate PDF immediately
        $pdfPath = $this->generatePdf($label);
        $label->update(['pdf_path' => $pdfPath]);

        // Deduct from batch stock if provided
        if ($batch) {
            $batch->decrement('quantity_remaining');
        }

        AuditLog::record(
            staffId:    $dispenser->id,
            action:     'dispensing_label_created',
            entityType: 'dispensing_label',
            entityId:   $label->id,
            metadata:   [
                'prescription_number' => $prescription->prescription_number,
                'batch_number'        => $batch?->batch_number,
                'cold_chain'          => $label->cold_chain,
            ],
            request: request()
        );

        return $label;
    }

    // ── Batch create labels ───────────────────────────────────────────────────

    /**
     * Create labels for multiple prescriptions in one operation.
     * Used from the dispensing queue "Dispense Selected" action.
     */
    public function batchCreate(
        array  $prescriptionIds,
        Staff  $dispenser,
        string $format = 'standard'
    ): array {
        $results = ['created' => [], 'failed' => []];

        foreach ($prescriptionIds as $id) {
            try {
                $rx    = Prescription::with(['product', 'patient'])->findOrFail($id);
                $batch = StockBatch::available()
                    ->where('product_id', $rx->product_id)
                    ->orderBy('expiry_date')   // FEFO — first-expiry, first-out
                    ->first();

                $label = $this->createLabel($rx, $dispenser, $batch, $format);
                $results['created'][] = $label->id;
            } catch (\Throwable $e) {
                $results['failed'][] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        return $results;
    }

    // ── Sign off dispensing & advance prescription ────────────────────────────

    /**
     * Mark a label as dispensed and advance prescription to 'dispensed'.
     */
    public function signOff(DispensingLabel $label, Staff $dispenser): void
    {
        $label->update(['printed' => true, 'printed_at' => now()]);

        $label->prescription->transitionTo(Prescription::STATUS_DISPENSED, $dispenser);

        AuditLog::record(
            staffId:    $dispenser->id,
            action:     'dispensing_signed_off',
            entityType: 'dispensing_label',
            entityId:   $label->id,
            metadata:   ['prescription_number' => $label->prescription->prescription_number],
            request:    request()
        );
    }

    // ── PDF generation ────────────────────────────────────────────────────────

    /**
     * Generate the 70×35mm label PDF.
     * Stored at: storage/app/private/labels/{label_id}_{format}.pdf
     */
    public function generatePdf(DispensingLabel $label): string
    {
        $view = $label->format === 'branded'
            ? 'labels.pdf-branded'
            : 'labels.pdf-standard';

        $pdf = Pdf::loadView($view, ['label' => $label])
            // 70mm × 35mm in points (1mm ≈ 2.8346pt)
            ->setPaper([0, 0, 198.43, 99.21])
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'dpi'                  => 203, // thermal printer DPI
            ]);

        $filename = "labels/{$label->id}_{$label->format}.pdf";
        Storage::disk('private')->put($filename, $pdf->output());

        return $filename;
    }

    // ── Stream / download ─────────────────────────────────────────────────────

    public function stream(DispensingLabel $label): \Symfony\Component\HttpFoundation\Response
    {
        if (! $label->pdf_path || ! Storage::disk('private')->exists($label->pdf_path)) {
            $path = $this->generatePdf($label);
            $label->update(['pdf_path' => $path]);
        }

        return response()->file(
            Storage::disk('private')->path($label->pdf_path),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    public function download(DispensingLabel $label): \Symfony\Component\HttpFoundation\Response
    {
        if (! $label->pdf_path || ! Storage::disk('private')->exists($label->pdf_path)) {
            $path = $this->generatePdf($label);
            $label->update(['pdf_path' => $path]);
        }

        return Storage::disk('private')->download(
            $label->pdf_path,
            "label-{$label->prescription->prescription_number}.pdf"
        );
    }

    // ── Near-expiry stock check ───────────────────────────────────────────────

    /**
     * Returns near-expiry batches for a given product at dispensing time.
     * Used to show an alert in the dispensing queue UI.
     */
    public function nearExpiryBatches(int $productId, int $thresholdDays = 90): \Illuminate\Database\Eloquent\Collection
    {
        return StockBatch::where('product_id', $productId)
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($thresholdDays))
            ->orderBy('expiry_date')
            ->get();
    }
}
