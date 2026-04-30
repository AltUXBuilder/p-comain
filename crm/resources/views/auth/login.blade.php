<x-layouts.guest title="Sign In">

    <div class="animate-fade-in">

        <div class="mb-8">
            <h1 class="font-display text-display-sm font-bold text-plum-800">Welcome back</h1>
            <p class="mt-1 text-sm text-plum-400">Sign in to the P&amp;Co CRM</p>
        </div>

        {{-- Session / validation errors --}}
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('status'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-plum-700">Email address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    autofocus
                    class="block w-full rounded-xl border-plum-200 bg-white px-4 py-2.5 text-sm text-plum-800 shadow-sm placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                    placeholder="you@prescribeandco.co.uk"
                >
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-plum-700">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="block w-full rounded-xl border-plum-200 bg-white px-4 py-2.5 text-sm text-plum-800 shadow-sm placeholder:text-plum-300 focus:border-lilac-500 focus:ring-lilac-500"
                    placeholder="••••••••••••"
                >
            </div>

            {{-- Remember me --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-plum-600">
                    <input
                        type="checkbox"
                        name="remember"
                        class="size-4 rounded border-plum-300 text-lilac-500 focus:ring-lilac-500"
                    >
                    Remember this device
                </label>
            </div>

            {{-- Submit --}}
            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-plum-800 px-4 py-2.5 text-sm font-semibold text-lilac-200 shadow-plum transition hover:bg-plum-900 focus:outline-none focus:ring-2 focus:ring-lilac-500 focus:ring-offset-2"
            >
                Sign in
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </button>

        </form>

        {{-- Security note --}}
        <p class="mt-6 text-center text-xs text-plum-400">
            You will be prompted for your authenticator app code after signing in.
        </p>

        <div class="mt-6 flex items-center gap-2 rounded-xl border border-plum-100 bg-plum-50/60 px-4 py-3">
            <svg class="size-4 shrink-0 text-plum-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <p class="text-xs text-plum-500">
                Access is restricted to authorised IP addresses only. All login attempts are audited.
            </p>
        </div>

    </div>

</x-layouts.guest>
