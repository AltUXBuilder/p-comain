<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ComplianceLog;
use App\Models\DsarRequest;
use App\Services\DsarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DsarController extends Controller
{
    public function __construct(private DsarService $service) {}

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = DsarRequest::with(['patient', 'handler'])
            ->orderByRaw("FIELD(status,'received','in_progress','completed','rejected')")
            ->orderBy('due_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $overdue = DsarRequest::whereNotIn('status', ['completed', 'rejected'])
            ->where('due_at', '<', now())
            ->count();

        return view('compliance.dsar.index', compact('requests', 'overdue'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('compliance.dsar.create', [
            'types' => DsarRequest::TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => ['required', 'in:' . implode(',', array_keys(DsarRequest::TYPES))],
            'requestor_email'  => ['required', 'email'],
            'user_id'          => ['nullable', 'exists:users,id'],
            'notes'            => ['nullable', 'string', 'max:2000'],
        ]);

        $dsar = DsarRequest::create([
            ...$validated,
            'status'    => 'received',
            'handled_by' => Auth::guard('staff')->id(),
        ]);

        ComplianceLog::record(
            type:    'dsar_request',
            userId:  $validated['user_id'] ?? null,
            staffId: Auth::guard('staff')->id(),
            notes:   "DSAR received. Type: {$validated['type']}. Requestor: {$validated['requestor_email']}",
            meta:    ['dsar_id' => $dsar->id]
        );

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'dsar_request_created',
            entityType: 'dsar_request',
            entityId:   $dsar->id,
            request:    $request
        );

        return redirect()->route('compliance.dsar.show', $dsar)
            ->with('success', 'DSAR logged. Due by ' . $dsar->due_at->format('d M Y') . '.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(DsarRequest $dsar)
    {
        $dsar->load(['patient', 'handler']);
        return view('compliance.dsar.show', compact('dsar'));
    }

    // ── Update status ─────────────────────────────────────────────────────────

    public function updateStatus(Request $request, DsarRequest $dsar)
    {
        $request->validate([
            'status' => ['required', 'in:received,in_progress,completed,rejected'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ]);

        $dsar->update([
            'status'     => $request->status,
            'handled_by' => Auth::guard('staff')->id(),
            'notes'      => $request->notes ?? $dsar->notes,
            'completed_at' => $request->status === 'completed' ? now() : $dsar->completed_at,
        ]);

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     "dsar_status_updated",
            entityType: 'dsar_request',
            entityId:   $dsar->id,
            metadata:   ['status' => $request->status],
            request:    $request
        );

        return back()->with('success', 'DSAR status updated.');
    }

    // ── Export SAR data ───────────────────────────────────────────────────────

    public function exportData(DsarRequest $dsar)
    {
        if (! $dsar->user_id) {
            return back()->withErrors(['error' => 'No patient linked to this DSAR — cannot export data.']);
        }

        try {
            $path = $this->service->exportPatientData($dsar);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return $this->service->downloadExport($dsar);
    }

    // ── Download existing export ──────────────────────────────────────────────

    public function downloadExport(DsarRequest $dsar)
    {
        try {
            return $this->service->downloadExport($dsar);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ── Erasure ───────────────────────────────────────────────────────────────

    public function erasePatient(Request $request, DsarRequest $dsar)
    {
        if ($dsar->type !== 'erasure') {
            return back()->withErrors(['error' => 'This DSAR is not a right-to-erasure request.']);
        }

        if (! $dsar->user_id) {
            return back()->withErrors(['error' => 'No patient linked to this DSAR.']);
        }

        try {
            $this->service->erasePatient($dsar, Auth::guard('staff')->user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('compliance.dsar.index')
            ->with('success', 'Patient data pseudonymised. Prescription records retained per GPhC requirements.');
    }
}
