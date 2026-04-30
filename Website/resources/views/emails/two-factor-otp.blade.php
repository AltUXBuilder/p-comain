<x-emails.layout :recipient-email="$user->email">

    <h1>Your verification code</h1>
    <p>Hi {{ $user->first_name }},</p>
    <p>Here is your one-time verification code for Prescribe &amp; Co. It expires in <strong>10 minutes</strong>.</p>

    <span class="otp-code">{{ $otp }}</span>

    <p class="muted">If you didn't request this code, please ignore this email and contact us immediately at <a href="mailto:{{ config('pharmacy.email') }}" style="color:#9a6daa;">{{ config('pharmacy.email') }}</a>.</p>

    <hr class="divider">
    <p class="muted" style="font-size:13px;">For security, never share this code with anyone — Prescribe &amp; Co will never ask for your code by phone or email.</p>

</x-emails.layout>
