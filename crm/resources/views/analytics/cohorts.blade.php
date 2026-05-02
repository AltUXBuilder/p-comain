<x-layouts.app>
    <x-slot name="pageTitle">Subscription Cohort Retention</x-slot>
    <div class="space-y-5 animate-fade-in">
        <a href="{{ route('analytics.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Analytics</a>

        {{-- Churn strip --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <h3 class="mb-4 text-sm font-semibold text-plum-700">Monthly Churn Rate (6 months)</h3>
            <div class="grid grid-cols-3 gap-3 sm:grid-cols-6">
                @foreach($churnRate as $row)
                    <div class="rounded-xl border border-plum-100 p-3 text-center">
                        <p class="text-lg font-bold {{ $row['churn_rate'] > 5 ? 'text-red-600' : ($row['churn_rate'] > 2 ? 'text-amber-600' : 'text-green-600') }}">
                            {{ $row['churn_rate'] }}%
                        </p>
                        <p class="text-[10px] text-plum-400">{{ $row['month'] }}</p>
                        <p class="text-[10px] text-plum-300">{{ $row['churned'] }} churned</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Cohort retention table --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Cohort Retention (% still active)</h3>
                <p class="mt-0.5 text-xs text-plum-400">Each row is a monthly cohort. Columns show % of that cohort still active after M months.</p>
            </div>
            @if(empty($cohorts))
                <div class="px-5 py-12 text-center text-sm text-plum-400">No subscription cohort data yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-plum-100">
                        <thead class="bg-plum-50/60"><tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Cohort</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">Size</th>
                            @foreach(['M0','M1','M2','M3','M4','M5','M6'] as $m)
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-plum-500">{{ $m }}</th>
                            @endforeach
                        </tr></thead>
                        <tbody class="divide-y divide-plum-50">
                            @foreach($cohorts as $cohort)
                                <tr class="hover:bg-plum-50/10">
                                    <td class="px-4 py-3 text-sm font-medium text-plum-800">{{ $cohort['cohort'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-plum-700">{{ number_format($cohort['size']) }}</td>
                                    @foreach($cohort['rates'] as $i => $rate)
                                        <td class="px-3 py-3 text-right text-xs font-mono">
                                            @if($rate === null)
                                                <span class="text-plum-200">—</span>
                                            @else
                                                <span class="{{ $i === 0 ? 'text-plum-400' : ($rate >= 80 ? 'text-green-600' : ($rate >= 60 ? 'text-amber-600' : 'text-red-500')) }} font-semibold">
                                                    {{ $rate }}%
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                    {{-- Pad if fewer retention months available --}}
                                    @for($i = count($cohort['rates']); $i <= 6; $i++)
                                        <td class="px-3 py-3 text-right text-xs text-plum-200">—</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
