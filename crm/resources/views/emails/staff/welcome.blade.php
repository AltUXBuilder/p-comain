<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Prescribe &amp; Co</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f5f0f7; font-family: 'DM Sans', Arial, sans-serif; }
        .wrapper { max-width: 580px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(74,48,80,0.12); }
        .header { background: linear-gradient(135deg, #4A3050 0%, #5B3E6A 100%); padding: 36px 40px; }
        .header img { height: 36px; width: auto; }
        .body { padding: 36px 40px; }
        h1 { margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #4A3050; }
        p { margin: 0 0 16px; font-size: 15px; color: #5a4a62; line-height: 1.6; }
        .btn { display: inline-block; background: #4A3050; color: #C9A8D4 !important; text-decoration: none; font-size: 15px; font-weight: 600; border-radius: 12px; padding: 14px 28px; margin: 8px 0 24px; }
        .meta { background: #f5f0f7; border-radius: 12px; padding: 16px 20px; margin: 20px 0; }
        .meta p { margin: 0; font-size: 13px; color: #7a5f87; }
        .meta strong { color: #4A3050; }
        .footer { padding: 24px 40px; border-top: 1px solid #ebe1ef; }
        .footer p { margin: 0; font-size: 12px; color: #a89ab5; }
        .footer a { color: #8e61a3; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <div class="header">
            <img src="{{ config('app.url') }}/images/brand/logo-dark.png" alt="Prescribe &amp; Co">
        </div>

        <div class="body">
            <h1>Welcome, {{ $staff->first_name }}</h1>
            <p>
                You've been added to the Prescribe &amp; Co internal CRM as
                <strong>{{ $roleLabel }}</strong>.
            </p>
            <p>
                Click the button below to set your password and activate your account.
                You'll also be guided through setting up two-factor authentication — this is required for all staff.
            </p>

            <a href="{{ $welcomeUrl }}" class="btn">
                Activate My Account →
            </a>

            <div class="meta">
                <p><strong>Your email:</strong> {{ $staff->email }}</p>
                <p style="margin-top:6px"><strong>Role:</strong> {{ $roleLabel }}</p>
                @if($staff->gphc_number)
                    <p style="margin-top:6px"><strong>GPhC number on record:</strong> {{ $staff->gphc_number }}</p>
                @endif
                <p style="margin-top:6px"><strong>Link expires:</strong> {{ $expiresIn }} from receipt</p>
            </div>

            <p style="font-size:13px;color:#9b88a8">
                If you did not expect this email, please contact
                <a href="mailto:admin@prescribeandco.co.uk" style="color:#8e61a3">admin@prescribeandco.co.uk</a> immediately.
                Do not click the link.
            </p>
        </div>

        <div class="footer">
            <p>
                This is an automated message from Prescribe &amp; Co's internal systems.<br>
                Prescribe &amp; Co · CRM · crm.prescribeandco.co.uk<br>
                <a href="https://prescribeandco.co.uk/privacy">Privacy Policy</a>
            </p>
        </div>

    </div>
</div>
</body>
</html>
