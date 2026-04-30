<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Patient;
use App\Models\GpSurgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Advanced patient search / index.
     *
     * GET /patients
     */
    public function index(Request $request)
    {
        $query = Patient::query()->with(['gpSurgery', 'riskFlaggedBy']);

        // ── Text search ──────────────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // ── DOB ──────────────────────────────────────────────────────────
        if ($dob = $request->input('dob')) {
            $query->whereDate('date_of_birth', $dob);
        }

        // ── Flags ─────────────────────────────────────────────────────────
        if ($request->boolean('risk_flagged')) {
            $query->where('risk_flagged', true);
        }

        if ($request->boolean('do_not_treat')) {
            $query->where('do_not_treat', true);
        }

        if ($request->boolean('deceased')) {
            $query->where('deceased', true);
        } else {
            // By default, exclude deceased from main list
            $query->where('deceased', false);
        }

        // ── Subscription status ───────────────────────────────────────────
        if ($subStatus = $request->input('subscription_status')) {
            $query->whereHas('subscriptions', function ($q) use ($subStatus) {
                $q->where('stripe_status', $subStatus);
            });
        }

        // ── Prescription status filter ────────────────────────────────────
        if ($rxStatus = $request->input('prescription_status')) {
            $query->whereHas('prescriptions', function ($q) use ($rxStatus) {
                $q->where('status', $rxStatus);
            });
        }

        // ── Order status ──────────────────────────────────────────────────
        if ($orderStatus = $request->input('order_status')) {
            $query->whereHas('orders', function ($q) use ($orderStatus) {
                $q->where('status', $orderStatus);
            });
        }

        // ── Treatment category ────────────────────────────────────────────
        if ($category = $request->input('category')) {
            $query->whereHas('consultations.product.treatment.category', function ($q) use ($category) {
                $q->where('id', $category);
            });
        }

        // ── Sort ──────────────────────────────────────────────────────────
        $sortField = $request->input('sort', 'created_at');
        $sortDir   = $request->input('dir', 'desc');

        $allowedSorts = ['last_name', 'first_name', 'email', 'created_at', 'date_of_birth'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $patients = $query->paginate(25)->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'filters'  => $request->only([
                'search', 'dob', 'risk_flagged', 'do_not_treat', 'deceased',
                'subscription_status', 'prescription_status', 'order_status',
                'category', 'sort', 'dir',
            ]),
        ]);
    }

    /**
     * Patient record detail page.
     *
     * GET /patients/{patient}
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'consultations.product.treatment',
            'prescriptions',
            'orders',
            'clinicalNotes.staff',
            'gpSurgery',
            'riskFlaggedBy',
            'doNotTreatSetBy',
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'patient_record_viewed',
            entityType: 'patient',
            entityId:   $patient->id,
            request:    request()
        );

        return view('patients.show', compact('patient'));
    }

    /**
     * Edit patient demographics.
     */
    public function edit(Patient $patient)
    {
        $surgeries = GpSurgery::orderBy('name')->get();
        return view('patients.edit', compact('patient', 'surgeries'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email,' . $patient->id],
            'mobile'          => ['nullable', 'string', 'max:20'],
            'date_of_birth'   => ['nullable', 'date'],
            'address_line_1'  => ['nullable', 'string', 'max:255'],
            'address_line_2'  => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'county'          => ['nullable', 'string', 'max:100'],
            'postcode'        => ['nullable', 'string', 'max:10'],
            'gp_surgery_id'   => ['nullable', 'exists:gp_surgeries,id'],
            'identity_verified' => ['boolean'],
        ]);

        $patient->update($validated);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'patient_record_updated',
            entityType: 'patient',
            entityId:   $patient->id,
            metadata:   ['fields_changed' => array_keys($validated)],
            request:    request()
        );

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient record updated.');
    }

    /**
     * Flag patient as high risk.
     *
     * POST /patients/{patient}/flag
     */
    public function flag(Request $request, Patient $patient)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $staff = Auth::guard('staff')->user();

        $patient->update([
            'risk_flagged'        => true,
            'risk_flag_reason'    => $request->reason,
            'risk_flagged_by'     => $staff->id,
            'risk_flagged_at'     => now(),
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'patient_risk_flagged',
            entityType: 'patient',
            entityId:   $patient->id,
            metadata:   ['reason' => $request->reason],
            request:    $request
        );

        return back()->with('success', 'Patient flagged as high risk.');
    }

    public function unflag(Request $request, Patient $patient)
    {
        $patient->update([
            'risk_flagged'     => false,
            'risk_flag_reason' => null,
            'risk_flagged_by'  => null,
            'risk_flagged_at'  => null,
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'patient_risk_flag_removed',
            entityType: 'patient',
            entityId:   $patient->id,
            request:    request()
        );

        return back()->with('success', 'Risk flag removed.');
    }

    /**
     * Set Do Not Treat flag.
     *
     * POST /patients/{patient}/do-not-treat
     */
    public function doNotTreat(Request $request, Patient $patient)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $staff = Auth::guard('staff')->user();

        $patient->update([
            'do_not_treat'        => true,
            'do_not_treat_reason' => $request->reason,
            'do_not_treat_set_by' => $staff->id,
            'do_not_treat_set_at' => now(),
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'patient_do_not_treat_set',
            entityType: 'patient',
            entityId:   $patient->id,
            metadata:   ['reason' => $request->reason],
            request:    $request
        );

        return back()->with('success', 'Do Not Treat flag set.');
    }

    /**
     * Mark patient as deceased.
     *
     * POST /patients/{patient}/deceased
     */
    public function markDeceased(Request $request, Patient $patient)
    {
        $staff = Auth::guard('staff')->user();

        $patient->update([
            'deceased'          => true,
            'deceased_noted_at' => now(),
            'deceased_noted_by' => $staff->id,
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'patient_marked_deceased',
            entityType: 'patient',
            entityId:   $patient->id,
            request:    $request
        );

        return back()->with('success', 'Patient record marked as deceased. All clinical actions are now locked.');
    }
}
