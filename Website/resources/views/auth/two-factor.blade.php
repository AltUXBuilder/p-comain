@php
$purposeLabels = [
    'new_device_login'      => 'new device login',
    'access_prescriptions'  => 'accessing your prescriptions',
    'change_payment'        => 'updating your payment details',
    'change_address'        => 'updating your delivery address',
];
$label = $purposeLabels[$purpose ?? 'new_device_login'] ?? 'this action';

$isSensitive = $sensitive ?? false;
$postRoute   = $isSensitive ? route('two-factor.sensitive.verify') : route('two-factor.verify');
@endphp

<x-auth-layout title="Verification required">

    {{-- Icon --}}
    <div class="flex justify-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-lilac-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-plum-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    <p class="text-sm text-plum-500 text-center mb-6">
        To continue with {{ $label }}, please enter the 6-digit code we've sent to your email address.
        The code expires in 10 minutes.
    </p>

    <form method="POST" action="{{ $postRoute }}" class="space-y-5">
        @csrf

        @if ($isSensitive)
            <input type="hidden" name="purpose"  value="{{ $purpose }}">
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif

        <div>
            <label for="code" class="label text-center block">Verification code</label>
            <input id="code" name="code" type="text"
                   inputmode="numeric" pattern="[0-9]{6}"
                   maxlength="6" required autofocus
                   placeholder="000000"
                   class="input text-center text-2xl font-mono tracking-[0.4em] @error('code') input-error @enderror">
            @error('code')
                <p class="error-message text-center">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full">Verify &amp; continue</button>
    </form>

    {{-- Resend --}}
    <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-4">
        @csrf
        <p class="text-center text-sm text-plum-400">
            Didn't receive the code?
            <button type="submit" class="text-plum-800 font-medium hover:underline underline-offset-2">
                Resend code
            </button>
        </p>
    </form>

</x-auth-layout>
