<x-layouts.app>
    <x-slot name="pageTitle">Orders</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ selected: [], carrier: 'royal_mail', showBatch: false }">

        {{-- Status tabs --}}
        <div class="flex gap-1.5 overflow-x-auto pb-1">
            <a href="{{ route('orders.index') }}"
                @class(['rounded-xl px-3 py-2 text-xs font-medium transition border',
                    'bg-plum-800 text-lilac-200 border-plum-800' => ! request('status'),
                    'border-plum-200 text-plum-600 hover:bg-plum-50' => request('status')])>
                All ({{ array_sum($counts) }})
            </a>
            @foreach(\App\Models\Order::STATUSES as $key => $label)
                <a href="{{ route('orders.index', array_merge(request()->query(), ['status' => $key])) }}"
                    @class(['rounded-xl px-3 py-2 text-xs font-medium transition border shrink-0',
                        'bg-plum-800 text-lilac-200 border-plum-800' => request('status') === $key,
                        'border-plum-200 text-plum-600 hover:bg-plum-50' => request('status') !== $key])>
                    {{ $label }} ({{ $counts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>

        {{-- Search / filter bar --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Order number, patient name, tracking…"
                        class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                </div>
                <select name="carrier" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">All carriers</option>
                    @foreach(\App\Models\Order::CARRIERS as $k => $v)
                        <option value="{{ $k }}" {{ request('carrier') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-1.5 text-sm text-plum-600">
                    <input type="checkbox" name="cold_chain" value="1" class="size-4 rounded border-plum-300 text-blue-500" {{ request()->boolean('cold_chain') ? 'checked' : '' }}>
                    Cold chain
                </label>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Search</button>
            </form>
        </div>

        {{-- Batch dispatch controls --}}
        <div class="flex items-center justify-between" x-show="selected.length > 0" x-cloak>
            <p class="text-sm text-plum-600"><span x-text="selected.length"></span> order(s) selected</p>
            <button @click="showBatch = true" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">
                Batch Dispatch
            </button>
        </div>

        {{-- Orders table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="w-8 px-4 py-3">
                            <input type="checkbox" class="size-4 rounded border-plum-300 text-lilac-500"
                                @change="selected = $event.target.checked ? Array.from(document.querySelectorAll('.ord-check')).map(c => c.value) : []">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Order</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Patient</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 lg:table-cell">Carrier</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($orders as $order)
                        <tr class="hover:bg-plum-50/20 transition">
                            <td class="px-4 py-3">
                                <input type="checkbox" value="{{ $order->id }}" class="ord-check size-4 rounded border-plum-300 text-lilac-500" x-model="selected">
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-mono text-xs font-semibold text-plum-600">{{ $order->order_number }}</p>
                                <p class="text-xs text-plum-400">{{ $order->created_at->format('d M Y') }}</p>
                                @if($order->requires_cold_chain)
                                    <span class="text-[10px] font-semibold text-blue-500">❄</span>
                                @endif
                            </td>
                            <td class="hidden px-4 py-3 text-sm text-plum-700 sm:table-cell">{{ $order->patient?->full_name ?? '—' }}</td>
                            <td class="hidden px-4 py-3 text-sm font-medium text-plum-800 md:table-cell">£{{ number_format($order->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $order->statusColour() }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-500 lg:table-cell">
                                {{ $order->carrierLabel() }}
                                @if($order->tracking_number)
                                    <br><span class="font-mono text-plum-400">{{ $order->tracking_number }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('orders.show', $order) }}" class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-plum-400">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="flex justify-center">{{ $orders->links() }}</div>
        @endif

        {{-- Royal Mail manifest export --}}
        <div class="rounded-2xl border border-plum-100 bg-white p-4 shadow-plum-sm">
            <h3 class="mb-3 text-sm font-semibold text-plum-700">Royal Mail Manifest Export</h3>
            <form method="GET" action="{{ route('orders.manifest-export') }}" class="flex gap-3">
                <input type="text" name="manifest_batch" placeholder="Manifest batch reference…"
                    class="flex-1 rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <button type="submit" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">Export CSV</button>
            </form>
        </div>

        {{-- Batch dispatch modal --}}
        <div x-show="showBatch" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Batch Dispatch</h3>
                <p class="mb-4 text-sm text-plum-500"><span x-text="selected.length"></span> order(s) selected. Enter a tracking number for each:</p>
                <form method="POST" action="{{ route('orders.batch-dispatch') }}" id="batch-dispatch-form">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-medium text-plum-600">Carrier</label>
                        <select name="carrier" x-model="carrier" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            @foreach(\App\Models\Order::CARRIERS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="max-h-60 overflow-y-auto space-y-2 mb-4">
                        <template x-for="(id, i) in selected" :key="id">
                            <div class="flex gap-2 items-center">
                                <input type="hidden" :name="`orders[${i}][id]`" :value="id">
                                <span class="w-8 text-xs text-plum-400" x-text="id"></span>
                                <input type="text" :name="`orders[${i}][tracking_number]`" placeholder="Tracking number" required
                                    class="flex-1 rounded-xl border-plum-200 py-1.5 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showBatch = false; selected = []" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2 text-sm font-semibold text-lilac-200 hover:bg-plum-900">Dispatch All</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
