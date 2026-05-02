<?php

/**
 * PATCH routes — add to routes/web.php inside the auth group
 *
 * 1. GDPR consent per-patient view (Gap 4)
 * 2. Consultation thread route already declared in Phase 12 — just needed the view
 *    Route already present: Route::get('/messages/consultation/{consultation}', ...)
 *    The view resources/views/messages/thread.blade.php was missing — now added.
 */

use Illuminate\Support\Facades\Route;

// ── Gap 4: per-patient GDPR consent ─────────────────────────────────────────

Route::get('/compliance/consent/{patient}', function (\App\Models\Patient $patient) {
    $consentLogs = \App\Models\ComplianceLog::with('staff')
        ->where('user_id', $patient->id)
        ->whereIn('type', \App\Models\ComplianceLog::TYPES_GROUPED['Patient'])
        ->orderByDesc('created_at')
        ->get();

    return view('compliance.gdpr-consent', compact('patient', 'consentLogs'));
})
->name('compliance.consent')
->middleware(['auth:staff', \App\Http\Middleware\EnforceTwoFactor::class, 'role:super_admin,superintendent_pharmacist']);

/*
 * ── Gap 1 & 2: Add these two entries to WorkflowRule::TRIGGERS:
 *
 *   'consultation.submitted'  => 'Consultation Submitted by Patient',
 *   'prescription.dispensed'  => 'Prescription Dispensed',
 *
 * ── Gap 1: Add to WorkflowEngine::actionSendEmail() match():
 *
 *   'consultation_submitted' => new \App\Mail\ConsultationSubmittedMail($context),
 *
 * ── Gap 2: Add to WorkflowEngine::actionSendEmail() match():
 *
 *   'order_dispensed' => new \App\Mail\OrderDispensedMail(
 *       $context->prescription?->load('patient')?->patient
 *           ? app(\App\Models\Order::class)->where('prescription_id', $context->prescription_id)->first()
 *           : $context,
 *       $this->resolvePatient($context)
 *   ),
 *
 * ── Gap 2: Add to DispensingLabelService::signOff() after advancing prescription:
 *
 *   app(\App\Services\WorkflowEngine::class)->fire('prescription.dispensed', $label);
 *
 * ── Gap 3: The thread.blade.php view is now present in messages/.
 *    The route was already declared in phase12-13.php:
 *    Route::get('/messages/consultation/{consultation}', [MessagesController::class, 'thread'])->name('messages.thread');
 *    No route change needed.
 *
 * ── Link from patient record: add to patients/show.blade.php sidebar or tab:
 *
 *   <a href="{{ route('compliance.consent', $patient) }}" class="text-xs font-medium text-lilac-600">
 *       GDPR Consent Record →
 *   </a>
 */
