<x-layouts.app>
    <x-slot name="pageTitle">Purchase Orders</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showCreate: false }">

        <div class="flex items-center justify-between">
            <a href="{{ route('stock.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Inventory</a>
            <button @click="showCreate = true" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200 hover:bg-plum-900">+ New PO</button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-plum-100 bg-white shadow-plum-sm">
            <table class="min-w-full divide-y divide-plum-100">
                <thead class="bg-plum-50/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">PO Number</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500">Status</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Expected</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-plum-500 sm:table-cell">Items</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-plum-50">
                    @forelse($orders as $po)
                        <tr class="hover:bg-plum-50/10">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-plum-700">{{ $po->po_number }}</td>
                            <td class="px-4 py-3 text-sm text-plum-700">{{ $po->supplier?->name }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ match($po->status) {
                                        'draft'     => 'bg-plum-100 text-plum-600',
                                        'sent'      => 'bg-blue-100 text-blue-700',
                                        'received'  => 'bg-green-100 text-green-700',
                                        'partial'   => 'bg-amber-100 text-amber-700',
                                        'cancelled' => 'bg-gray-100 text-gray-500',
                                        default     => 'bg-plum-100 text-plum-600',
                                    } }}">
                                    {{ $po->statusLabel() }}
                                </span>
                            </td>
                            <td class="hidden px-4 py-3 text-xs text-plum-500 sm:table-cell">{{ $po->expected_date?->format('d M Y') ?? '—' }}</td>
                            <td class="hidden px-4 py-3 text-sm text-plum-600 sm:table-cell">{{ $po->items->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                @if(! in_array($po->status, ['received', 'cancelled']))
                                    <form method="POST" action="{{ route('stock.purchase-orders.status', $po) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <select name="status" onchange="this.form.submit()"
                                            class="rounded-lg border-plum-200 py-1 px-2 text-xs focus:border-lilac-500 focus:ring-lilac-500">
                                            @foreach(\App\Models\PurchaseOrder::STATUSES as $k => $l)
                                                <option value="{{ $k }}" {{ $po->status === $k ? 'selected' : '' }}>{{ $l }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-plum-400">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="flex justify-center">{{ $orders->links() }}</div>
        @endif

    </div>
</x-layouts.app>
