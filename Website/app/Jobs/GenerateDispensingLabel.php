<?php

namespace App\Jobs;

use App\Models\DispensingLabel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateDispensingLabel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public int    $labelId,
        public string $format = 'standard' // 'standard' or 'branded'
    ) {}

    public function handle(): void
    {
        $label = DispensingLabel::with(['prescription.product', 'dispensedBy'])
            ->findOrFail($this->labelId);

        if ($label->pdf_path && Storage::disk('private')->exists($label->pdf_path)) {
            return; // Already generated
        }

        $view = $this->format === 'branded'
            ? 'labels.pdf-branded'
            : 'labels.pdf-standard';

        $pharmacy = [
            'name'        => config('pharmacy.name'),
            'address'     => config('pharmacy.address'),
            'gphc_number' => config('pharmacy.gphc_number'),
        ];

        $pdf = Pdf::loadView($view, compact('label', 'pharmacy'))
            ->setPaper([0, 0, 198.43, 99.21], 'landscape') // 70mm × 35mm in points
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);

        $path = "labels/label-{$label->id}.pdf";
        Storage::disk('private')->put($path, $pdf->output());

        $label->update(['pdf_path' => $path]);
    }
}
