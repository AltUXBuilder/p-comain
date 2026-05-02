<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
body{margin:0;padding:0;background:#f5f0f7;font-family:Arial,sans-serif;}
.wrap{max-width:560px;margin:0 auto;padding:28px 16px;}
.card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(74,48,80,.12);}
.hdr{background:linear-gradient(135deg,#4A3050,#5B3E6A);padding:28px 32px;}
.hdr img{height:28px;}
.body{padding:28px 32px;}
h1{margin:0 0 8px;font-size:20px;font-weight:700;color:#4A3050;}
p{margin:0 0 12px;font-size:14px;color:#5a4a62;line-height:1.6;}
.detail-box{background:#f5f0f7;border-radius:10px;padding:14px 16px;margin:12px 0;}
.detail-box p{margin:0;font-size:13px;color:#7a5f87;}
.detail-box strong{color:#4A3050;}
.badge{display:inline-block;background:#d1fae5;color:#065f46;font-size:12px;font-weight:700;border-radius:8px;padding:4px 10px;margin-bottom:14px;}
.btn{display:inline-block;background:#4A3050;color:#C9A8D4!important;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;padding:12px 24px;margin:8px 0 16px;}
.ft{padding:16px 32px;border-top:1px solid #ebe1ef;font-size:11px;color:#a89ab5;}
</style>
</head>
<body>
<div class="wrap"><div class="card">
    <div class="hdr"><img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe &amp; Co"></div>
    <div class="body">
        <div class="badge">✓ Order Dispensed</div>
        <h1>Your order has been dispensed</h1>
        <p>Hi {{ $patient?->first_name }}, great news — your order has been dispensed by our pharmacy team and is ready for dispatch.</p>

        <div class="detail-box">
            <p><strong>Order number:</strong> {{ $order->order_number }}</p>
            @if($order->items->isNotEmpty())
                <p style="margin-top:6px"><strong>Items:</strong>
                    {{ $order->items->map(fn($i) => $i->product_name)->implode(', ') }}
                </p>
            @endif
            <p style="margin-top:6px"><strong>Dispensed:</strong> {{ now()->format('d F Y') }}</p>
        </div>

        <p>Your order will now be packaged and collected by our courier. You'll receive a further email with tracking details as soon as it has been dispatched.</p>

        @if($order->requires_cold_chain)
            <p style="background:#dbeafe;border-radius:8px;padding:10px 14px;font-size:13px;color:#1d4ed8;">
                ❄ <strong>Cold chain product:</strong> Your medication requires refrigeration. Please store it in your fridge (2–8°C) immediately upon receipt.
            </p>
        @endif

        <a href="{{ config('app.url') }}/account/orders" class="btn">View your order →</a>

        <p style="font-size:12px;color:#9b88a8">Questions? Contact us at <a href="mailto:pharmacy@prescribeandco.co.uk" style="color:#8e61a3">pharmacy@prescribeandco.co.uk</a></p>
    </div>
    <div class="ft">Prescribe &amp; Co · GPhC Registered · prescribeandco.co.uk</div>
</div></div>
</body>
</html>
