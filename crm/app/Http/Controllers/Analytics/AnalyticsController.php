<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $service) {}

    // ── Main analytics dashboard ──────────────────────────────────────────────

    public function index(Request $request)
    {
        $days   = (int) $request->input('days', 30);
        $days   = in_array($days, [7, 30, 60, 90]) ? $days : 30;

        $funnel      = $this->service->consultationFunnel($days);
        $categories  = $this->service->categoryPerformance($days);
        $prescribers = $this->service->prescriberRates($days);
        $acquisition = $this->service->acquisitionByChannel($days);
        $nps         = $this->service->npsSummary(90); // NPS always over 90 days

        return view('analytics.index', compact(
            'days', 'funnel', 'categories', 'prescribers', 'acquisition', 'nps'
        ));
    }

    // ── Dispensing throughput ─────────────────────────────────────────────────

    public function dispensing(Request $request)
    {
        $groupBy = $request->input('group', 'day');
        $periods = match($groupBy) {
            'week'  => 13,
            'month' => 12,
            default => 30,
        };

        $throughput = $this->service->dispensingThroughput($groupBy, $periods);
        return view('analytics.dispensing', compact('throughput', 'groupBy'));
    }

    // ── Subscription cohort retention ─────────────────────────────────────────

    public function cohorts(Request $request)
    {
        $cohorts   = $this->service->cohortRetention(12, 6);
        $churnRate = $this->service->monthlyChurnRate(6);
        return view('analytics.cohorts', compact('cohorts', 'churnRate'));
    }

    // ── NPS detail ────────────────────────────────────────────────────────────

    public function nps(Request $request)
    {
        $days    = (int) $request->input('days', 90);
        $summary = $this->service->npsSummary($days);
        return view('analytics.nps', compact('summary', 'days'));
    }

    // ── Report exports ────────────────────────────────────────────────────────

    /**
     * Export consultation funnel as CSV.
     */
    public function exportFunnel(Request $request)
    {
        $days   = (int) $request->input('days', 30);
        $funnel = $this->service->consultationFunnel($days);

        $rows = [
            ['Stage', 'Count', 'Conversion from Previous Stage'],
            ['Started', $funnel['started'], '—'],
            ['Completed', $funnel['completed'], $funnel['started_to_completed'] ? $funnel['started_to_completed'] . '%' : '—'],
            ['Approved', $funnel['approved'], $funnel['completed_to_approved'] ? $funnel['completed_to_approved'] . '%' : '—'],
            ['Ordered', $funnel['ordered'], $funnel['approved_to_ordered'] ? $funnel['approved_to_ordered'] . '%' : '—'],
            ['', '', ''],
            ['Overall conversion', $funnel['overall_conversion'] ? $funnel['overall_conversion'] . '%' : '—', ''],
        ];

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'analytics_funnel_exported',
            entityType: null, entityId: null,
            metadata:   ['days' => $days],
            request:    $request
        );

        return $this->csvResponse($rows, "consultation-funnel-{$days}d.csv");
    }

    /**
     * Export prescriber rates as CSV.
     */
    public function exportPrescriberRates(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $data = $this->service->prescriberRates($days);

        $rows = [['Prescriber', 'GPhC', 'Total Reviewed', 'Approved', 'Rejected', 'Approval Rate %', 'Rejection Rate %', 'Avg Review Time (h)']];
        foreach ($data as $row) {
            $rows[] = [
                $row->prescriber_name,
                $row->gphc_number ?? '—',
                $row->total_reviewed,
                $row->approved,
                $row->rejected,
                $row->approval_rate  ?? '—',
                $row->rejection_rate ?? '—',
                $row->avg_review_hours ? round($row->avg_review_hours, 1) : '—',
            ];
        }

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'analytics_prescriber_rates_exported',
            entityType: null, entityId: null,
            request:    $request
        );

        return $this->csvResponse($rows, "prescriber-rates-{$days}d.csv");
    }

    /**
     * Export NPS scores as CSV.
     */
    public function exportNps(Request $request)
    {
        $days = (int) $request->input('days', 90);
        $rows = [['Score', 'Category', 'Comment', 'Patient', 'Collected At']];

        \App\Models\NpsScore::with('patient')
            ->where('collected_at', '>=', now()->subDays($days))
            ->orderByDesc('collected_at')
            ->cursor()
            ->each(function ($nps) use (&$rows) {
                $rows[] = [
                    $nps->score,
                    $nps->category,
                    $nps->comment ?? '',
                    $nps->patient?->full_name ?? 'Anonymised',
                    $nps->collected_at->format('d/m/Y H:i'),
                ];
            });

        AuditLog::record(
            staffId:    Auth::guard('staff')->id(),
            action:     'analytics_nps_exported',
            entityType: null, entityId: null,
            request:    $request
        );

        return $this->csvResponse($rows, "nps-scores-{$days}d.csv");
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function csvResponse(array $rows, string $filename): \Illuminate\Http\Response
    {
        $csv = '';
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
