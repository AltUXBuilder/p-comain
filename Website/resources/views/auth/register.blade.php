<x-auth-layout title="Create your account" description="Join Prescribe & Co for discreet, clinically reviewed treatments.">

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Name row --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="first_name" class="label">First name</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                       autocomplete="given-name" required
                       class="input @error('first_name') input-error @enderror">
                @error('first_name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="last_name" class="label">Last name</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                       autocomplete="family-name" required
                       class="input @error('last_name') input-error @enderror">
                @error('last_name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   autocomplete="email" required
                   class="input @error('email') input-error @enderror">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- DOB --}}
        <div>
            <label for="date_of_birth" class="label">Date of birth</label>
            <input id="date_of_birth" name="date_of_birth" type="date"
                   value="{{ old('date_of_birth') }}"
                   max="{{ now()->subYears(18)->format('Y-m-d') }}"
                   required
                   class="input @error('date_of_birth') input-error @enderror">
            @error('date_of_birth')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        {{-- Address --}}
        <div>
            <label for="address_line_1" class="label">Address line 1</label>
            <input id="address_line_1" name="address_line_1" type="text" value="{{ old('address_line_1') }}"
                   autocomplete="address-line1" required
                   class="input @error('address_line_1') input-error @enderror">
            @error('address_line_1')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="address_line_2" class="label">Address line 2 <span class="text-plum-300 font-normal">(optional)</span></label>
            <input id="address_line_2" name="address_line_2" type="text" value="{{ old('address_line_2') }}"
                   autocomplete="address-line2"
                   class="input">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="city" class="label">City</label>
                <input id="city" name="city" type="text" value="{{ old('city') }}"
                       autocomplete="address-level2" required
                       class="input @error('city') input-error @enderror">
                @error('city')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="postcode" class="label">Postcode</label>
                <input id="postcode" name="postcode" type="text" value="{{ old('postcode') }}"
                       autocomplete="postal-code" required placeholder="SW1A 1AA"
                       class="input uppercase @error('postcode') input-error @enderror">
                @error('postcode')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password"
                   autocomplete="new-password" required
                   class="input @error('password') input-error @enderror">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   autocomplete="new-password" required
                   class="input">
        </div>

        {{-- Consents --}}
        <div class="space-y-3 pt-1">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="terms_accepted" value="1" required
                       class="mt-0.5 rounded border-plum-300 text-plum-800 focus:ring-plum-800">
                <span class="text-sm text-plum-600">
                    I agree to the
                    <a href="{{ route('terms') }}" target="_blank" class="text-plum-800 underline underline-offset-2 hover:text-plum-600">Terms of Service</a>
                    and
                    <a href="{{ route('privacy') }}" target="_blank" class="text-plum-800 underline underline-offset-2 hover:text-plum-600">Privacy Policy</a>
                </span>
            </label>
            @error('terms_accepted')
                <p class="error-message">{{ $message }}</p>
            @enderror

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="gdpr_marketing_consent" value="1"
                       class="mt-0.5 rounded border-plum-300 text-plum-800 focus:ring-plum-800">
                <span class="text-sm text-plum-500">
                    I'd like to receive health advice and updates from Prescribe &amp; Co
                    <span class="text-plum-400">(optional)</span>
                </span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full">
            Create account
        </button>
    </form>

    <p class="mt-5 text-center text-sm text-plum-500">
        Already have an account?
        <a href="{{ route('login') }}" class="text-plum-800 font-medium hover:underline underline-offset-2">Sign in</a>
    </p>

</x-auth-layout>
