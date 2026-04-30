<?php

namespace App\Http\Controllers\Prescriptions;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Prescription;
use App\Models\Staff;
use App\Models\TreatmentCategory;
use App\Services\PrescriptionPdfService;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    public function __construct(
        private PrescriptionService    $service,
        private PrescriptionPdfService $pdfService,
    ) {}

    // ── Index / prescriber dashboard ─────────────────────────────────────────

    /**
     * GET /prescriptions
     *
     * Shows the prescriber's queue: pending review, plus filters.
     */
    public function index(Request $request)
    {
        $staff = Auth::guard('staff')->user();

        $query = Prescription::with(['patient', 'product.treatment', 'prescriber'])
            ->when(
                ! $staff->isSuperAdmin(),
                fn ($q) => $q->where('prescriber_id', $staff->id)
            );

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: show active statuses (not archived)
            $query->whereNotIn('status', [Prescription::STATUS_ARCHIVED]);
        }

        // Date range
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $query->orderByRaw("FIELD(status, 'pending_review', 'draft', 'approved', 'sent_to_dispense', 'dispensed', 'archived')")
              ->orderBy('created_at', 'asc');

        $prescriptions = $query->paginate(25)->withQueryString();

        $counts = [];
        foreach (Prescription::STATUSES as $key => $label) {
            $counts[$key] = Prescription::where('status', $key)
                ->when(! $staff->isSuperAdmin(), fn ($q) => $q->where('prescriber_id', $staff->id))
                ->count();
        }

        return view('prescriptions.index', compact('prescriptions', 'counts'));
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    /**
     * GET /prescriptions/{prescription}
     */
    public function show(Prescription $prescription)
    {
        $prescription->load(['patient', 'prescriber', 'product.treatment', 'consultation', 'repeatChildren', 'repeatParent']);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'prescription_viewed',
            entityType: 'prescription',
            entityId:   $prescription->id,
            request:    request()
        );

        return view('prescriptions.show', compact('prescription'));
    }

    // ── Builder — edit draft ─────────────────────────────────────────────────

    /**
     * GET /prescriptions/{prescription}/edit
     */
    public function edit(Prescription $prescription)
    {
        abort_if(
            $prescription->status !== Prescription::STATUS_DRAFT,
            403,
            'Only draft prescriptions can be edited.'
        );

        $prescription->load(['patient', 'product', 'prescriber']);

        return view('prescriptions.edit', compact('prescription'));
    }

    /**
     * PUT /prescriptions/{prescription}
     */
    public function update(Request $request, Prescription $prescription)
    {
        abort_if($prescription->status !== Prescription::STATUS_DRAFT, 403);

        $validated = $request->validate([
            'dosage_instructions'  => ['required', 'string', 'max:500'],
            'quantity'             => ['required', 'string', 'max:100'],
            'prescriber_notes'     => ['nullable', 'string', 'max:2000'],
            'is_repeat'            => ['boolean'],
            'repeat_interval_days' => ['nullable', 'integer', 'min:7', 'max:365'],
            'legal_wording'        => ['required', 'string'],
        ]);

        $prescription->update($validated);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'prescription_draft_updated',
            entityType: 'prescription',
            entityId:   $prescription->id,
            request:    $request
        );

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated.');
    }

    // ── Submit draft for review ───────────────────────────────────────────────

    /**
     * POST /prescriptions/{prescription}/submit
     */
    public function submit(Prescription $prescription)
    {
        $prescription->transitionTo(Prescription::STATUS_PENDING_REVIEW, Auth::guard('staff')->user());
        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription submitted for review.');
    }

    // ── Sign / approve ────────────────────────────────────────────────────────

    /**
     * POST /prescriptions/{prescription}/sign
     *
     * Accepts optional base64 PNG signature override in request body.
     */
    public function sign(Request $request, Prescription $prescription)
    {
        $request->validate([
            'signature_override' => ['nullable', 'string'], // base64 PNG
        ]);

        $staff = Auth::guard('staff')->user();

        // Check prescriber has a signature saved unless override provided
        if (! $request->filled('signature_override') && ! $prescription->hasSignature()) {
            return back()->withErrors(['signature' => 'You have no signature on file. Please save your signature in Profile Settings or draw one below.']);
        }

        try {
            $this->service->sign(
                $prescription,
                $staff,
                $request->input('signature_override')
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['signature' => $e->getMessage()]);
        }

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription signed and approved. PDF generated.');
    }

    // ── Batch sign ────────────────────────────────────────────────────────────

    /**
     * POST /prescriptions/batch-sign
     */
    public function batchSign(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:prescriptions,id'],
        ]);

        $staff   = Auth::guard('staff')->user();
        $results = $this->service->batchSign($request->ids, $staff);

        $signed = count($results['signed']);
        $failed = count($results['failed']);

        $message = "{$signed} prescription(s) signed.";
        if ($failed) {
            $message .= " {$failed} failed — see details below.";
        }

        return back()->with('batch_results', $results)->with('success', $message);
    }

    // ── Send to dispense ──────────────────────────────────────────────────────

    /**
     * POST /prescriptions/{prescription}/send-to-dispense
     */
    public function sendToDispense(Prescription $prescription)
    {
        $this->service->sendToDispense($prescription, Auth::guard('staff')->user());
        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription sent to dispensing queue.');
    }

    // ── Archive ───────────────────────────────────────────────────────────────

    /**
     * POST /prescriptions/{prescription}/archive
     */
    public function archive(Prescription $prescription)
    {
        $prescription->transitionTo(Prescription::STATUS_ARCHIVED, Auth::guard('staff')->user());
        return back()->with('success', 'Prescription archived.');
    }

    // ── PDF stream & download ─────────────────────────────────────────────────

    /**
     * GET /prescriptions/{prescription}/pdf
     */
    public function pdf(Prescription $prescription)
    {
        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'prescription_pdf_viewed',
            entityType: 'prescription',
            entityId:   $prescription->id,
            request:    request()
        );
        return $this->pdfService->stream($prescription);
    }

    /**
     * GET /prescriptions/{prescription}/download
     */
    public function download(Prescription $prescription)
    {
        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'prescription_pdf_downloaded',
            entityType: 'prescription',
            entityId:   $prescription->id,
            request:    request()
        );
        return $this->pdfService->download($prescription);
    }

    // ── Private prescription register ─────────────────────────────────────────

    /**
     * GET /prescriptions/register
     */
    public function register(Request $request)
    {
        $query = Prescription::with(['patient', 'prescriber', 'product.treatment.category'])
            ->whereNotIn('status', [Prescription::STATUS_DRAFT, Prescription::STATUS_PENDING_REVIEW])
            ->orderBy('signed_at', 'desc');

        // Filters
        if ($from = $request->input('from')) {
            $query->whereDate('signed_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('signed_at', '<=', $to);
        }
        if ($prescriberId = $request->input('prescriber_id')) {
            $query->where('prescriber_id', $prescriberId);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('product.treatment.category', fn ($q) => $q->where('id', $categoryId));
        }

        $prescriptions = $query->paginate(30)->withQueryString();
        $prescribers   = Staff::whereIn('role', ['superintendent_pharmacist', 'prescriber'])->orderBy('last_name')->get();
        $categories    = TreatmentCategory::orderBy('name')->get();

        return view('prescriptions.register', compact('prescriptions', 'prescribers', 'categories'));
    }

    /**
     * GET /prescriptions/register/export
     *
     * CSV download of the private prescription register.
     */
    public function exportRegister(Request $request)
    {
        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'prescription_register_exported',
            entityType: 'prescription',
            entityId:   null,
            metadata:   $request->only(['from', 'to', 'prescriber_id', 'category_id']),
            request:    $request
        );

        $csv = $this->service->exportRegister(
            from:         $request->input('from') ? new \DateTime($request->input('from')) : null,
            to:           $request->input('to')   ? new \DateTime($request->input('to'))   : null,
            prescriberId: $request->input('prescriber_id'),
            categoryId:   $request->input('category_id'),
        );

        $filename = 'prescription-register-' . now()->format('Ymd-His') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
