<x-layouts.app>
    <x-slot name="pageTitle">Dispensing Throughput</x-slot>
    <div class="max-w-2xl space-y-5 animate-fade-in">
        <a href="{{ route('analytics.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Analytics</a>

        <div class="flex gap-2">
            @foreach(['day' => 'Daily (30d)', 'week' => 'Weekly (13w)', 'month' => 'Monthly (12m)'] as $key => $label)
                <a href="{{ route('analytics.dispensing', ['group' => $key]) }}"
                    @class(['rounded-xl px-4 py-2 text-sm font-medium transition border',
                        'bg-plum-800 text-lilac-200 border-plum-800' => $groupBy === $key,
                        'border-plum-200 text-plum-600 hover:bg-plum-50' => $groupBy !== $key])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Labels Dispensed per {{ ucfirst($groupBy) }}</h3>
            </div>
            @if($throughput->isEmpty())
                <div class="px-5 py-12 text-center text-sm text-plum-400">No dispensing data in this period.</div>
            @else
                @php $max = $throughput->max('labels') ?: 1; @endphp
                <div class="divide-y divide-plum-50">
                    @foreach($throughput as $row)
                        <div class="flex items-center gap-4 px-5 py-2.5">
                            <p class="w-24 shrink-0 text-xs font-mono text-plum-500">{{ $row->period }}</p>
                            <div class="flex-1 h-5 overflow-hidden rounded-full bg-plum-50">
                                <div
                                    class="h-full rounded-full bg-plum-600 transition-all"
                                    style="width: {{ round($row->labels / $max * 100) }}%"
                                ></div>
                            </div>
                            <p class="w-10 shrink-0 text-right text-sm font-semibold text-plum-800">{{ $row->labels }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-plum-50 px-5 py-3 text-right">
                    <p class="text-sm font-semibold text-plum-700">Total: {{ number_format($throughput->sum('labels')) }} labels</p>
                    <p class="text-xs text-plum-400">Average: {{ round($throughput->avg('labels'), 1) }} per {{ $groupBy }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
