<x-patient-layout>
    <x-slot name="title">My Consultations</x-slot>
    <x-slot name="pageTitle">My Consultations</x-slot>

    @if ($consultations->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-lilac-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="font-display text-xl font-bold text-plum-800 mb-2">No consultations yet</h2>
            <p class="text-plum-500 text-sm mb-6">Start a consultation to get a prescription for one of our treatments.</p>
            <a href="{{ route('treatments.index') }}" class="btn-primary">Browse treatments</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($consultations as $consultation)
                <a href="{{ route('patient.consultations.show', $consultation) }}"
                   class="card px-6 py-5 flex items-center gap-4 hover:shadow-plum transition-shadow group">

                    {{-- Category icon dot --}}
                    <div class="w-10 h-10 rounded-xl bg-plum-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-plum-800 text-sm">{{ $consultation->product->name }}</p>
                        <p class="text-xs text-plum-400 mt-0.5">
                            {{ $consultation->product->treatment->category->name ?? '' }}
                            · {{ $consultation->submitted_at?->format('j M Y') ?? $consultation->created_at->format('j M Y') }}
                        </p>
                    </div>

                    <x-patient.consultation-status-badge :status="$consultation->status" />

                    <svg class="w-4 h-4 text-plum-300 group-hover:text-plum-600 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $consultations->links() }}</div>
    @endif

</x-patient-layout>
