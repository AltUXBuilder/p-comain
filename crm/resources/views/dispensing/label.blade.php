<x-layouts.app>
    <x-slot name="pageTitle">Label — {{ $label->prescription?->prescription_number }}</x-slot>

    <div class="max-w-xl space-y-5 animate-fade-in">

        <a href="{{ route('dispensing.queue') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to queue</a>

        {{-- Success banner --}}
        @if(session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <svg class="size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Cold chain reminder --}}
        @if($label->cold_chain)
            <div class="flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                <span class="text-xl">❄</span>
                <p class="text-sm font-semibold text-blue-800">Cold chain — ensure refrigerated packaging is used for dispatch.</p>
            </div>
        @endif

        {{-- Label detail card --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-semibold text-plum-800">Dispensing Label</h2>
                <div class="flex gap-2">
                    <a href="{{ route('dispensing.label.pdf', $label) }}" target="_blank"
                        class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">
                        View PDF
                    </a>
                    <a href="{{ route('dispensing.label.download', $label) }}"
                        class="rounded-lg bg-plum-800 px-3 py-1.5 text-xs font-medium text-lilac-200 hover:bg-plum-900">
                        Download
                    </a>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                <div>
                    <dt class="text-xs text-plum-400">Patient</dt>
                    <dd class="font-medium text-plum-800">{{ $label->patient_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Dispensing date</dt>
                    <dd class="text-plum-700">{{ $label->dispensing_date->format('d M Y') }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs text-plum-400">Medication</dt>
                    <dd class="font-semibold text-plum-800">{{ $label->medication_name }} {{ $label->medication_strength }} {{ $label->medication_form }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-xs text-plum-400">Dosage instructions</dt>
                    <dd class="text-plum-700">{{ $label->dosage_instructions }}</dd>
                </div>
                @if($label->batch_number)
                <div>
                    <dt class="text-xs text-plum-400">Batch number</dt>
                    <dd class="font-mono text-plum-700">{{ $label->batch_number }}</dd>
                </div>
                @endif
                @if($label->expiry_date)
                <div>
                    <dt class="text-xs text-plum-400">Expiry date</dt>
                    <dd class="{{ $label->expiryColour() }}">{{ $label->expiry_date->format('m/Y') }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs text-plum-400">Dispensed by</dt>
                    <dd class="text-plum-700">{{ $label->dispensed_by_name }}
                        @if($label->dispensed_by_gphc)
                            <span class="font-mono text-plum-400">({{ $label->dispensed_by_gphc }})</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Format</dt>
                    <dd class="capitalize text-plum-700">{{ $label->format }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-plum-400">Status</dt>
                    <dd>
                        @if($label->printed)
                            <span class="text-xs font-medium text-green-600">✓ Printed {{ $label->printed_at?->format('d M Y, H:i') }}</span>
                        @else
                            <span class="text-xs text-amber-600">Not yet printed</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- PDF preview --}}
        @if($label->pdf_path)
            <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                <p class="mb-3 text-sm font-semibold text-plum-700">Label Preview (70×35mm)</p>
                <div class="flex justify-center rounded-xl bg-gray-100 p-4">
                    <iframe
                        src="{{ route('dispensing.label.pdf', $label) }}"
                        class="rounded border border-plum-200 shadow"
                        style="width: 264px; height: 132px;"
                        title="Label preview"
                    ></iframe>
                </div>
                <p class="mt-2 text-center text-xs text-plum-400">Preview is to scale at 96dpi. Print at 203dpi for exact 70×35mm output.</p>
            </div>
        @endif

        {{-- Mark printed --}}
        @if(! $label->printed)
            <form method="POST" action="{{ route('labels.mark-printed') }}">
                @csrf
                <input type="hidden" name="ids[]" value="{{ $label->id }}">
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-plum-200 px-4 py-3 text-sm font-medium text-plum-600 hover:bg-plum-50">
                    Mark as printed
                </button>
            </form>
        @endif

        {{-- Prescription link --}}
        <a href="{{ route('prescriptions.show', $label->prescription) }}" class="block text-center text-xs font-medium text-lilac-600 hover:text-lilac-800">
            View prescription {{ $label->prescription?->prescription_number }} →
        </a>

    </div>
</x-layouts.app>
