<x-layouts.app>
    <x-slot name="pageTitle">NPS Report</x-slot>
    <div class="space-y-5 animate-fade-in">
        <div class="flex items-center justify-between">
            <a href="{{ route('analytics.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Analytics</a>
            <div class="flex items-center gap-3">
                <div class="flex gap-1">
                    @foreach([30, 60, 90, 180] as $d)
                        <a href="{{ route('analytics.nps', ['days' => $d]) }}"
                            @class(['rounded-xl px-3 py-1.5 text-xs font-medium transition border',
                                'bg-plum-800 text-lilac-200 border-plum-800' => $days == $d,
                                'border-plum-200 text-plum-600 hover:bg-plum-50' => $days != $d])>
                            {{ $d }}d
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('analytics.export.nps', ['days' => $days]) }}" class="rounded-xl border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">Export CSV</a>
            </div>
        </div>

        {{-- Main NPS score --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-plum-100 bg-white p-5 text-center shadow-plum-sm col-span-2 sm:col-span-1">
                <p class="text-5xl font-bold {{ $summary['nps'] === null ? 'text-plum-300' : ($summary['nps'] >= 50 ? 'text-green-600' : ($summary['nps'] >= 0 ? 'text-amber-600' : 'text-red-600')) }}">
                    {{ $summary['nps'] !== null ? ($summary['nps'] >= 0 ? '+' : '') . $summary['nps'] : '—' }}
                </p>
                <p class="mt-1 text-xs font-semibold text-plum-500">NPS · {{ $summary['total'] }} responses</p>
            </div>
            <div class="rounded-2xl border border-green-100 bg-green-50 p-5 text-center shadow-plum-sm">
                <p class="text-3xl font-bold text-green-700">{{ number_format($summary['promoters']) }}</p>
                <p class="text-xs text-green-600">Promoters <span class="text-green-400">(9–10)</span></p>
            </div>
            <div class="rounded-2xl border border-plum-100 bg-white p-5 text-center shadow-plum-sm">
                <p class="text-3xl font-bold text-plum-600">{{ number_format($summary['passives']) }}</p>
                <p class="text-xs text-plum-400">Passives <span class="text-plum-300">(7–8)</span></p>
            </div>
            <div class="rounded-2xl border border-red-100 bg-red-50 p-5 text-center shadow-plum-sm">
                <p class="text-3xl font-bold text-red-600">{{ number_format($summary['detractors']) }}</p>
                <p class="text-xs text-red-400">Detractors <span class="text-red-300">(0–6)</span></p>
            </div>
        </div>

        {{-- NPS trend table --}}
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm overflow-hidden">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Monthly NPS Trend</h3>
            </div>
            <div class="divide-y divide-plum-50">
                @foreach($summary['trend'] as $month)
                    <div class="flex items-center gap-4 px-5 py-3">
                        <p class="w-20 shrink-0 text-xs font-medium text-plum-600">{{ $month['month'] }}</p>
                        <div class="flex-1">
                            @if($month['nps'] !== null)
                                @php $w = max(5, min(100, ($month['nps'] + 100) / 2)); @endphp
                                <div class="h-4 overflow-hidden rounded-full bg-plum-50">
                                    <div class="h-full rounded-full {{ $month['nps'] >= 50 ? 'bg-green-500' : ($month['nps'] >= 0 ? 'bg-amber-400' : 'bg-red-400') }}"
                                        style="width: {{ $w }}%"></div>
                                </div>
                            @else
                                <div class="h-4 rounded-full bg-plum-50"></div>
                            @endif
                        </div>
                        <p class="w-12 shrink-0 text-right text-sm font-semibold {{ $month['nps'] === null ? 'text-plum-300' : ($month['nps'] >= 0 ? 'text-plum-800' : 'text-red-600') }}">
                            {{ $month['nps'] !== null ? ($month['nps'] >= 0 ? '+' : '') . $month['nps'] : '—' }}
                        </p>
                        <p class="w-12 shrink-0 text-right text-xs text-plum-400">{{ $month['total'] }} resp.</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent comments --}}
        @if($summary['recent']->isNotEmpty())
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Recent Comments</h3>
            </div>
            <div class="divide-y divide-plum-50">
                @foreach($summary['recent'] as $score)
                    <div class="flex items-start gap-4 px-5 py-4">
                        <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold
                            {{ $score->category === 'promoter' ? 'bg-green-100 text-green-700' :
                               ($score->category === 'passive' ? 'bg-plum-100 text-plum-600' : 'bg-red-100 text-red-600') }}">
                            {{ $score->score }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-plum-700">{{ $score->comment }}</p>
                            <p class="mt-1 text-xs text-plum-400">
                                {{ $score->patient?->full_name ?? 'Anonymous' }}
                                · {{ $score->collected_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-layouts.app>
