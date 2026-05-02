<?php

namespace App\Jobs;

use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePrescriptionPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public int $prescriptionId) {}

    public function handle(): void
    {
        $prescription = Prescription::with(['patient', 'product', 'prescriber'])
            ->findOrFail($this->prescriptionId);

        if ($prescription->pdf_path && Storage::disk('private')->exists($prescription->pdf_path)) {
            return; // Already generated
        }

        $pharmacy = [
            'name'        => config('pharmacy.name'),
            'address'     => config('pharmacy.address'),
            'gphc_number' => config('pharmacy.gphc_number'),
        ];

        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription', 'pharmacy'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);

        $path = "prescriptions/{$prescription->prescription_number}.pdf";
        Storage::disk('private')->put($path, $pdf->output());

        $prescription->update(['pdf_path' => $path]);
    }
}
