<?php

namespace App\Services;

use App\Models\NpsScore;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // ── Consultation funnel ───────────────────────────────────────────────────

    /**
     * Started → Completed → Approved → Ordered drop-off funnel.
     * Counts are for the given period (default: last 30 days).
     */
    public function consultationFunnel(int $days = 30): array
    {
        $since = now()->subDays($days)->startOfDay();

        $started = DB::table('draft_consultations')
            ->where('created_at', '>=', $since)
            ->count();

        $completed = DB::table('consultations')
            ->where('created_at', '>=', $since)
            ->count();

        $approved = DB::table('consultations')
            ->where('created_at', '>=', $since)
            ->where('status', 'approved')
            ->count();

        $ordered = DB::table('orders')
            ->where('created_at', '>=', $since)
            ->whereNotIn('status', ['pending_payment', 'cancelled'])
            ->whereNotNull('prescription_id')
            ->count();

        $safeDiv = fn ($a, $b) => $b > 0 ? round($a / $b * 100, 1) : null;

        return [
            'period_days'     => $days,
            'started'         => $started,
            'completed'       => $completed,
            'approved'        => $approved,
            'ordered'         => $ordered,
            'started_to_completed' => $safeDiv($completed, $started),
            'completed_to_approved' => $safeDiv($approved, $completed),
            'approved_to_ordered'   => $safeDiv($ordered, $approved),
            'overall_conversion'    => $safeDiv($ordered, $started),
        ];
    }

    // ── Treatment category performance ────────────────────────────────────────

    public function categoryPerformance(int $days = 30): \Illuminate\Support\Collection
    {
        $since = now()->subDays($days);

        return DB::table('consultations')
            ->join('products',             'consultations.product_id',             '=', 'products.id')
            ->join('treatments',           'products.treatment_id',                '=', 'treatments.id')
            ->join('treatment_categories', 'treatments.treatment_category_id',     '=', 'treatment_categories.id')
            ->leftJoin('orders', function ($join) {
                $join->on('orders.prescription_id', '=',
                    DB::raw('(SELECT id FROM prescriptions WHERE consultation_id = consultations.id LIMIT 1)'));
            })
            ->where('consultations.created_at', '>=', $since)
            ->select([
                'treatment_categories.name as category',
                DB::raw('COUNT(DISTINCT consultations.id) as total_consultations'),
                DB::raw("SUM(CASE WHEN consultations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN consultations.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw('COUNT(DISTINCT orders.id) as orders'),
                DB::raw('COALESCE(SUM(orders.total), 0) as revenue'),
            ])
            ->groupBy('treatment_categories.id', 'treatment_categories.name')
            ->orderByDesc('total_consultations')
            ->get()
            ->map(function ($row) {
                $row->approval_rate = $row->total_consultations > 0
                    ? round($row->approved / $row->total_consultations * 100, 1)
                    : null;
                return $row;
            });
    }

    // ── Prescriber approval / rejection rates ─────────────────────────────────

    public function prescriberRates(int $days = 30): \Illuminate\Support\Collection
    {
        $since = now()->subDays($days);

        return DB::table('consultations')
            ->join('staff', 'consultations.prescriber_id', '=', 'staff.id')
            ->where('consultations.reviewed_at', '>=', $since)
            ->whereNotNull('consultations.prescriber_id')
            ->select([
                'staff.id as staff_id',
                DB::raw("CONCAT(staff.first_name, ' ', staff.last_name) as prescriber_name"),
                'staff.gphc_number',
                DB::raw('COUNT(*) as total_reviewed'),
                DB::raw("SUM(CASE WHEN consultations.status = 'approved' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN consultations.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, consultations.created_at, consultations.reviewed_at)) as avg_review_hours'),
            ])
            ->groupBy('staff.id', 'staff.first_name', 'staff.last_name', 'staff.gphc_number')
            ->orderByDesc('total_reviewed')
            ->get()
            ->map(function ($row) {
                $row->approval_rate  = $row->total_reviewed > 0 ? round($row->approved / $row->total_reviewed * 100, 1) : null;
                $row->rejection_rate = $row->total_reviewed > 0 ? round($row->rejected / $row->total_reviewed * 100, 1) : null;
                return $row;
            });
    }

    // ── Dispensing throughput ─────────────────────────────────────────────────

    /**
     * Labels dispensed per day/week/month, configurable grouping.
     */
    public function dispensingThroughput(string $groupBy = 'day', int $periods = 30): \Illuminate\Support\Collection
    {
        $format = match($groupBy) {
            'week'  => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $since = match($groupBy) {
            'week'  => now()->subWeeks($periods),
            'month' => now()->subMonths($periods),
            default => now()->subDays($periods),
        };

        return DB::table('dispensing_labels')
            ->where('dispensed_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(dispensed_at, '{$format}') as period, COUNT(*) as labels")
            ->groupByRaw("DATE_FORMAT(dispensed_at, '{$format}')")
            ->orderBy('period')
            ->get();
    }

    // ── Subscription cohort retention ─────────────────────────────────────────

    /**
     * Monthly cohorts: how many subscribers acquired in month M are still active
     * in subsequent months (M+1, M+2, …).
     * Returns up to 12 cohorts × 6 retention months.
     */
    public function cohortRetention(int $cohorts = 12, int $retentionMonths = 6): array
    {
        $results = [];

        for ($i = $cohorts - 1; $i >= 0; $i--) {
            $cohortStart = now()->subMonths($i)->startOfMonth();
            $cohortEnd   = now()->subMonths($i)->endOfMonth();

            $cohortSize = DB::table('subscriptions')
                ->whereBetween('created_at', [$cohortStart, $cohortEnd])
                ->count();

            if ($cohortSize === 0) continue;

            $retention = [$cohortSize]; // month 0 = 100%

            for ($m = 1; $m <= $retentionMonths; $m++) {
                $checkDate = $cohortStart->copy()->addMonths($m);
                if ($checkDate->isFuture()) {
                    $retention[] = null;
                    continue;
                }

                $active = DB::table('subscriptions as s')
                    ->whereBetween('s.created_at', [$cohortStart, $cohortEnd])
                    ->where(function ($q) use ($checkDate) {
                        $q->where('s.stripe_status', 'active')
                          ->orWhere(function ($q2) use ($checkDate) {
                              $q2->whereNotNull('s.ends_at')
                                 ->where('s.ends_at', '>=', $checkDate);
                          });
                    })
                    ->count();

                $retention[] = $active;
            }

            $results[] = [
                'cohort'     => $cohortStart->format('M Y'),
                'size'       => $cohortSize,
                'retention'  => $retention, // [size, m1_active, m2_active, ...]
                'rates'      => array_map(
                    fn ($v) => $v !== null ? round($v / $cohortSize * 100, 1) : null,
                    $retention
                ),
            ];
        }

        return $results;
    }

    // ── NPS ───────────────────────────────────────────────────────────────────

    public function npsSummary(int $days = 90): array
    {
        $since = now()->subDays($days);
        $query = NpsScore::where('collected_at', '>=', $since);

        $total     = $query->count();
        $breakdown = NpsScore::where('collected_at', '>=', $since)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        $promoters  = $breakdown['promoter']  ?? 0;
        $passives   = $breakdown['passive']   ?? 0;
        $detractors = $breakdown['detractor'] ?? 0;

        // Recent scores for display
        $recentScores = NpsScore::with('patient')
            ->where('collected_at', '>=', $since)
            ->whereNotNull('comment')
            ->latest('collected_at')
            ->limit(10)
            ->get();

        // Trend: NPS per month for last 6 months
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = now()->subMonths($i)->startOfMonth();
            $mEnd   = now()->subMonths($i)->endOfMonth();
            $nps    = NpsScore::calculateNps(
                NpsScore::whereBetween('collected_at', [$mStart, $mEnd])
            );
            $trend[] = [
                'month' => $mStart->format('M Y'),
                'nps'   => $nps,
                'total' => NpsScore::whereBetween('collected_at', [$mStart, $mEnd])->count(),
            ];
        }

        return [
            'period_days' => $days,
            'total'       => $total,
            'nps'         => NpsScore::calculateNps(NpsScore::where('collected_at', '>=', $since)),
            'promoters'   => $promoters,
            'passives'    => $passives,
            'detractors'  => $detractors,
            'recent'      => $recentScores,
            'trend'       => $trend,
        ];
    }

    // ── Patient acquisition by channel ────────────────────────────────────────

    public function acquisitionByChannel(int $days = 90): \Illuminate\Support\Collection
    {
        return DB::table('users')
            ->where('created_at', '>=', now()->subDays($days))
            ->select([
                DB::raw("COALESCE(acquisition_channel, 'unknown') as channel"),
                DB::raw('COUNT(*) as patients'),
                DB::raw("SUM(CASE WHEN stripe_id IS NOT NULL THEN 1 ELSE 0 END) as converted"),
            ])
            ->groupBy('channel')
            ->orderByDesc('patients')
            ->get()
            ->map(function ($row) {
                $row->conversion_rate = $row->patients > 0
                    ? round($row->converted / $row->patients * 100, 1)
                    : 0;
                return $row;
            });
    }

    // ── Churn rate ────────────────────────────────────────────────────────────

    public function monthlyChurnRate(int $months = 6): \Illuminate\Support\Collection
    {
        $results = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $start   = now()->subMonths($i)->startOfMonth();
            $end     = now()->subMonths($i)->endOfMonth();

            $activeAtStart = DB::table('subscriptions')
                ->where('stripe_status', 'active')
                ->where('created_at', '<', $start)
                ->where(function ($q) use ($end) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', $end);
                })
                ->count();

            $churned = DB::table('subscriptions')
                ->whereBetween('ends_at', [$start, $end])
                ->count();

            $results->push([
                'month'        => $start->format('M Y'),
                'active_start' => $activeAtStart,
                'churned'      => $churned,
                'churn_rate'   => $activeAtStart > 0
                    ? round($churned / $activeAtStart * 100, 2)
                    : 0,
            ]);
        }

        return $results;
    }
}
