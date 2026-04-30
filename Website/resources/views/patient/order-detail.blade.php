<x-patient-layout>
    <x-slot name="title">Order {{ $order->order_number }}</x-slot>
    <x-slot name="pageTitle">Order {{ $order->order_number }}</x-slot>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- Status tracker --}}
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold text-plum-800 mb-5">Order status</h2>
                <livewire:patient.order-tracker :order="$order" />
            </div>

            {{-- Items --}}
            <div class="card p-6">
                <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Items</h2>
                <div class="space-y-3">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-4 py-2 border-b border-plum-50 last:border-0">
                            <div>
                                <p class="text-sm font-medium text-plum-800">{{ $item->product_name }}</p>
                                @if ($item->product_strength)
                                    <p class="text-xs text-plum-400">{{ $item->product_strength }} {{ $item->product_form }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-plum-700">Qty: {{ $item->quantity }}</p>
                                <p class="text-sm font-medium text-plum-800">£{{ number_format($item->line_total, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="pt-4 space-y-1 text-sm">
                    <div class="flex justify-between text-plum-500">
                        <span>Subtotal</span><span>£{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if ($order->vat_amount > 0)
                        <div class="flex justify-between text-plum-500">
                            <span>VAT</span><span>£{{ number_format($order->vat_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-plum-500">
                        <span>Shipping</span>
                        <span>{{ $order->shipping_cost > 0 ? '£'.number_format($order->shipping_cost, 2) : 'Free' }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-plum-800 pt-2 border-t border-plum-100">
                        <span>Total</span><span>£{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5 text-sm space-y-3">
                <h3 class="font-display text-base font-bold text-plum-800">Delivery address</h3>
                <address class="not-italic text-plum-600 text-sm leading-relaxed">
                    {{ $order->delivery_name }}<br>
                    {{ $order->delivery_address_line_1 }}<br>
                    @if ($order->delivery_address_line_2){{ $order->delivery_address_line_2 }}<br>@endif
                    {{ $order->delivery_city }}<br>
                    {{ $order->delivery_postcode }}
                </address>
            </div>

            @if ($order->tracking_number)
                <div class="card p-5">
                    <h3 class="font-display text-base font-bold text-plum-800 mb-2">Tracking</h3>
                    <p class="text-xs text-plum-500 mb-1">{{ ucfirst(str_replace('_', ' ', $order->carrier ?? '')) }}</p>
                    <p class="text-sm font-mono text-plum-700">{{ $order->tracking_number }}</p>
                    @if ($order->tracking_url)
                        <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener"
                           class="btn-primary btn-sm w-full justify-center mt-3 text-xs">Track parcel →</a>
                    @endif
                </div>
            @endif

            @if ($order->invoice)
                <div class="card p-5">
                    <h3 class="font-display text-base font-bold text-plum-800 mb-2">Invoice</h3>
                    <p class="text-xs text-plum-500">{{ $order->invoice->invoice_number }}</p>
                    <p class="text-sm font-medium text-plum-800 mt-1">£{{ number_format($order->invoice->total, 2) }}</p>
                </div>
            @endif
        </div>
    </div>
</x-patient-layout>
