<x-layouts.app>
    <x-slot name="pageTitle">Dispense — {{ $prescription->prescription_number }}</x-slot>

    <div class="max-w-xl animate-fade-in">

        <div class="mb-5">
            <a href="{{ route('dispensing.queue') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Back to queue</a>
        </div>

        {{-- Near expiry alert --}}
        @if($nearExpiry->isNotEmpty())
            <div class="mb-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                <svg class="mt-0.5 size-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">Near-expiry stock</p>
                    @foreach($nearExpiry as $b)
                        <p class="text-xs text-amber-700">Batch {{ $b->batch_number }}: expires {{ $b->expiry_date->format('d M Y') }} ({{ $b->expiry_date->diffForHumans() }})</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Cold chain alert --}}
        @if($prescription->product?->cold_chain)
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                <span class="text-lg">❄</span>
                <div>
                    <p class="text-sm font-semibold text-blue-800">Cold chain product</p>
                    <p class="text-xs text-blue-600">This product requires refrigerated storage (2–8°C). Ensure cold chain is maintained throughout dispensing and dispatch.</p>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-plum-100 bg-white p-6 shadow-plum-sm">

            {{-- Prescription summary --}}
            <div class="mb-5 rounded-xl bg-plum-50/60 px-4 py-3">
                <div class="flex justify-between">
                    <div>
                        <p class="font-mono text-xs font-semibold text-plum-500">{{ $prescription->prescription_number }}</p>
                        <p class="text-sm font-semibold text-plum-800">{{ $prescription->patient?->full_name }}</p>
                        <p class="text-xs text-plum-500">{{ $prescription->product?->name }} · {{ $prescription->product?->strength }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-plum-400">Dosage</p>
                        <p class="text-xs font-medium text-plum-700 max-w-40">{{ $prescription->dosage_instructions }}</p>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('dispensing.dispense', $prescription) }}" class="space-y-5">
                @csrf

                {{-- Batch selection --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Stock Batch <span class="text-plum-400 font-normal">(optional)</span></label>
                    <select name="stock_batch_id" class="block w-full rounded-xl border-plum-200 py-2.5 px-3 text-sm text-plum-800 focus:border-lilac-500 focus:ring-lilac-500">
                        <option value="">— No batch selected —</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" {{ $loop->first ? 'selected' : '' }}>
                                Batch {{ $batch->batch_number }}
                                · Exp {{ $batch->expiry_date?->format('m/Y') ?? 'N/A' }}
                                · {{ $batch->quantity_remaining }} remaining
                                @if($batch->isNearExpiry(30)) ⚠ @endif
                            </option>
                        @endforeach
                    </select>
                    @if($batches->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">No available batches found for this product. You can still dispense without a batch.</p>
                    @else
                        <p class="mt-1 text-xs text-plum-400">Batches are ordered FEFO (first-expiry first-out). First option is recommended.</p>
                    @endif
                </div>

                {{-- Label format --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-plum-700">Label Format</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm text-plum-700">
                            <input type="radio" name="format" value="standard" checked class="text-lilac-500">
                            Standard
                        </label>
                        <label class="flex items-center gap-2 text-sm text-plum-700">
                            <input type="radio" name="format" value="branded" class="text-lilac-500">
                            Branded (P&amp;Co)
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-plum-50 pt-4">
                    <a href="{{ route('dispensing.queue') }}" class="rounded-xl border border-plum-200 px-4 py-2.5 text-sm text-plum-500 hover:bg-plum-50">Cancel</a>
                    <button type="submit" class="flex items-center gap-2 rounded-xl bg-plum-800 px-5 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Confirm &amp; Generate Label
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-layouts.app>
