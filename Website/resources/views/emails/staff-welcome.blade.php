<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome to Prescribe & Co CRM</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#f5f0f7; font-family:Helvetica,Arial,sans-serif; color:#4A3050; }
  .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(74,48,80,.1); }
  .header { background:#4A3050; padding:32px; text-align:center; }
  .body { padding:40px; }
  .footer { background:#f5f0f7; padding:20px 40px; text-align:center; font-size:12px; color:#9a6daa; }
  h1 { font-family:Georgia,serif; font-size:26px; color:#4A3050; margin-bottom:12px; }
  p { font-size:15px; line-height:1.6; color:#4A3050; margin-bottom:16px; }
  .btn { display:inline-block; background:#4A3050; color:#C9A8D4 !important; text-decoration:none; padding:14px 28px; border-radius:12px; font-weight:600; font-size:15px; margin:8px 0; }
  .highlight-box { background:#f5f0f7; border-left:3px solid #C9A8D4; border-radius:0 8px 8px 0; padding:16px 20px; margin:16px 0; }
  .divider { border:none; border-top:1px solid #ebe1ef; margin:24px 0; }
  .muted { font-size:13px; color:#9a6daa; }
  .warning { background:#fff8f0; border-left:3px solid #f59e0b; border-radius:0 8px 8px 0; padding:14px 20px; margin:16px 0; font-size:14px; color:#92400e; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <p style="color:#C9A8D4; font-family:Georgia,serif; font-size:28px; font-weight:700; margin:0;">P&amp;Co.</p>
    <p style="color:#C9A8D4; opacity:0.7; font-size:12px; margin-top:4px;">Prescribe &amp; Co — Staff Portal</p>
  </div>
  <div class="body">
    <h1>Welcome to Prescribe &amp; Co</h1>
    <p>Hi {{ $staff->first_name }},</p>
    <p>A staff account has been created for you on the Prescribe &amp; Co CRM. To activate your account, please click the link below to set your password and complete two-factor authentication setup.</p>

    <div class="highlight-box">
      <p style="margin:0;"><strong>Name:</strong> {{ $staff->full_name }}</p>
      <p style="margin:4px 0 0;"><strong>Role:</strong> {{ $staff->role_display }}</p>
      <p style="margin:4px 0 0;"><strong>Email:</strong> {{ $staff->email }}</p>
    </div>

    <a href="{{ $welcomeUrl }}" class="btn">Set up my account</a>

    <div class="warning">
      <strong>⚠ This link expires in 48 hours.</strong> If it has expired, please contact your Super Admin to resend it.
    </div>

    @if ($staff->requiresGphcNumber())
    <p>As a <strong>{{ $staff->role_display }}</strong>, you will be required to confirm your GPhC registration number during setup. Please have it ready.</p>
    @endif

    <hr class="divider">
    <p class="muted">
      This email was sent by the Prescribe &amp; Co system. If you were not expecting this, please contact
      <a href="mailto:{{ config('pharmacy.email') }}" style="color:#9a6daa;">{{ config('pharmacy.email') }}</a> immediately.
      Do not click the link above if you don't recognise this.
    </p>
  </div>
  <div class="footer">Prescribe &amp; Co · GPhC Registered Pharmacy · prescribeandco.co.uk</div>
</div>
</body>
</html>
