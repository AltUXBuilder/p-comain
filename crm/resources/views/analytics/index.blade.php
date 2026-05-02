<x-layouts.app>
    <x-slot name="pageTitle">Analytics</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Period selector + export links --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex gap-2">
                @foreach([7, 30, 60, 90] as $d)
                    <a href="{{ route('analytics.index', ['days' => $d]) }}"
                        @class(['rounded-xl px-4 py-2 text-sm font-medium transition border',
                            'bg-plum-800 text-lilac-200 border-plum-800' => $days == $d,
                            'border-plum-200 text-plum-600 hover:bg-plum-50' => $days != $d])>
                        {{ $d }}d
                    </a>
                @endforeach
            </div>
            <div class="flex gap-2">
                <a href="{{ route('analytics.dispensing') }}" class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-600 hover:bg-plum-50">Dispensing</a>
                <a href="{{ route('analytics.cohorts') }}"   class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-600 hover:bg-plum-50">Cohorts</a>
                <a href="{{ route('analytics.nps') }}"       class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-600 hover:bg-plum-50">NPS</a>
            </div>
        </div>

        {{-- ── Consultation funnel ─────────────────────────────────────── --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-plum-700">Consultation Funnel ({{ $days }} days)</h3>
                <a href="{{ route('analytics.export.funnel', ['days' => $days]) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Export CSV</a>
            </div>
            <div class="grid grid-cols-4 gap-3">
                @foreach([
                    ['label' => 'Started',   'count' => $funnel['started'],   'rate' => null],
                    ['label' => 'Completed', 'count' => $funnel['completed'], 'rate' => $funnel['started_to_completed']],
                    ['label' => 'Approved',  'count' => $funnel['approved'],  'rate' => $funnel['completed_to_approved']],
                    ['label' => 'Ordered',   'count' => $funnel['ordered'],   'rate' => $funnel['approved_to_ordered']],
                ] as $i => $stage)
                    <div class="relative rounded-xl border border-plum-100 p-4 text-center">
                        @if($i > 0)
                            <div class="absolute -left-1.5 top-1/2 -translate-y-1/2 text-plum-300 text-xs">→</div>
                        @endif
                        <p class="text-2xl font-bold text-plum-800">{{ number_format($stage['count']) }}</p>
                        <p class="text-xs font-medium text-plum-600">{{ $stage['label'] }}</p>
                        @if($stage['rate'] !== null)
                            <p class="mt-1 text-xs text-plum-400">{{ $stage['rate'] }}% from prev.</p>
                        @endif
                    </div>
                @endforeach
            </div>
            @if($funnel['overall_conversion'] !== null)
                <p class="mt-3 text-center text-xs text-plum-400">
                    Overall conversion (started → ordered): <strong class="text-plum-700">{{ $funnel['overall_conversion'] }}%</strong>
                </p>
            @endif
        </div>

        <div class="grid gap-5 lg:grid-cols-2">

            {{-- ── Treatment category performance ──────────────────── --}}
            <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
                <div class="border-b border-plum-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-plum-700">Treatment Category Performance</h3>
                </div>
                <table class="min-w-full divide-y divide-plum-50">
                    <thead class="bg-plum-50/60"><tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Category</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Consultations</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Approval %</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Revenue</th>
                    </tr></thead>
                    <tbody class="divide-y divide-plum-50">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-plum-50/10">
                                <td class="px-4 py-3 text-sm font-medium text-plum-800">{{ $cat->category }}</td>
                                <td class="px-4 py-3 text-right text-sm text-plum-700">{{ number_format($cat->total_consultations) }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    @if($cat->approval_rate !== null)
                                        <span class="font-medium {{ $cat->approval_rate >= 80 ? 'text-green-600' : ($cat->approval_rate >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                                            {{ $cat->approval_rate }}%
                                        </span>
                                    @else
                                        <span class="text-plum-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-plum-800">£{{ number_format($cat->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-plum-400">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ── Patient acquisition by channel ──────────────────── --}}
            <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
                <div class="border-b border-plum-50 px-5 py-4">
                    <h3 class="text-sm font-semibold text-plum-700">Patient Acquisition by Channel</h3>
                </div>
                <table class="min-w-full divide-y divide-plum-50">
                    <thead class="bg-plum-50/60"><tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Channel</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Patients</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Converted</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Rate</th>
                    </tr></thead>
                    <tbody class="divide-y divide-plum-50">
                        @forelse($acquisition as $row)
                            <tr class="hover:bg-plum-50/10">
                                <td class="px-4 py-3 text-sm font-medium text-plum-800">{{ str_replace('_', ' ', ucfirst($row->channel)) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-plum-700">{{ number_format($row->patients) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-plum-700">{{ number_format($row->converted) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium {{ $row->conversion_rate >= 30 ? 'text-green-600' : 'text-plum-600' }}">{{ $row->conversion_rate }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-plum-400">No acquisition data. Ensure <code>acquisition_channel</code> is set on patient records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ── Prescriber rates ────────────────────────────────────── --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
            <div class="mb-0 flex items-center justify-between border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Prescriber Approval & Rejection Rates ({{ $days }} days)</h3>
                <a href="{{ route('analytics.export.prescriber-rates', ['days' => $days]) }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Export CSV</a>
            </div>
            <table class="min-w-full divide-y divide-plum-50">
                <thead class="bg-plum-50/60"><tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Prescriber</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">GPhC</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Reviewed</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Approval %</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Rejection %</th>
                    <th class="hidden px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Avg Review (h)</th>
                </tr></thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($prescribers as $p)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 text-sm font-medium text-plum-800">{{ $p->prescriber_name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-plum-500">{{ $p->gphc_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm text-plum-700">{{ number_format($p->total_reviewed) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ $p->approval_rate >= 80 ? 'text-green-600' : 'text-amber-600' }}">
                                {{ $p->approval_rate !== null ? $p->approval_rate . '%' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm {{ $p->rejection_rate > 20 ? 'text-red-600 font-medium' : 'text-plum-600' }}">
                                {{ $p->rejection_rate !== null ? $p->rejection_rate . '%' : '—' }}
                            </td>
                            <td class="hidden px-4 py-3 text-right text-sm text-plum-500 md:table-cell">
                                {{ $p->avg_review_hours ? round($p->avg_review_hours, 1) . 'h' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-plum-400">No reviewed consultations in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── NPS summary strip ───────────────────────────────────── --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-plum-700">Net Promoter Score (90 days)</h3>
                <a href="{{ route('analytics.nps') }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">Full NPS report →</a>
            </div>
            <div class="grid grid-cols-4 gap-3">
                <div class="rounded-xl border border-plum-100 p-3 text-center">
                    <p class="text-3xl font-bold {{ $nps['nps'] === null ? 'text-plum-300' : ($nps['nps'] >= 50 ? 'text-green-600' : ($nps['nps'] >= 0 ? 'text-amber-600' : 'text-red-600')) }}">
                        {{ $nps['nps'] !== null ? ($nps['nps'] >= 0 ? '+' : '') . $nps['nps'] : '—' }}
                    </p>
                    <p class="text-xs text-plum-400">NPS Score</p>
                </div>
                <div class="rounded-xl border border-green-100 bg-green-50 p-3 text-center">
                    <p class="text-2xl font-bold text-green-700">{{ number_format($nps['promoters']) }}</p>
                    <p class="text-xs text-green-600">Promoters (9–10)</p>
                </div>
                <div class="rounded-xl border border-plum-100 p-3 text-center">
                    <p class="text-2xl font-bold text-plum-600">{{ number_format($nps['passives']) }}</p>
                    <p class="text-xs text-plum-400">Passives (7–8)</p>
                </div>
                <div class="rounded-xl border border-red-100 bg-red-50 p-3 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ number_format($nps['detractors']) }}</p>
                    <p class="text-xs text-red-400">Detractors (0–6)</p>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
