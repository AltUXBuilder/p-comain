<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'Prescribe & Co' }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#f5f0f7; font-family:'DM Sans',Helvetica,Arial,sans-serif; color:#4A3050; }
  .wrapper { max-width:600px; margin:32px auto; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(74,48,80,0.10); }
  .header { background:#4A3050; padding:32px; text-align:center; }
  .header img { height:40px; width:auto; }
  .body { padding:40px 40px 32px; }
  .footer { background:#f5f0f7; padding:24px 40px; text-align:center; }
  h1 { font-family:Georgia,'Cormorant Garamond',serif; font-size:26px; font-weight:700; color:#4A3050; margin-bottom:12px; line-height:1.2; }
  p { font-size:15px; line-height:1.6; color:#4A3050; margin-bottom:16px; }
  .btn { display:inline-block; background:#4A3050; color:#C9A8D4 !important; text-decoration:none; padding:14px 28px; border-radius:12px; font-weight:600; font-size:15px; margin:8px 0; }
  .btn:hover { background:#3a2540; }
  .divider { border:none; border-top:1px solid #ebe1ef; margin:24px 0; }
  .muted { font-size:13px; color:#9a6daa; }
  .badge { display:inline-block; background:#f4eef9; border:1px solid #dfc3eb; color:#4A3050; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; }
  .highlight-box { background:#f5f0f7; border-left:3px solid #C9A8D4; border-radius:0 8px 8px 0; padding:16px 20px; margin:16px 0; }
  .otp-code { font-family:monospace; font-size:36px; font-weight:700; color:#4A3050; letter-spacing:0.3em; text-align:center; background:#f5f0f7; border-radius:12px; padding:20px; display:block; margin:16px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <img src="{{ config('pharmacy.website', 'https://prescribeandco.co.uk') }}/images/brand/logo-dark.png"
         alt="Prescribe &amp; Co">
  </div>
  <div class="body">
    {{ $slot }}
  </div>
  <div class="footer">
    <p class="muted">
      Prescribe &amp; Co · GPhC Registered Pharmacy<br>
      <a href="{{ config('pharmacy.website') }}" style="color:#9a6daa;">prescribeandco.co.uk</a>
    </p>
    <p class="muted" style="margin-top:8px; font-size:12px;">
      You're receiving this because you have an account with Prescribe &amp; Co.
      This email was sent to {{ $recipientEmail ?? '' }}.
    </p>
  </div>
</div>
</body>
</html>
