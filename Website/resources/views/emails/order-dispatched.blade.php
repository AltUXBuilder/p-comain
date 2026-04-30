<x-emails.layout :recipient-email="$user->email">

    <h1>Your order is on its way</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>Your order <strong>{{ $order->order_number }}</strong> has been dispensed and dispatched. It's on its way to you now.</p>

    @if ($order->tracking_number)
    <div class="highlight-box">
        <p style="margin:0;"><strong>Carrier:</strong> {{ ucfirst(str_replace('_', ' ', $order->carrier ?? '')) }}</p>
        <p style="margin:4px 0 0;"><strong>Tracking number:</strong> {{ $order->tracking_number }}</p>
    </div>

    @if ($order->tracking_url)
    <a href="{{ $order->tracking_url }}" class="btn">Track my order</a>
    @endif
    @endif

    <p>Delivery address:</p>
    <p>{{ $order->delivery_name }}<br>
    {{ $order->delivery_address_line_1 }}<br>
    @if($order->delivery_address_line_2){{ $order->delivery_address_line_2 }}<br>@endif
    {{ $order->delivery_city }}, {{ $order->delivery_postcode }}</p>

    @if ($order->requires_cold_chain)
    <div class="highlight-box">
        <p style="margin:0;"><strong>⚠ Cold chain product:</strong> Please refrigerate at 2–8°C immediately upon receipt. Do not freeze.</p>
    </div>
    @endif

    <hr class="divider">
    <p class="muted">Questions about your delivery? <a href="{{ route('patient.messages.index') }}" style="color:#9a6daa;">Message our team</a>.</p>

</x-emails.layout>
