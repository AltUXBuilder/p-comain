<?php

namespace App\Http\Controllers\Consultations;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Consultation;
use App\Models\ConsultationRejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    /**
     * Consultation queue — pending review, sorted by wait time.
     *
     * GET /consultations/queue
     */
    public function queue(Request $request)
    {
        $query = Consultation::with(['patient', 'product.treatment'])
            ->where('status', 'awaiting_review')
            ->orderBy('created_at', 'asc'); // longest wait first

        // Filter by category
        if ($category = $request->input('category')) {
            $query->whereHas('product.treatment.category', fn ($q) => $q->where('id', $category));
        }

        // Filter by prescriber (self)
        if ($request->boolean('mine')) {
            $query->where('prescriber_id', Auth::guard('staff')->id());
        }

        $consultations = $query->paginate(20)->withQueryString();

        $stats = [
            'awaiting'  => Consultation::where('status', 'awaiting_review')->count(),
            'flagged'   => Consultation::where('status', 'flagged')->count(),
            'today'     => Consultation::where('status', 'awaiting_review')
                            ->whereDate('created_at', today())->count(),
        ];

        return view('consultations.queue', compact('consultations', 'stats'));
    }

    /**
     * Individual consultation review.
     *
     * GET /consultations/{consultation}
     */
    public function show(Consultation $consultation)
    {
        $consultation->load([
            'patient',
            'product.treatment',
            'questionnaire.questions',
            'prescriber',
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'consultation_viewed',
            entityType: 'consultation',
            entityId:   $consultation->id,
            request:    request()
        );

        // Decode answers (stored as JSON in the DB)
        $answers  = is_array($consultation->answers)
            ? $consultation->answers
            : json_decode($consultation->answers, true);

        $questions = $consultation->questionnaire?->questions
            ?->keyBy('id')
            ?? collect();

        return view('consultations.show', compact('consultation', 'answers', 'questions'));
    }

    /**
     * Approve a consultation — creates a prescription stub.
     *
     * POST /consultations/{consultation}/approve
     */
    public function approve(Request $request, Consultation $consultation)
    {
        $this->ensureActionable($consultation);

        $staff = Auth::guard('staff')->user();

        $consultation->update([
            'status'        => 'approved',
            'prescriber_id' => $staff->id,
            'reviewed_at'   => now(),
        ]);

        // Create draft prescription
        \App\Models\Prescription::create([
            'consultation_id' => $consultation->id,
            'patient_id'      => $consultation->patient_id,
            'prescriber_id'   => $staff->id,
            'product_id'      => $consultation->product_id,
            'status'          => 'draft',
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'consultation_approved',
            entityType: 'consultation',
            entityId:   $consultation->id,
            metadata:   ['gphc_number' => $staff->gphc_number],
            request:    $request
        );

        // TODO Phase 12: dispatch approval notification email to patient

        return redirect()->route('consultations.queue')
            ->with('success', 'Consultation approved. Draft prescription created.');
    }

    /**
     * Reject a consultation — adds to rejection register.
     *
     * POST /consultations/{consultation}/reject
     */
    public function reject(Request $request, Consultation $consultation)
    {
        $request->validate([
            'reason'            => ['required', 'string', 'max:2000'],
            'notify_patient'    => ['boolean'],
        ]);

        $this->ensureActionable($consultation);

        $staff = Auth::guard('staff')->user();

        $consultation->update([
            'status'        => 'rejected',
            'prescriber_id' => $staff->id,
            'reviewed_at'   => now(),
        ]);

        ConsultationRejection::create([
            'consultation_id'  => $consultation->id,
            'prescriber_id'    => $staff->id,
            'gphc_number'      => $staff->gphc_number,
            'reason'           => $request->reason,
            'patient_notified' => $request->boolean('notify_patient'),
        ]);

        AuditLog::record(
            staffId:    $staff->id,
            action:     'consultation_rejected',
            entityType: 'consultation',
            entityId:   $consultation->id,
            metadata:   [
                'reason'           => $request->reason,
                'patient_notified' => $request->boolean('notify_patient'),
                'gphc_number'      => $staff->gphc_number,
            ],
            request:    $request
        );

        return redirect()->route('consultations.queue')
            ->with('success', 'Consultation rejected and added to the rejection register.');
    }

    /**
     * Flag a consultation for further review.
     *
     * POST /consultations/{consultation}/flag
     */
    public function flag(Request $request, Consultation $consultation)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $consultation->update(['status' => 'flagged']);

        // Save as a clinical note on the patient
        \App\Models\ClinicalNote::create([
            'patient_id'    => $consultation->patient_id,
            'staff_id'      => Auth::guard('staff')->id(),
            'consultation_id' => $consultation->id,
            'body'          => "[Consultation flagged] {$request->reason}",
            'internal_only' => true,
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'consultation_flagged',
            entityType: 'consultation',
            entityId:   $consultation->id,
            metadata:   ['reason' => $request->reason],
            request:    $request
        );

        return back()->with('success', 'Consultation flagged for review.');
    }

    // ─── Private helpers ──────────────────────────────────────────────────

    private function ensureActionable(Consultation $consultation): void
    {
        abort_if(
            in_array($consultation->status, ['approved', 'rejected']),
            422,
            'This consultation has already been reviewed.'
        );

        abort_if(
            $consultation->patient?->do_not_treat,
            422,
            'This patient is marked Do Not Treat.'
        );

        abort_if(
            $consultation->patient?->deceased,
            422,
            'This patient is deceased.'
        );
    }
}
