<x-patient-layout>
    <x-slot name="title">Prescription {{ $prescription->prescription_number }}</x-slot>
    <x-slot name="pageTitle">Prescription</x-slot>

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Main --}}
        <div class="lg:col-span-2 space-y-5">

            <div class="card p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-xs text-plum-400 mb-1">{{ $prescription->prescription_number }}</p>
                        <h2 class="font-display text-xl font-bold text-plum-800">{{ $prescription->product->name }}</h2>
                        <p class="text-sm text-plum-500 mt-0.5">
                            {{ $prescription->product->strength }} {{ $prescription->product->form }}
                        </p>
                    </div>
                    @php
                        $badge = match($prescription->status) {
                            'approved','sent_to_dispense' => 'badge-lilac',
                            'dispensed'                  => 'badge-green',
                            default                      => 'badge-amber',
                        };
                    @endphp
                    <span class="{{ $badge }} shrink-0 capitalize">{{ str_replace('_', ' ', $prescription->status) }}</span>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex gap-3 py-3 border-t border-plum-100">
                        <span class="w-32 shrink-0 text-plum-400">Dosage</span>
                        <span class="text-plum-800">{{ $prescription->dosage_instructions }}</span>
                    </div>
                    <div class="flex gap-3 py-3 border-t border-plum-100">
                        <span class="w-32 shrink-0 text-plum-400">Quantity</span>
                        <span class="text-plum-800">{{ $prescription->quantity }}</span>
                    </div>
                    <div class="flex gap-3 py-3 border-t border-plum-100">
                        <span class="w-32 shrink-0 text-plum-400">Valid from</span>
                        <span class="text-plum-800">{{ $prescription->valid_from?->format('j F Y') }}</span>
                    </div>
                    @if ($prescription->valid_until)
                        <div class="flex gap-3 py-3 border-t border-plum-100">
                            <span class="w-32 shrink-0 text-plum-400">Valid until</span>
                            <span class="text-plum-800 {{ $prescription->isExpired() ? 'text-red-600' : '' }}">
                                {{ $prescription->valid_until->format('j F Y') }}
                                @if ($prescription->isExpired()) (expired) @endif
                            </span>
                        </div>
                    @endif
                    <div class="flex gap-3 py-3 border-t border-plum-100">
                        <span class="w-32 shrink-0 text-plum-400">Prescribed by</span>
                        <span class="text-plum-800">{{ $prescription->prescriber_name }}</span>
                    </div>
                    <div class="flex gap-3 py-3 border-t border-plum-100">
                        <span class="w-32 shrink-0 text-plum-400">Pharmacy</span>
                        <span class="text-plum-800">{{ $prescription->pharmacy_name }}</span>
                    </div>
                </div>

                @if ($prescription->pdf_path)
                    <div class="mt-5 pt-5 border-t border-plum-100">
                        <a href="{{ route('patient.prescriptions.download', $prescription) }}"
                           class="btn-primary btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download prescription PDF
                        </a>
                    </div>
                @endif
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5 text-xs space-y-2.5">
                @if ($prescription->signed_at)
                    <div class="flex justify-between">
                        <span class="text-plum-400">Signed</span>
                        <span class="text-plum-700 font-medium">{{ $prescription->signed_at->format('j M Y') }}</span>
                    </div>
                @endif
                @if ($prescription->approved_at)
                    <div class="flex justify-between">
                        <span class="text-plum-400">Approved</span>
                        <span class="text-plum-700 font-medium">{{ $prescription->approved_at->format('j M Y') }}</span>
                    </div>
                @endif
                @if ($prescription->is_repeat)
                    <div class="pt-2 border-t border-plum-100">
                        <span class="badge-lilac">Repeat prescription</span>
                        @if ($prescription->next_repeat_due)
                            <p class="text-plum-400 mt-1">Next due: {{ $prescription->next_repeat_due->format('j M Y') }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Link to consultation --}}
            @if ($prescription->consultation)
                <a href="{{ route('patient.consultations.show', $prescription->consultation) }}"
                   class="card p-5 flex items-center justify-between hover:shadow-plum transition-shadow group">
                    <div>
                        <p class="text-xs text-plum-400">Related consultation</p>
                        <p class="text-sm font-medium text-plum-800 mt-0.5">View consultation</p>
                    </div>
                    <svg class="w-4 h-4 text-plum-300 group-hover:text-plum-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>
</x-patient-layout>
