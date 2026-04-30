<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;

class PatientTimelineController extends Controller
{
    /**
     * Chronological timeline of every patient touchpoint.
     *
     * Merges: consultations, prescriptions, orders, messages,
     * clinical notes, 2FA trigger events, audit log entries.
     *
     * GET /patients/{patient}/timeline
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'consultations',
            'prescriptions',
            'orders',
            'clinicalNotes.staff',
        ]);

        // Merge events into a unified collection
        $events = collect();

        foreach ($patient->consultations as $item) {
            $events->push([
                'type'  => 'consultation',
                'date'  => $item->created_at,
                'label' => 'Consultation submitted',
                'meta'  => $item->product?->name,
                'status' => $item->status,
                'link'  => route('consultations.show', $item),
            ]);
        }

        foreach ($patient->prescriptions as $item) {
            $events->push([
                'type'  => 'prescription',
                'date'  => $item->created_at,
                'label' => 'Prescription created',
                'meta'  => "#{$item->prescription_number}",
                'status' => $item->status,
                'link'  => null,
            ]);
        }

        foreach ($patient->orders as $item) {
            $events->push([
                'type'  => 'order',
                'date'  => $item->created_at,
                'label' => 'Order placed',
                'meta'  => "#{$item->id}",
                'status' => $item->status,
                'link'  => null,
            ]);
        }

        foreach ($patient->clinicalNotes as $item) {
            $events->push([
                'type'  => 'note',
                'date'  => $item->created_at,
                'label' => 'Clinical note added',
                'meta'  => "by {$item->staff?->full_name}",
                'status' => null,
                'link'  => null,
            ]);
        }

        $events = $events->sortByDesc('date')->values();

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'patient_timeline_viewed',
            entityType: 'patient',
            entityId:   $patient->id,
            request:    request()
        );

        return view('patients.timeline', compact('patient', 'events'));
    }
}
