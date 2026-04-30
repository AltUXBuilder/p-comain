<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrescriptionController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $prescriptions = auth()->user()
            ->prescriptions()
            ->with(['product.treatment.category'])
            ->latest()
            ->paginate(10);

        return view('patient.prescriptions', compact('prescriptions'));
    }

    public function show(Prescription $prescription): \Illuminate\View\View
    {
        $this->authorise($prescription);
        $prescription->load(['product', 'prescriber', 'consultation', 'dispensingLabel']);
        return view('patient.prescription-detail', compact('prescription'));
    }

    public function download(Prescription $prescription): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorise($prescription);

        if (!$prescription->pdf_path || !Storage::disk('private')->exists($prescription->pdf_path)) {
            return back()->with('error', 'Prescription PDF is not yet available.');
        }

        return Storage::disk('private')->download(
            $prescription->pdf_path,
            'Prescription-' . $prescription->prescription_number . '.pdf'
        );
    }

    private function authorise(Prescription $prescription): void
    {
        if ($prescription->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
