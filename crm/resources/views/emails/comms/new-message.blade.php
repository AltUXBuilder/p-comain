<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><style>body{margin:0;padding:0;background:#f5f0f7;font-family:Arial,sans-serif;}.wrap{max-width:560px;margin:0 auto;padding:28px 16px;}.card{background:#fff;border-radius:16px;overflow:hidden;}.hdr{background:linear-gradient(135deg,#4A3050,#5B3E6A);padding:28px 32px;}.hdr img{height:28px;}.body{padding:28px 32px;}h1{margin:0 0 8px;font-size:20px;font-weight:700;color:#4A3050;}p{margin:0 0 12px;font-size:14px;color:#5a4a62;line-height:1.6;}.msg-box{background:#f5f0f7;border-radius:10px;padding:14px 16px;margin:12px 0;font-size:14px;color:#4A3050;line-height:1.6;}.btn{display:inline-block;background:#4A3050;color:#C9A8D4!important;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;padding:12px 24px;margin:8px 0 16px;}.ft{padding:16px 32px;border-top:1px solid #ebe1ef;font-size:11px;color:#a89ab5;}</style></head><body>
<div class="wrap"><div class="card">
<div class="hdr"><img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe &amp; Co"></div>
<div class="body">
<h1>You have a new message</h1>
<p>Hi {{ $patient?->first_name }}, you have a new message from the Prescribe &amp; Co team:</p>
<div class="msg-box">{{ $message->body }}</div>
<a href="{{ config('app.url') }}/account/messages" class="btn">View &amp; Reply →</a>
<p style="font-size:12px;color:#9b88a8">Please do not reply directly to this email — use your account to send a secure response.</p>
</div>
<div class="ft">Prescribe &amp; Co · GPhC Registered · prescribeandco.co.uk</div>
</div></div></body></html>
