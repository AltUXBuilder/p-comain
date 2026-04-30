<x-layouts.guest title="Two-Factor Authentication">

    <div class="animate-fade-in">

        <div class="mb-8">
            <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-plum-800">
                <svg class="size-6 text-lilac-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3.75h3M6.75 21h10.5"/>
                </svg>
            </div>
            <h1 class="font-display text-display-sm font-bold text-plum-800">Two-factor verification</h1>
            <p class="mt-1 text-sm text-plum-400">
                Open your authenticator app and enter the 6-digit code.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div x-data="{ useRecovery: false }">

            {{-- TOTP form --}}
            <form
                x-show="!useRecovery"
                method="POST"
                action="{{ route('two-factor.login') }}"
                class="space-y-5"
            >
                @csrf
                <div>
                    <label for="code" class="mb-1.5 block text-sm font-medium text-plum-700">Authenticator code</label>
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
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 shadow-plum transition hover:bg-plum-900 focus:outline-none focus:ring-2 focus:ring-lilac-500 focus:ring-offset-2"
                >
                    Verify
                </button>
            </form>

            {{-- Recovery code form --}}
            <form
                x-show="useRecovery"
                method="POST"
                action="{{ route('two-factor.login') }}"
                class="space-y-5"
                x-cloak
            >
                @csrf
                <div>
                    <label for="recovery_code" class="mb-1.5 block text-sm font-medium text-plum-700">Recovery code</label>
                    <input
                        id="recovery_code"
                        type="text"
                        name="recovery_code"
                        autocomplete="one-time-code"
                        class="block w-full rounded-xl border-plum-200 bg-white px-4 py-2.5 text-sm font-mono text-plum-800 shadow-sm placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                        placeholder="xxxx-xxxx"
                    >
                </div>
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 shadow-plum transition hover:bg-plum-900"
                >
                    Use recovery code
                </button>
            </form>

            {{-- Toggle --}}
            <button
                @click="useRecovery = !useRecovery"
                class="mt-4 w-full text-center text-sm text-lilac-600 hover:text-lilac-800"
                type="button"
            >
                <span x-text="useRecovery ? 'Use authenticator app instead' : 'Use a recovery code instead'"></span>
            </button>

        </div>

    </div>

</x-layouts.guest>
