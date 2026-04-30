<x-auth-layout title="Welcome back" description="Sign in to your Prescribe & Co account.">

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
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

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="label mb-0">Password</label>
                <a href="{{ route('password.request') }}"
                   class="text-xs text-plum-500 hover:text-plum-800 transition-colors">
                    Forgot password?
                </a>
            </div>
            <input id="password" name="password" type="password"
                   autocomplete="current-password" required
                   class="input @error('password') input-error @enderror">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="remember"
                   class="rounded border-plum-300 text-plum-800 focus:ring-plum-800">
            <span class="text-sm text-plum-600">Remember me on this device</span>
        </label>

        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <p class="mt-5 text-center text-sm text-plum-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-plum-800 font-medium hover:underline underline-offset-2">Create one</a>
    </p>

</x-auth-layout>
