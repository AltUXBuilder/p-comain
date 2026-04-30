<x-patient-layout>
    <x-slot name="title">My Orders</x-slot>
    <x-slot name="pageTitle">My Orders</x-slot>

    @if ($orders->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-lilac-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-plum-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h2 class="font-display text-xl font-bold text-plum-800 mb-2">No orders yet</h2>
            <p class="text-plum-500 text-sm">Your orders will appear here once a prescription is approved and you complete checkout.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <a href="{{ route('patient.orders.show', $order) }}"
                   class="card px-6 py-5 flex items-center gap-4 hover:shadow-plum transition-shadow group">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-medium text-plum-800">{{ $order->order_number }}</p>
                            @if ($order->requires_cold_chain)
                                <span class="badge-plum text-xs">Cold chain</span>
                            @endif
                        </div>
                        <p class="text-xs text-plum-400">
                            {{ $order->items->pluck('product_name')->join(', ') }}
                            · {{ $order->created_at->format('j M Y') }}
                        </p>
                        @if ($order->tracking_number)
                            <p class="text-xs text-plum-500 mt-0.5">Tracking: {{ $order->tracking_number }}</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        @php
                            $badge = match($order->status) {
                                'dispatched'        => 'badge-lilac',
                                'delivered'         => 'badge-green',
                                'payment_confirmed','processing' => 'badge-amber',
                                'cancelled','refunded' => 'badge-red',
                                default             => 'badge-lilac',
                            };
                        @endphp
                        <span class="{{ $badge }} capitalize mb-1 inline-flex">{{ str_replace('_', ' ', $order->status) }}</span>
                        <p class="text-sm font-medium text-plum-800">£{{ number_format($order->total, 2) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-plum-300 group-hover:text-plum-600 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif

</x-patient-layout>
