<?php

namespace App\Services;

use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PrescriptionPdfService
{
    /**
     * Generate a GPhC-compliant private prescription PDF.
     *
     * Stored at: storage/app/private/prescriptions/{prescription_number}.pdf
     * Returns the storage path.
     */
    public function generate(Prescription $prescription): string
    {
        $prescription->load(['patient', 'prescriber', 'product.treatment', 'consultation']);

        // Embed signature as base64 for DomPDF inline rendering
        $signatureBase64 = $this->loadSignatureBase64($prescription);

        // Pharmacy details (from config)
        $pharmacy = [
            'name'         => config('crm.pharmacy.name',         'Prescribe & Co'),
            'address'      => config('crm.pharmacy.address',      ''),
            'gphc_number'  => config('crm.pharmacy.gphc_number',  ''),
            'phone'        => config('crm.pharmacy.phone',        ''),
            'email'        => config('crm.pharmacy.email',        'pharmacy@prescribeandco.co.uk'),
        ];

        $pdf = Pdf::loadView('prescriptions.pdf', [
            'prescription'    => $prescription,
            'patient'         => $prescription->patient,
            'prescriber'      => $prescription->prescriber,
            'product'         => $prescription->product,
            'pharmacy'        => $pharmacy,
            'signatureBase64' => $signatureBase64,
            'generatedAt'     => now(),
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont'         => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false, // no external requests
            'dpi'                  => 150,
        ]);

        $filename = "{$prescription->prescription_number}.pdf";
        $path     = "prescriptions/{$filename}";

        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Stream a prescription PDF to the browser for in-browser viewing.
     */
    public function stream(Prescription $prescription): \Symfony\Component\HttpFoundation\Response
    {
        // Re-generate on-the-fly if no PDF stored yet (draft view)
        if (! $prescription->pdf_path || ! Storage::disk('private')->exists($prescription->pdf_path)) {
            $path = $this->generate($prescription);
            $prescription->update(['pdf_path' => $path]);
        }

        return response()->file(
            Storage::disk('private')->path($prescription->pdf_path),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline']
        );
    }

    /**
     * Force-download a prescription PDF.
     */
    public function download(Prescription $prescription): \Symfony\Component\HttpFoundation\Response
    {
        if (! $prescription->pdf_path || ! Storage::disk('private')->exists($prescription->pdf_path)) {
            $path = $this->generate($prescription);
            $prescription->update(['pdf_path' => $path]);
        }

        return Storage::disk('private')->download(
            $prescription->pdf_path,
            "{$prescription->prescription_number}.pdf"
        );
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function loadSignatureBase64(Prescription $prescription): ?string
    {
        $path = $prescription->effectiveSignaturePath();

        if (! $path) {
            return null;
        }

        try {
            $bytes = Storage::disk('private')->get($path);
            return 'data:image/png;base64,' . base64_encode($bytes);
        } catch (\Throwable) {
            return null;
        }
    }
}
