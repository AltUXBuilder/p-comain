<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ComplianceLog;
use App\Models\ConsultationRejection;
use App\Models\Prescription;
use App\Services\PrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{
    // ── GPhC inspection readiness dashboard ───────────────────────────────────

    public function index()
    {
        // Build a checklist of key GPhC-required registers and whether they have recent entries
        $checklist = [
            [
                'label'    => 'Private Prescription Register',
                'detail'   => Prescription::whereIn('status', ['approved', 'sent_to_dispense', 'dispensed'])->count() . ' prescriptions',
                'route'    => route('prescriptions.register'),
                'ok'       => Prescription::whereIn('status', ['approved', 'sent_to_dispense', 'dispensed'])->exists(),
            ],
            [
                'label'    => 'Refusal/Rejection Register',
                'detail'   => ConsultationRejection::count() . ' rejections on record',
                'route'    => route('compliance.rejections'),
                'ok'       => true, // always maintained
            ],
            [
                'label'    => 'Audit Trail',
                'detail'   => AuditLog::where('created_at', '>=', now()->subDays(7))->count() . ' entries in last 7 days',
                'route'    => route('compliance.audit-log'),
                'ok'       => AuditLog::exists(),
            ],
            [
                'label'    => 'Staff Two-Factor Authentication',
                'detail'   => \App\Models\Staff::where('active', true)->where('two_factor_confirmed', false)->count() . ' staff without 2FA',
                'route'    => route('staff.index'),
                'ok'       => \App\Models\Staff::where('active', true)->where('two_factor_confirmed', false)->count() === 0,
            ],
            [
                'label'    => 'IP Whitelist Active',
                'detail'   => \App\Models\IpWhitelistEntry::where('active', true)->count() . ' whitelisted IPs',
                'route'    => route('settings.index'),
                'ok'       => \App\Models\IpWhitelistEntry::where('active', true)->exists(),
            ],
            [
                'label'    => 'Dispensing Labels',
                'detail'   => \App\Models\DispensingLabel::count() . ' labels generated',
                'route'    => route('labels.index'),
                'ok'       => true,
            ],
            [
                'label'    => 'MHRA Yellow Card Reports',
                'detail'   => ComplianceLog::where('type', 'mhra_yellow_card')->count() . ' reports',
                'route'    => route('compliance.log', ['type' => 'mhra_yellow_card']),
                'ok'       => true,
            ],
            [
                'label'    => 'GDPR Consent Records',
                'detail'   => ComplianceLog::where('type', 'gdpr_consent')->count() . ' records',
                'route'    => route('compliance.log', ['type' => 'gdpr_consent']),
                'ok'       => ComplianceLog::where('type', 'gdpr_consent')->exists(),
            ],
        ];

        $recentLogs = ComplianceLog::with(['patient', 'staff'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('compliance.index', compact('checklist', 'recentLogs'));
    }

    // ── Compliance log ────────────────────────────────────────────────────────

    public function log(Request $request)
    {
        $query = ComplianceLog::with(['patient', 'staff'])
            ->orderByDesc('created_at');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('compliance.log', [
            'logs'  => $logs,
            'types' => ComplianceLog::TYPES,
        ]);
    }

    // ── Record compliance event ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'    => ['required', 'in:' . implode(',', array_keys(ComplianceLog::TYPES))],
            'user_id' => ['nullable', 'exists:users,id'],
            'notes'   => ['required', 'string', 'max:2000'],
            'meta'    => ['nullable', 'array'],
        ]);

        $log = ComplianceLog::record(
            type:    $validated['type'],
            userId:  $validated['user_id'] ?? null,
            staffId: Auth::guard('staff')->id(),
            notes:   $validated['notes'],
            meta:    $validated['meta'] ?? [],
        );

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     "compliance_log_created",
            entityType: 'compliance_log',
            entityId:   $log->id,
            metadata:   ['type' => $log->type],
            request:    $request
        );

        return back()->with('success', 'Compliance record saved.');
    }

    // ── Rejection register ─────────────────────────────────────────────────────

    public function rejections(Request $request)
    {
        $query = ConsultationRejection::with(['consultation.patient', 'prescriber'])
            ->orderByDesc('created_at');

        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $rejections = $query->paginate(30)->withQueryString();

        return view('compliance.rejections', compact('rejections'));
    }

    public function rejectionsExport(Request $request)
    {
        $query = ConsultationRejection::with(['consultation.patient', 'prescriber'])
            ->orderByDesc('created_at');

        if ($from = $request->input('from')) $query->where('created_at', '>=', $from);
        if ($to   = $request->input('to'))   $query->where('created_at', '<=', $to . ' 23:59:59');

        $rows = [['Date', 'Patient', 'Prescriber', 'GPhC', 'Reason', 'Patient Notified']];

        foreach ($query->cursor() as $r) {
            $rows[] = [
                $r->created_at->format('d/m/Y H:i'),
                $r->consultation?->patient?->full_name ?? '—',
                $r->prescriber?->full_name ?? '—',
                $r->gphc_number ?? '—',
                $r->reason,
                $r->patient_notified ? 'Yes' : 'No',
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'rejection_register_exported',
            entityType: null, entityId: null,
            request:    $request
        );

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rejection-register-' . now()->format('Ymd') . '.csv"',
        ]);
    }

    // ── Audit log ────────────────────────────────────────────────────────────

    public function auditLog(Request $request)
    {
        $query = AuditLog::with('staff')
            ->orderByDesc('created_at');

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }
        if ($staffId = $request->input('staff_id')) {
            $query->where('staff_id', $staffId);
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs  = $query->paginate(50)->withQueryString();
        $staff = \App\Models\Staff::orderBy('last_name')->get();

        return view('compliance.audit-log', compact('logs', 'staff'));
    }

    public function auditLogExport(Request $request)
    {
        $query = AuditLog::with('staff')->orderByDesc('created_at');

        if ($from = $request->input('from')) $query->where('created_at', '>=', $from);
        if ($to   = $request->input('to'))   $query->where('created_at', '<=', $to . ' 23:59:59');
        if ($a    = $request->input('action')) $query->where('action', 'like', "%{$a}%");

        $rows = [['Timestamp', 'Staff', 'GPhC', 'Action', 'Entity', 'Entity ID', 'IP Address']];

        foreach ($query->cursor() as $log) {
            $rows[] = [
                $log->created_at->format('d/m/Y H:i:s'),
                $log->staff?->full_name ?? 'System',
                $log->gphc_number ?? '—',
                $log->action,
                $log->entity_type ?? '—',
                $log->entity_id   ?? '—',
                $log->ip_address  ?? '—',
            ];
        }

        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'audit_log_exported',
            entityType: null, entityId: null,
            request:    $request
        );

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log-' . now()->format('Ymd') . '.csv"',
        ]);
    }
}
