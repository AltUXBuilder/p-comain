<x-patient-layout>
    <x-slot name="title">My Prescriptions</x-slot>
    <x-slot name="pageTitle">My Prescriptions</x-slot>

    @if ($prescriptions->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-lilac-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h2 class="font-display text-xl font-bold text-plum-800 mb-2">No prescriptions yet</h2>
            <p class="text-plum-500 text-sm">Prescriptions appear here once a consultation has been approved.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($prescriptions as $prescription)
                <div class="card px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-medium text-plum-800 text-sm">{{ $prescription->product->name }}</p>
                                <span class="text-xs text-plum-400">· {{ $prescription->prescription_number }}</span>
                            </div>
                            <p class="text-xs text-plum-400">
                                {{ $prescription->product->treatment->category->name ?? '' }}
                                · Issued {{ $prescription->valid_from?->format('j M Y') }}
                            </p>
                            <p class="text-xs text-plum-500 mt-1.5">{{ $prescription->dosage_instructions }}</p>
                        </div>
                        @php
                            $statusColor = match($prescription->status) {
                                'approved', 'sent_to_dispense' => 'badge-lilac',
                                'dispensed'                    => 'badge-green',
                                'archived', 'cancelled'        => 'badge-amber',
                                default                        => 'badge-lilac',
                            };
                        @endphp
                        <span class="{{ $statusColor }} shrink-0 capitalize">{{ str_replace('_', ' ', $prescription->status) }}</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-plum-100 flex gap-3">
                        <a href="{{ route('patient.prescriptions.show', $prescription) }}"
                           class="btn-secondary btn-sm text-xs">View details</a>
                        @if ($prescription->pdf_path)
                            <a href="{{ route('patient.prescriptions.download', $prescription) }}"
                               class="btn-ghost btn-sm text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download PDF
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $prescriptions->links() }}</div>
    @endif

</x-patient-layout>
