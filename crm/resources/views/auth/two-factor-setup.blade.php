<x-layouts.guest title="Set Up Two-Factor Authentication">

    <div class="animate-fade-in" x-data="twoFactorSetup()" x-init="init()">

        <div class="mb-6">
            <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-plum-800">
                <svg class="size-6 text-lilac-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <h1 class="font-display text-display-sm font-bold text-plum-800">Secure your account</h1>
            <p class="mt-1 text-sm text-plum-400">
                Two-factor authentication is required for all CRM staff. Scan the QR code with Google Authenticator, Authy, or a compatible app.
            </p>
        </div>

        {{-- Step 1: Enable 2FA (get QR code) --}}
        <div x-show="step === 'enable'" class="space-y-5">
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 shadow-plum transition hover:bg-plum-900"
                >
                    Generate QR Code
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Step 2: Show QR code and confirm --}}
        @if(session('status') === 'two-factor-authentication-enabled' || auth('staff')->user()->two_factor_secret)
        <div class="space-y-6">

            {{-- QR Code --}}
            <div class="flex flex-col items-center gap-4 rounded-2xl border border-plum-100 bg-white p-6">
                <p class="text-center text-xs font-medium text-plum-500 uppercase tracking-wide">Step 1 — Scan this QR code</p>
                <div class="rounded-xl bg-white p-2 shadow-plum-sm">
                    {!! auth('staff')->user()->twoFactorQrCodeSvg() !!}
                </div>

                {{-- Manual entry fallback --}}
                <details class="w-full">
                    <summary class="cursor-pointer text-center text-xs text-lilac-600 hover:text-lilac-800">
                        Can't scan? Enter code manually
                    </summary>
                    <div class="mt-3 rounded-xl bg-plum-50 px-4 py-3 text-center">
                        <p class="mb-1 text-xs text-plum-500">Account name: <strong class="text-plum-700">P&amp;Co CRM · {{ auth('staff')->user()->email }}</strong></p>
                        <p class="break-all font-mono text-sm font-semibold text-plum-800">{{ decrypt(auth('staff')->user()->two_factor_secret) }}</p>
                    </div>
                </details>
            </div>

            {{-- Confirm code --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="mb-1.5 block text-sm font-medium text-plum-700">
                        Step 2 — Enter the 6-digit code from your app
                    </label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        autofocus
                        autocomplete="one-time-code"
                        class="block w-full rounded-xl border-plum-200 bg-white px-4 py-2.5 text-center text-2xl font-semibold tracking-[0.5em] text-plum-800 shadow-sm placeholder:text-plum-300 placeholder:tracking-normal focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="000000"
                    >
                </div>
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 shadow-plum transition hover:bg-plum-900"
                >
                    Confirm &amp; Activate Account
                </button>
            </form>

        </div>
        @endif

        <p class="mt-6 text-center text-xs text-plum-400">
            After confirming, you'll receive recovery codes. Keep them somewhere safe.
        </p>

    </div>

</x-layouts.guest>

@push('scripts')
<script>
function twoFactorSetup() {
    return {
        step: 'enable',
        init() {
            @if(auth('staff')->user()->two_factor_secret)
                this.step = 'confirm';
            @endif
        }
    }
}
</script>
@endpush
