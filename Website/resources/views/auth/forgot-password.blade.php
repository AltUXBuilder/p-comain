<x-auth-layout title="Reset your password" description="Enter your email and we'll send you a reset link.">

    @if (session('status'))
        <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   autocomplete="email" required autofocus
                   class="input @error('email') input-error @enderror">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn-primary w-full">Send reset link</button>
    </form>

    <p class="mt-5 text-center text-sm text-plum-500">
        <a href="{{ route('login') }}" class="text-plum-800 font-medium hover:underline underline-offset-2">← Back to sign in</a>
    </p>

</x-auth-layout>
