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
.btn{display:inline-block;background:#4A3050;color:#C9A8D4!important;text-decoration:none;font-size:14px;font-weight:600;border-radius:10px;padding:12px 24px;margin:8px 0 16px;}
.steps{margin:16px 0;padding:0;list-style:none;}
.steps li{font-size:13px;color:#5a4a62;padding:6px 0;padding-left:20px;position:relative;}
.steps li::before{content:"→";position:absolute;left:0;color:#C9A8D4;font-weight:bold;}
.ft{padding:16px 32px;border-top:1px solid #ebe1ef;font-size:11px;color:#a89ab5;}
</style>
</head>
<body>
<div class="wrap"><div class="card">
    <div class="hdr"><img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe &amp; Co"></div>
    <div class="body">
        <h1>We've received your consultation</h1>
        <p>Hi {{ $patient?->first_name }}, thank you for completing your consultation for <strong>{{ $consultation->product?->name }}</strong>. It has been received and is now in our clinical review queue.</p>

        <div class="detail-box">
            <p><strong>Consultation reference:</strong> #{{ $consultation->id }}</p>
            <p style="margin-top:6px"><strong>Product:</strong> {{ $consultation->product?->name }}</p>
            <p style="margin-top:6px"><strong>Submitted:</strong> {{ $consultation->created_at->format('d F Y, H:i') }}</p>
        </div>

        <p>What happens next:</p>
        <ul class="steps">
            <li>One of our registered prescribers will review your answers</li>
            <li>You'll receive an email once a decision has been made, usually within 24 hours</li>
            <li>If approved, your prescription will be prepared and dispatched</li>
        </ul>

        <a href="{{ config('app.url') }}/account/consultations" class="btn">View your consultation →</a>

        <p style="font-size:12px;color:#9b88a8">Questions? Contact us at <a href="mailto:pharmacy@prescribeandco.co.uk" style="color:#8e61a3">pharmacy@prescribeandco.co.uk</a></p>
    </div>
    <div class="ft">Prescribe &amp; Co · GPhC Registered · prescribeandco.co.uk</div>
</div></div>
</body>
</html>
