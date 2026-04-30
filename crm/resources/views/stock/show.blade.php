<x-layouts.app>
    <x-slot name="pageTitle">{{ $product->name }} — Stock</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showReceive: false, showWriteOff: false, writeOffBatch: null }">

        <a href="{{ route('stock.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Inventory</a>

        {{-- Product header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-plum-800">{{ $product->name }}</h2>
                <p class="text-sm text-plum-400">
                    {{ $product->strength }} {{ $product->form }}
                    @if($product->cold_chain) &nbsp;· <span class="text-blue-500 font-medium">❄ Cold chain</span> @endif
                </p>
            </div>
            <div class="flex gap-2">
                <button @click="showReceive = true" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                    + Receive Stock
                </button>
                <form method="POST" action="{{ route('stock.reconcile', $product) }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">Reconcile</button>
                </form>
            </div>
        </div>

        {{-- Aggregate summary --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold {{ $stock->isOutOfStock() ? 'text-red-600' : ($stock->isBelowThreshold() ? 'text-amber-600' : 'text-plum-800') }}">
                    {{ $stock->quantity_on_hand }}
                </p>
                <p class="text-xs text-plum-400">On hand</p>
            </div>
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">{{ $stock->minimum_threshold }}</p>
                <p class="text-xs text-plum-400">Min threshold</p>
            </div>
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $stock->statusColour() }}">{{ $stock->statusLabel() }}</span>
            </div>
        </div>

        {{-- Update threshold --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="POST" action="{{ route('stock.threshold', $product) }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-medium text-plum-500">Minimum stock threshold</label>
                    <input type="number" name="minimum_threshold" value="{{ $stock->minimum_threshold }}" min="0"
                        class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Update</button>
            </form>
        </div>

        {{-- Batches --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Stock Batches</h3>
            </div>
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Expiry</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Remaining</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-700">{{ $batch->batch_number }}</td>
                            <td class="px-4 py-3 text-sm {{ $batch->isExpired() ? 'text-red-600 font-semibold' : ($batch->isNearExpiry(30) ? 'text-red-500' : ($batch->isNearExpiry(90) ? 'text-amber-600' : 'text-plum-700')) }}">
                                {{ $batch->expiry_date?->format('d M Y') }}
                                @if($batch->isExpired()) <span class="text-xs">(Expired)</span>
                                @elseif($batch->isNearExpiry(30)) <span class="text-xs">({{ $batch->expiry_date->diffForHumans() }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-plum-800">
                                {{ $batch->quantity_remaining }} / {{ $batch->quantity_received }}
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 sm:table-cell">
                                {{ $batch->received_date?->format('d M Y') }}<br>
                                <span class="text-plum-300">{{ $batch->supplier?->name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                    {{ match($batch->status) {
                                        'active'     => 'bg-green-100 text-green-700',
                                        'depleted'   => 'bg-gray-100 text-gray-500',
                                        'written_off' => 'bg-red-100 text-red-600',
                                        'recalled'   => 'bg-red-200 text-red-800',
                                        default      => 'bg-plum-100 text-plum-600',
                                    } }}">
                                    {{ ucfirst(str_replace('_', ' ', $batch->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($batch->status === 'active' && $batch->quantity_remaining > 0)
                                    <button
                                        @click="showWriteOff = true; writeOffBatch = {{ $batch->id }}"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                    >Write off</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-plum-400">No batches on record.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Write-off log --}}
        @if($writeOffs->isNotEmpty())
        <div class="rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <div class="border-b border-plum-50 px-5 py-4">
                <h3 class="text-sm font-semibold text-plum-700">Write-off Log</h3>
            </div>
            @foreach($writeOffs as $wo)
                <div class="flex items-center justify-between border-b border-plum-50 px-5 py-3 last:border-0">
                    <div>
                        <p class="text-sm text-plum-700">{{ $wo->quantity }} units — {{ ucfirst($wo->reason) }}</p>
                        @if($wo->notes) <p class="text-xs text-plum-400">{{ $wo->notes }}</p> @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-medium text-plum-600">{{ $wo->staff?->full_name }}</p>
                        <p class="text-xs text-plum-400">{{ $wo->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Receive stock modal --}}
        <div x-show="showReceive" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Receive Stock — {{ $product->name }}</h3>
                <form method="POST" action="{{ route('stock.receive', $product) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="mb-1 block text-xs font-medium text-plum-600">Supplier</label>
                            <select name="supplier_id" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                                <option value="">Select supplier…</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Batch number</label>
                            <input type="text" name="batch_number" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Expiry date</label>
                            <input type="date" name="expiry_date" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Qty received</label>
                            <input type="number" name="quantity_received" min="1" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Unit cost (£)</label>
                            <input type="number" name="unit_cost" step="0.01" min="0" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Received date</label>
                            <input type="date" name="received_date" value="{{ today()->format('Y-m-d') }}" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        @if($product->cold_chain)
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="cold_chain_maintained" value="1" checked class="size-4 rounded border-plum-300 text-blue-500">
                            <label class="text-xs text-plum-600">Cold chain maintained</label>
                        </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showReceive = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Receive</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Write-off modal --}}
        <div x-show="showWriteOff" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Write Off Stock</h3>
                <template x-for="batch in [writeOffBatch]" :key="batch">
                    <form :action="`/stock/batch/${writeOffBatch}/write-off`" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Quantity</label>
                            <input type="number" name="quantity" min="1" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Reason</label>
                            <select name="reason" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                                @foreach(\App\Models\StockWriteOff::REASONS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-600">Notes (optional)</label>
                            <textarea name="notes" rows="2" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showWriteOff = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Write Off</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-layouts.app>
