<x-auth-layout title="Verify your email">

    <div class="flex justify-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-lilac-100 flex items-center justify-center">
            <svg class="w-7 h-7 text-plum-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/>
            </svg>
        </div>
    </div>

    <p class="text-sm text-plum-500 text-center mb-6">
        Thanks for registering. Before you can submit your consultation, please verify your email address
        by clicking the link we sent you.
    </p>

    @if (session('status') === 'verification-link-sent')
        <x-ui.alert type="success" class="mb-4">
            A new verification link has been sent to your email address.
        </x-ui.alert>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary w-full">
                Resend verification email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost w-full text-sm text-plum-500">
                Sign out
            </button>
        </form>
    </div>

</x-auth-layout>
