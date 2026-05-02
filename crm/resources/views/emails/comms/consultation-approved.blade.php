{{-- resources/views/emails/comms/consultation-approved.blade.php --}}
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><style>
body{margin:0;padding:0;background:#f5f0f7;font-family:Arial,sans-serif;}
.wrap{max-width:560px;margin:0 auto;padding:28px 16px;}
.card{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(74,48,80,.12);}
.hdr{background:linear-gradient(135deg,#4A3050,#5B3E6A);padding:28px 32px;}
.hdr img{height:28px;}
.body{padding:28px 32px;}
h1{margin:0 0 8px;font-size:20px;font-weight:700;color:#4A3050;}
p{margin:0 0 12px;font-size:14px;color:#5a4a62;line-height:1.6;}
.btn{display:inline-block;background:#4A3050;color:#C9A8D4!important;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;padding:12px 24px;margin:8px 0 16px;}
.ft{padding:16px 32px;border-top:1px solid #ebe1ef;font-size:11px;color:#a89ab5;}
</style></head><body>
<div class="wrap"><div class="card">
<div class="hdr"><img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe & Co"></div>
<div class="body">
<h1>Your consultation has been approved</h1>
<p>Hi {{ $patient?->first_name }}, great news — your consultation for <strong>{{ $consultation->product?->name }}</strong> has been reviewed and approved by one of our prescribers.</p>
<p>We're now preparing your prescription and will notify you once it's ready for dispatch.</p>
<a href="{{ config('app.url') }}/account/orders" class="btn">View your account →</a>
<p style="font-size:12px;color:#9b88a8">Questions? Email us at <a href="mailto:pharmacy@prescribeandco.co.uk" style="color:#8e61a3">pharmacy@prescribeandco.co.uk</a></p>
</div>
<div class="ft">Prescribe &amp; Co · GPhC Registered · prescribeandco.co.uk</div>
</div></div></body></html>
