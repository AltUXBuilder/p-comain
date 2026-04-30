<x-layouts.app>
    <x-slot name="pageTitle">{{ $order->order_number }}</x-slot>

    <div class="space-y-5 animate-fade-in" x-data="{ showDispatch: false, showReturn: false, showFailed: false }">

        <a href="{{ route('orders.index') }}" class="text-sm font-medium text-lilac-600 hover:text-lilac-800">← Orders</a>

        {{-- Cold chain warning --}}
        @if($order->requires_cold_chain)
            <div class="flex items-center gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                <span class="text-xl">❄</span>
                <p class="text-sm font-semibold text-blue-800">Cold chain order — refrigerated packaging required for dispatch.</p>
            </div>
        @endif

        <div class="grid gap-5 lg:grid-cols-3">

            {{-- ── Main ──────────────────────────────────────────── --}}
            <div class="space-y-4 lg:col-span-2">

                {{-- Order header --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-sm font-bold text-plum-600">{{ $order->order_number }}</p>
                            @if($order->reshipOrder)
                                <p class="text-xs text-amber-600">Reship of original order</p>
                            @endif
                            <p class="mt-1 text-xs text-plum-400">Placed {{ $order->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-sm font-semibold {{ $order->statusColour() }}">
                            {{ $order->statusLabel() }}
                        </span>
                    </div>
                </div>

                {{-- Packing checklist --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-4 text-sm font-semibold text-plum-700">Packing Checklist</h3>
                    <div class="divide-y divide-plum-50">
                        @foreach($order->items as $item)
                            <div class="flex items-center gap-3 py-3">
                                <input type="checkbox" class="size-4 rounded border-plum-300 text-lilac-500">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-plum-800">{{ $item->product_name }}</p>
                                    <p class="text-xs text-plum-400">
                                        {{ $item->product_strength }} {{ $item->product_form }}
                                        · Qty: {{ $item->quantity }}
                                        @if($item->product?->cold_chain) · <span class="text-blue-500">❄ Cold chain</span> @endif
                                    </p>
                                </div>
                                <p class="text-sm font-medium text-plum-700">£{{ number_format($item->line_total, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 border-t border-plum-100 pt-3 text-right">
                        <p class="text-xs text-plum-400">Subtotal: £{{ number_format($order->subtotal, 2) }}</p>
                        @if($order->vat_amount > 0)
                            <p class="text-xs text-plum-400">VAT: £{{ number_format($order->vat_amount, 2) }}</p>
                        @endif
                        <p class="text-xs text-plum-400">Shipping: £{{ number_format($order->shipping_cost, 2) }}</p>
                        <p class="mt-1 text-base font-bold text-plum-800">Total: £{{ number_format($order->total, 2) }}</p>
                    </div>
                </div>

                {{-- Delivery address --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Delivery Address</h3>
                    <p class="text-sm font-medium text-plum-800">{{ $order->delivery_name }}</p>
                    <p class="text-sm text-plum-600">{{ $order->deliveryAddress() }}</p>
                </div>

                {{-- Tracking --}}
                @if($order->tracking_number)
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Tracking</h3>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-plum-400">Carrier</dt>
                            <dd class="font-medium text-plum-800">{{ $order->carrierLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-plum-400">Tracking number</dt>
                            <dd class="font-mono text-plum-700">{{ $order->tracking_number }}</dd>
                        </div>
                        @if($order->dispatched_at)
                        <div>
                            <dt class="text-xs text-plum-400">Dispatched</dt>
                            <dd class="text-plum-700">{{ $order->dispatched_at->format('d M Y, H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                    @if($link = $order->trackingLink())
                        <a href="{{ $link }}" target="_blank" class="mt-3 block text-xs font-medium text-lilac-600 hover:text-lilac-800">Track shipment →</a>
                    @endif
                </div>
                @endif

                {{-- Notes --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Notes</h3>
                    <form method="POST" action="{{ route('orders.notes', $order) }}" class="space-y-3">
                        @csrf @method('PATCH')
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-500">Fulfilment notes</label>
                            <textarea name="fulfilment_notes" rows="2"
                                class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                                >{{ $order->fulfilment_notes }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-plum-500">Packing notes</label>
                            <textarea name="packing_notes" rows="2"
                                class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"
                                >{{ $order->packing_notes }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">Save notes</button>
                        </div>
                    </form>
                </div>

            </div>

            {{-- ── Sidebar: patient, actions ─────────────────────── --}}
            <div class="space-y-4">

                {{-- Patient --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Patient</h3>
                    <p class="text-sm font-medium text-plum-800">{{ $order->patient?->full_name }}</p>
                    <p class="text-xs text-plum-400">{{ $order->patient?->email }}</p>
                    @if($order->patient)
                        <a href="{{ route('patients.show', $order->patient) }}" class="mt-2 block text-xs font-medium text-lilac-600 hover:text-lilac-800">View patient record →</a>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="rounded-2xl border border-plum-100 bg-white p-5 shadow-plum-sm space-y-2">
                    <h3 class="mb-3 text-sm font-semibold text-plum-700">Actions</h3>

                    @if($order->status === \App\Models\Order::STATUS_PROCESSING)
                        <button @click="showDispatch = true"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 hover:bg-plum-900">
                            Dispatch Order
                        </button>
                    @endif

                    @if($order->status === \App\Models\Order::STATUS_DISPATCHED)
                        <form method="POST" action="{{ route('orders.delivered', $order) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-700">
                                Mark Delivered
                            </button>
                        </form>
                        <button @click="showFailed = true"
                            class="flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                            Failed Delivery
                        </button>
                    @endif

                    @if(in_array($order->status, [\App\Models\Order::STATUS_DELIVERED, \App\Models\Order::STATUS_DISPATCHED, \App\Models\Order::STATUS_FAILED_DELIVERY]))
                        <button @click="showReturn = true"
                            class="flex w-full items-center justify-center rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">
                            Record Return
                        </button>
                    @endif

                    @if(in_array($order->status, [\App\Models\Order::STATUS_RETURNED, \App\Models\Order::STATUS_FAILED_DELIVERY]))
                        <form method="POST" action="{{ route('orders.reship', $order) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-600 hover:bg-plum-50">
                                Create Reship
                            </button>
                        </form>
                    @endif

                    @if($order->prescription)
                        <a href="{{ route('prescriptions.show', $order->prescription) }}" class="block text-center text-xs font-medium text-lilac-600 hover:text-lilac-800 pt-1">
                            View prescription →
                        </a>
                    @endif
                </div>

            </div>

        </div>

        {{-- Dispatch modal --}}
        <div x-show="showDispatch" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Dispatch Order</h3>
                <form method="POST" action="{{ route('orders.dispatch', $order) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Carrier</label>
                        <select name="carrier" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                            @foreach(\App\Models\Order::CARRIERS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Tracking number <span class="text-red-500">*</span></label>
                        <input type="text" name="tracking_number" required class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-plum-600">Tracking URL (optional)</label>
                        <input type="url" name="tracking_url" class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500" placeholder="https://…">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showDispatch = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-5 py-2 text-sm font-semibold text-lilac-200 hover:bg-plum-900">Confirm Dispatch</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Return modal --}}
        <div x-show="showReturn" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Record Return</h3>
                <form method="POST" action="{{ route('orders.returned', $order) }}" class="space-y-3">
                    @csrf
                    <textarea name="reason" rows="3" required placeholder="Return reason…"
                        class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showReturn = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-plum-800 px-4 py-2 text-sm font-medium text-lilac-200">Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Failed delivery modal --}}
        <div x-show="showFailed" class="fixed inset-0 z-50 flex items-center justify-center bg-plum-950/50 p-4" x-cloak>
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-plum-lg">
                <h3 class="mb-4 text-base font-semibold text-plum-800">Failed Delivery</h3>
                <form method="POST" action="{{ route('orders.failed-delivery', $order) }}" class="space-y-3">
                    @csrf
                    <textarea name="notes" rows="3" required placeholder="Failed delivery notes…"
                        class="block w-full rounded-xl border-plum-200 py-2 px-3 text-sm focus:border-lilac-500 focus:ring-lilac-500"></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showFailed = false" class="rounded-xl border border-plum-200 px-4 py-2 text-sm text-plum-500">Cancel</button>
                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white">Confirm</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
