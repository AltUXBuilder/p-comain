<x-layouts.app>
    <x-slot name="pageTitle">Inventory</x-slot>

    <div class="space-y-5 animate-fade-in">

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-plum-100 bg-white p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-plum-800">{{ $stats['total_products'] }}</p>
                <p class="text-xs text-plum-400">Products tracked</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-amber-700">{{ $stats['low_stock'] }}</p>
                <p class="text-xs text-amber-500">Low stock</p>
            </div>
            <div class="rounded-2xl border border-red-100 bg-red-50 p-4 text-center shadow-plum-sm">
                <p class="text-2xl font-bold text-red-700">{{ $stats['out_of_stock'] }}</p>
                <p class="text-xs text-red-400">Out of stock</p>
            </div>
        </div>

        {{-- Near-expiry alert strip --}}
        @if($nearExpiry30->isNotEmpty())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                <p class="mb-2 text-sm font-semibold text-red-800">⚠ {{ $nearExpiry30->count() }} batch(es) expiring within 30 days</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($nearExpiry30 as $b)
                        <a href="{{ route('stock.show', $b->product_id) }}"
                            class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                            {{ $b->product?->name }} · Batch {{ $b->batch_number }} · Exp {{ $b->expiry_date->format('d M Y') }}
                        </a>
                    @endforeach
                </div>
                <a href="{{ route('stock.near-expiry') }}" class="mt-2 block text-xs font-medium text-red-600 hover:text-red-800">View full near-expiry report →</a>
            </div>
        @endif

        {{-- Filter & actions --}}
        <div class="flex items-center justify-between gap-4">
            <form method="GET" action="{{ route('stock.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search product…"
                    class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                <select name="status" class="rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    <option value="">All</option>
                    <option value="low"  {{ request('status') === 'low' ? 'selected' : '' }}>Low stock</option>
                    <option value="out"  {{ request('status') === 'out' ? 'selected' : '' }}>Out of stock</option>
                </select>
                <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">Filter</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('stock.near-expiry') }}" class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-600 hover:bg-plum-50">Expiry Report</a>
                <a href="{{ route('stock.suppliers') }}" class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-600 hover:bg-plum-50">Suppliers</a>
                <a href="{{ route('stock.purchase-orders') }}" class="rounded-xl border border-plum-200 px-3 py-2 text-xs font-medium text-plum-600 hover:bg-plum-50">Purchase Orders</a>
            </div>
        </div>

        {{-- Stock table --}}
        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">On Hand</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Min Threshold</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 md:table-cell">Last Reconciled</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($stock as $item)
                        <tr class="hover:bg-plum-50/20 transition">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-plum-800">{{ $item->product?->name }}</p>
                                @if($item->product?->cold_chain)
                                    <span class="text-[10px] font-semibold text-blue-500">❄ Cold chain</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-lg font-bold {{ $item->isOutOfStock() ? 'text-red-600' : ($item->isBelowThreshold() ? 'text-amber-600' : 'text-plum-800') }}">
                                    {{ $item->quantity_on_hand }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-plum-600">{{ $item->minimum_threshold }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->statusColour() }}">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-400 md:table-cell">
                                {{ $item->last_reconciled_at?->format('d M Y') ?? 'Never' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('stock.show', $item->product_id) }}" class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-600 hover:bg-plum-50">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-plum-400">No stock records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stock->hasPages())
            <div class="flex justify-center">{{ $stock->links() }}</div>
        @endif

    </div>
</x-layouts.app>
