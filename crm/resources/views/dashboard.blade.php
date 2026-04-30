<x-layouts.app>
    <x-slot name="pageTitle">Dashboard</x-slot>

    <div class="space-y-6 animate-fade-in">

        {{-- Greeting --}}
        <div>
            <h2 class="text-xl font-semibold text-plum-800">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth('staff')->user()->first_name }}
            </h2>
            <p class="mt-0.5 text-sm text-plum-400">{{ now()->format('l, d F Y') }}</p>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

            @foreach([
                ['label' => 'Awaiting Review',  'value' => $stats['consultations_pending'], 'color' => 'plum',   'route' => 'consultations.queue'],
                ['label' => 'Flagged',           'value' => $stats['consultations_flagged'], 'color' => 'amber',  'route' => 'consultations.queue'],
                ['label' => 'Total Patients',    'value' => number_format($stats['patients_total']),   'color' => 'plum',  'route' => 'patients.index'],
                ['label' => 'High Risk Patients','value' => $stats['patients_flagged'],       'color' => 'red',    'route' => 'patients.index'],
            ] as $card)
            <a
                href="{{ route($card['route']) }}"
                class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm transition hover:shadow-plum hover:-translate-y-0.5"
            >
                <p class="text-2xl font-bold text-plum-800">{{ $card['value'] }}</p>
                <p class="mt-0.5 text-sm text-plum-400">{{ $card['label'] }}</p>
            </a>
            @endforeach

        </div>

        {{-- Recent consultation queue preview --}}
        @if(auth('staff')->user()->hasAnyRole(['super_admin', 'superintendent_pharmacist', 'prescriber']))
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="flex items-center justify-between border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-800">Consultations Awaiting Review</h3>
                <a href="{{ route('consultations.queue') }}" class="text-xs font-medium text-lilac-600 hover:text-lilac-800">View all →</a>
            </div>
            @forelse($recentConsultations as $c)
                <div class="flex items-center justify-between border-b border-plum-50 px-5 py-3 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-plum-800">{{ $c->patient?->full_name ?? 'Unknown patient' }}</p>
                        <p class="text-xs text-plum-400">{{ $c->product?->name }} · {{ $c->created_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('consultations.show', $c) }}" class="rounded-lg border border-plum-200 px-3 py-1 text-xs font-medium text-plum-600 hover:bg-plum-50">Review</a>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-sm text-plum-400">Queue is clear.</div>
            @endforelse
        </div>
        @endif

    </div>
</x-layouts.app>
