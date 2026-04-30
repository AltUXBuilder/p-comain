<x-layouts.guest title="Set Your Password">

    <div class="animate-fade-in">

        <div class="mb-8">
            <div class="mb-4 flex size-12 items-center justify-center rounded-2xl bg-plum-800">
                <svg class="size-6 text-lilac-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>
                </svg>
            </div>
            <h1 class="font-display text-display-sm font-bold text-plum-800">Set your password</h1>
            <p class="mt-1 text-sm text-plum-400">Welcome, <strong class="text-plum-700">{{ $staff->first_name }}</strong>. Choose a strong password to get started.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('welcome.set-password', $token) }}" class="space-y-5">
            @csrf

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-plum-700">New password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="block w-full rounded-xl border-plum-200 bg-white px-4 py-2.5 text-sm text-plum-800 shadow-sm placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                    placeholder="Minimum 12 characters"
                >
                <p class="mt-1.5 text-xs text-plum-400">Must be at least 12 characters with uppercase, lowercase, number, and symbol.</p>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-plum-700">Confirm password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="block w-full rounded-xl border-plum-200 bg-white px-4 py-2.5 text-sm text-plum-800 shadow-sm placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                    placeholder="Repeat password"
                >
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 shadow-plum transition hover:bg-plum-900 focus:outline-none focus:ring-2 focus:ring-lilac-500 focus:ring-offset-2"
            >
                Set password &amp; continue
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </button>

        </form>

        <div class="mt-6 rounded-xl border border-plum-100 bg-plum-50/60 px-4 py-3">
            <p class="text-xs text-plum-500">After setting your password you will be asked to set up two-factor authentication (TOTP). You'll need Google Authenticator, Authy, or a compatible app on your phone.</p>
        </div>

    </div>

</x-layouts.guest>
