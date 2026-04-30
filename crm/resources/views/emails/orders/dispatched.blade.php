<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your order has been dispatched</title>
<style>
    body { margin: 0; padding: 0; background: #f5f0f7; font-family: 'DM Sans', Arial, sans-serif; }
    .wrapper { max-width: 580px; margin: 0 auto; padding: 32px 16px; }
    .card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(74,48,80,0.12); }
    .header { background: linear-gradient(135deg, #4A3050 0%, #5B3E6A 100%); padding: 30px 36px; }
    .header img { height: 32px; width: auto; }
    .body { padding: 32px 36px; }
    h1 { margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #4A3050; }
    p { margin: 0 0 14px; font-size: 15px; color: #5a4a62; line-height: 1.6; }
    .track-btn { display: inline-block; background: #4A3050; color: #C9A8D4 !important; text-decoration: none; font-size: 15px; font-weight: 600; border-radius: 12px; padding: 14px 28px; margin: 8px 0 20px; }
    .detail-box { background: #f5f0f7; border-radius: 12px; padding: 16px 20px; margin: 16px 0; }
    .detail-box p { margin: 0; font-size: 13px; color: #7a5f87; }
    .detail-box strong { color: #4A3050; }
    .footer { padding: 20px 36px; border-top: 1px solid #ebe1ef; }
    .footer p { margin: 0; font-size: 12px; color: #a89ab5; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe & Co">
        </div>
        <div class="body">
            <h1>Your order is on its way</h1>
            <p>Hi {{ $patient?->first_name }}, your order from Prescribe &amp; Co has been dispatched and is on its way to you.</p>

            <div class="detail-box">
                <p><strong>Order number:</strong> {{ $order->order_number }}</p>
                <p style="margin-top:6px"><strong>Carrier:</strong> {{ $carrierName }}</p>
                @if($order->tracking_number)
                    <p style="margin-top:6px"><strong>Tracking number:</strong> {{ $order->tracking_number }}</p>
                @endif
                <p style="margin-top:6px"><strong>Dispatched:</strong> {{ $order->dispatched_at?->format('d F Y') }}</p>
            </div>

            @if($trackingUrl)
                <a href="{{ $trackingUrl }}" class="track-btn">Track Your Delivery →</a>
            @endif

            <p style="font-size:13px;color:#9b88a8">If you have any questions about your order, please reply to this email or contact us at <a href="mailto:pharmacy@prescribeandco.co.uk" style="color:#8e61a3">pharmacy@prescribeandco.co.uk</a>.</p>
        </div>
        <div class="footer">
            <p>Prescribe &amp; Co · GPhC Registered · prescribeandco.co.uk<br>
            <a href="{{ config('app.url') }}/privacy" style="color:#8e61a3">Privacy Policy</a></p>
        </div>
    </div>
</div>
</body>
</html>
