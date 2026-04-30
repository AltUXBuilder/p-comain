<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied — Prescribe &amp; Co CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;background:#4A3050;font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{background:#fff;border-radius:20px;padding:48px 40px;max-width:440px;width:100%;text-align:center;box-shadow:0 8px 40px rgba(74,48,80,0.35)}
        .icon{width:64px;height:64px;border-radius:16px;background:#f5f0f7;display:flex;align-items:center;justify-content:center;margin:0 auto 24px}
        h1{font-size:22px;font-weight:700;color:#4A3050;margin-bottom:10px}
        p{font-size:14px;color:#7a5f87;line-height:1.6;margin-bottom:8px}
        .ip{display:inline-block;background:#f5f0f7;border-radius:8px;padding:6px 14px;font-family:monospace;font-size:13px;color:#4A3050;margin:16px 0}
        .note{font-size:12px;color:#a89ab5;margin-top:20px;padding-top:20px;border-top:1px solid #ebe1ef}
    </style>
</head>
<body>
<div class="card">
    <div class="icon">
        <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#4A3050" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
        </svg>
    </div>
    <h1>Access Restricted</h1>
    <p>This system is restricted to authorised IP addresses only.</p>
    <p>Your current IP address is not on the whitelist:</p>
    <div class="ip">{{ $ip }}</div>
    <p>If you believe this is an error, please contact your system administrator.</p>
    <div class="note">Prescribe &amp; Co · Internal CRM · All access attempts are logged and monitored.</div>
</div>
</body>
</html>
