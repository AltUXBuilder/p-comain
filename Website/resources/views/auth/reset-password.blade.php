<x-auth-layout title="Set new password">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}"
                   autocomplete="email" required
                   class="input @error('email') input-error @enderror">
            @error('email')<p class="error-message">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="label">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   class="input @error('password') input-error @enderror">
            @error('password')<p class="error-message">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   autocomplete="new-password" required class="input">
        </div>
        <button type="submit" class="btn-primary w-full">Reset password</button>
    </form>
</x-auth-layout>
