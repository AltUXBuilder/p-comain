<x-app-layout>
    <x-slot name="title">Order confirmed</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-16 text-center">

        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6 animate-fade-in">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="font-display text-display-md font-bold text-plum-800 mb-3 animate-slide-up">
            Order confirmed
        </h1>
        <p class="text-plum-500 text-lg mb-2">Thank you, {{ auth()->user()->first_name }}.</p>

        @if ($order)
            <p class="text-plum-500 mb-2">
                Your order <strong>{{ $order->order_number }}</strong> has been placed successfully.
            </p>
            <p class="text-plum-500 mb-10">
                We've sent a confirmation to <strong>{{ auth()->user()->email }}</strong>.
                Our dispensing team will prepare your order and you'll receive a dispatch notification once it's on its way.
            </p>

            {{-- Order summary --}}
            <div class="card p-6 text-left mb-8">
                <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Order summary</h2>
                @foreach ($order->items as $item)
                    <div class="flex justify-between items-center py-2 border-b border-plum-50 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-plum-800">{{ $item->product_name }}</p>
                            @if ($item->product_strength)
                                <p class="text-xs text-plum-400">{{ $item->product_strength }}</p>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-plum-800">£{{ number_format($item->line_total, 2) }}</p>
                    </div>
                @endforeach
                <div class="flex justify-between font-bold text-plum-800 pt-3 mt-1">
                    <span>Total paid</span>
                    <span>£{{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            @if ($order->prescription?->product->requires_cold_chain)
                <div class="alert-warning mb-8 text-left">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <div>
                        <p class="font-medium text-sm">Cold chain product</p>
                        <p class="text-xs mt-0.5">Please refrigerate at 2–8°C immediately upon receipt. Do not freeze.</p>
                    </div>
                </div>
            @endif
        @else
            <p class="text-plum-500 mb-10">Your order has been placed. You'll receive a confirmation email shortly.</p>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('patient.orders.index') }}"   class="btn-primary">View my orders</a>
            <a href="{{ route('patient.dashboard') }}"      class="btn-secondary">Go to dashboard</a>
        </div>

    </div>
</x-app-layout>
