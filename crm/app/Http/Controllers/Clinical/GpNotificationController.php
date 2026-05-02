<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GpNotification;
use App\Models\Patient;
use App\Services\PrescriptionPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GpNotificationController extends Controller
{
    /**
     * Log that a GP has been notified about a patient.
     * POST /patients/{patient}/gp-notifications
     */
    public function store(Request $request, Patient $patient)
    {
        $request->validate([
            'method'       => ['required', 'in:letter,email,phone,fax,other'],
            'notes'        => ['required', 'string', 'max:2000'],
            'prescription_id' => ['nullable', 'exists:prescriptions,id'],
        ]);

        $notification = GpNotification::create([
            'patient_id'      => $patient->id,
            'staff_id'        => Auth::guard('staff')->id(),
            'method'          => $request->method,
            'notes'           => $request->notes,
            'prescription_id' => $request->prescription_id,
            'notified_at'     => now(),
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'gp_notified',
            entityType: 'patient',
            entityId:   $patient->id,
            metadata:   ['method' => $request->method, 'gp_surgery' => $patient->gpSurgery?->name],
            request:    $request
        );

        return back()->with('success', 'GP notification recorded.');
    }

    /**
     * Generate a pre-populated GP notification letter PDF.
     * GET /patients/{patient}/gp-letter?prescription_id=...
     */
    public function generateLetter(Request $request, Patient $patient)
    {
        $prescription = null;
        if ($request->prescription_id) {
            $prescription = \App\Models\Prescription::with(['product', 'prescriber'])
                ->findOrFail($request->prescription_id);
        }

        $staff    = Auth::guard('staff')->user();
        $surgery  = $patient->gpSurgery;

        $pharmacy = [
            'name'        => config('crm.pharmacy.name'),
            'address'     => config('crm.pharmacy.address'),
            'gphc_number' => config('crm.pharmacy.gphc_number'),
            'email'       => config('crm.pharmacy.email'),
            'phone'       => config('crm.pharmacy.phone'),
        ];

        $pdf = Pdf::loadView('clinical.gp-letter', compact(
            'patient', 'prescription', 'staff', 'surgery', 'pharmacy'
        ))
        ->setPaper('a4', 'portrait')
        ->setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'gp_letter_generated',
            entityType: 'patient',
            entityId:   $patient->id,
            metadata:   ['prescription_id' => $prescription?->id],
            request:    $request
        );

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"gp-letter-{$patient->id}.pdf\"",
        ]);
    }
}
