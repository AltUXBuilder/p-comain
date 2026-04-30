<x-layouts.app>
    <x-slot name="pageTitle">Edit Prescription — {{ $prescription->prescription_number }}</x-slot>

    <div class="max-w-2xl animate-fade-in" x-data="{ isRepeat: {{ $prescription->is_repeat ? 'true' : 'false' }} }">

        <div class="mb-5">
            <a href="{{ route('prescriptions.show', $prescription) }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to prescription</a>
        </div>

        <div class="rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-plum-800">Prescription Builder</h2>
                <span class="font-mono text-xs text-plum-400">{{ $prescription->prescription_number }}</span>
            </div>

            {{-- Patient & product summary --}}
            <div class="mb-5 rounded-xl bg-plum-50/60 px-4 py-3">
                <p class="text-sm font-medium text-plum-800">{{ $prescription->patient?->full_name }}</p>
                <p class="text-xs text-plum-500">{{ $prescription->product?->name }} · {{ $prescription->product?->strength }} {{ $prescription->product?->form }}</p>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('prescriptions.update', $prescription) }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Dosage instructions --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">
                        Dosage Instructions <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="dosage_instructions"
                        rows="3"
                        required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="e.g. Inject 0.25mg subcutaneously once weekly for 4 weeks, then increase to 0.5mg once weekly."
                    >{{ old('dosage_instructions', $prescription->dosage_instructions) }}</textarea>
                </div>

                {{-- Quantity --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Quantity <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="quantity"
                        value="{{ old('quantity', $prescription->quantity) }}"
                        required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="e.g. 4 pre-filled pens (1mg/0.5ml)"
                    >
                </div>

                {{-- Prescriber notes --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Prescriber Notes <span class="text-plum-400 font-normal">(printed on PDF)</span></label>
                    <textarea
                        name="prescriber_notes"
                        rows="2"
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="Optional clinical notes to be printed on the prescription…"
                    >{{ old('prescriber_notes', $prescription->prescriber_notes) }}</textarea>
                </div>

                {{-- Repeat --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm font-medium text-plum-700">
                        <input
                            type="checkbox"
                            name="is_repeat"
                            value="1"
                            @change="isRepeat = $event.target.checked"
                            class="size-4 rounded border-plum-300 text-lilac-500"
                            {{ old('is_repeat', $prescription->is_repeat) ? 'checked' : '' }}
                        >
                        This is a repeat prescription
                    </label>

                    <div x-show="isRepeat" x-cloak>
                        <label class="mb-1.5 block text-sm font-medium text-plum-700">Repeat interval (days)</label>
                        <input
                            type="number"
                            name="repeat_interval_days"
                            value="{{ old('repeat_interval_days', $prescription->repeat_interval_days) }}"
                            min="7"
                            max="365"
                            class="block w-40 rounded-xl border-plum-200 py-2.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                            placeholder="e.g. 28"
                        >
                        <p class="mt-1 text-xs text-plum-400">After this prescription is dispensed, a new one will auto-generate after this many days.</p>
                    </div>
                </div>

                {{-- Legal wording --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Legal Wording</label>
                    <textarea
                        name="legal_wording"
                        rows="4"
                        required
                        class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-600 focus:border-lilac-500 focus:ring-lilac-500"
                    >{{ old('legal_wording', $prescription->legal_wording ?? \App\Models\Prescription::DEFAULT_LEGAL_WORDING) }}</textarea>
                    <p class="mt-1 text-xs text-plum-400">This text is printed verbatim on the prescription PDF. Amend only if clinically required.</p>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-plum-50">
                    <a href="{{ route('prescriptions.show', $prescription) }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                    <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                        Save Prescription
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-layouts.app>
