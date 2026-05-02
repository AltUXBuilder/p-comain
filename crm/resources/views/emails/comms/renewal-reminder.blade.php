<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><style>body{margin:0;padding:0;background:#f5f0f7;font-family:Arial,sans-serif;}.wrap{max-width:560px;margin:0 auto;padding:28px 16px;}.card{background:#fff;border-radius:16px;overflow:hidden;}.hdr{background:linear-gradient(135deg,#4A3050,#5B3E6A);padding:28px 32px;}.hdr img{height:28px;}.body{padding:28px 32px;}h1{margin:0 0 8px;font-size:20px;font-weight:700;color:#4A3050;}p{margin:0 0 12px;font-size:14px;color:#5a4a62;line-height:1.6;}.btn{display:inline-block;background:#4A3050;color:#C9A8D4!important;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;padding:12px 24px;margin:8px 0 16px;}.ft{padding:16px 32px;border-top:1px solid #ebe1ef;font-size:11px;color:#a89ab5;}</style></head><body>
<div class="wrap"><div class="card">
<div class="hdr"><img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe &amp; Co"></div>
<div class="body">
<h1>Your subscription renews in 7 days</h1>
<p>Hi {{ $patient?->first_name }}, this is a friendly reminder that your Prescribe &amp; Co subscription renews on <strong>{{ $renewalDate->format('d F Y') }}</strong>.</p>
<p>No action is needed — your next prescription will be prepared automatically. If you'd like to make any changes to your subscription, you can do so in your account.</p>
<a href="{{ config('app.url') }}/account/subscription" class="btn">Manage subscription →</a>
</div>
<div class="ft">Prescribe &amp; Co · GPhC Registered · prescribeandco.co.uk</div>
</div></div></body></html>
