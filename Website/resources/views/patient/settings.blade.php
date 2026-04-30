<x-patient-layout>
    <x-slot name="title">Account Settings</x-slot>
    <x-slot name="pageTitle">Account Settings</x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- ── Personal information (read-only) ────────────────────────────── --}}
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Personal information</h2>
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="label">Full name</p>
                    <p class="text-plum-700">{{ auth()->user()->full_name }}</p>
                </div>
                <div>
                    <p class="label">Email address</p>
                    <p class="text-plum-700">{{ auth()->user()->email }}</p>
                </div>
                <div>
                    <p class="label">Date of birth</p>
                    <p class="text-plum-700">{{ auth()->user()->date_of_birth->format('j F Y') }}</p>
                </div>
                <div>
                    <p class="label">Account created</p>
                    <p class="text-plum-700">{{ auth()->user()->created_at->format('j F Y') }}</p>
                </div>
            </div>
            <p class="text-xs text-plum-400 mt-4">To update your name, email or date of birth, please contact our support team.</p>
        </div>

        {{-- ── Delivery address (2FA protected) ───────────────────────────── --}}
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Delivery address</h2>
            <form method="POST" action="{{ route('patient.settings.address') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="address_line_1" class="label">Address line 1</label>
                    <input id="address_line_1" name="address_line_1" type="text"
                           value="{{ old('address_line_1', auth()->user()->address_line_1) }}"
                           class="input @error('address_line_1') input-error @enderror" required>
                    @error('address_line_1')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="address_line_2" class="label">Address line 2 <span class="font-normal text-plum-300">(optional)</span></label>
                    <input id="address_line_2" name="address_line_2" type="text"
                           value="{{ old('address_line_2', auth()->user()->address_line_2) }}"
                           class="input">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="label">City</label>
                        <input id="city" name="city" type="text"
                               value="{{ old('city', auth()->user()->city) }}"
                               class="input @error('city') input-error @enderror" required>
                        @error('city')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="postcode" class="label">Postcode</label>
                        <input id="postcode" name="postcode" type="text"
                               value="{{ old('postcode', auth()->user()->postcode) }}"
                               class="input uppercase @error('postcode') input-error @enderror" required>
                        @error('postcode')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-1">
                    <button type="submit" class="btn-primary btn-sm">Save address</button>
                    <p class="text-xs text-plum-400">
                        <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        Verification required
                    </p>
                </div>
            </form>
        </div>

        {{-- ── Change password ──────────────────────────────────────────────── --}}
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Change password</h2>
            <form method="POST" action="{{ route('patient.settings.password') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="current_password" class="label">Current password</label>
                    <input id="current_password" name="current_password" type="password"
                           autocomplete="current-password"
                           class="input @error('current_password') input-error @enderror" required>
                    @error('current_password')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="new_password" class="label">New password</label>
                    <input id="new_password" name="password" type="password"
                           autocomplete="new-password"
                           class="input @error('password') input-error @enderror" required>
                    @error('password')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password" class="input" required>
                </div>
                <button type="submit" class="btn-secondary btn-sm">Update password</button>
            </form>
        </div>

        {{-- ── Notification preferences ─────────────────────────────────────── --}}
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold text-plum-800 mb-4">Email preferences</h2>
            <form method="POST" action="{{ route('patient.settings.notifications') }}" class="space-y-4">
                @csrf
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="gdpr_marketing_consent" value="1"
                           {{ auth()->user()->gdpr_marketing_consent ? 'checked' : '' }}
                           class="mt-0.5 rounded border-plum-300 text-plum-800 focus:ring-plum-800">
                    <div>
                        <p class="text-sm font-medium text-plum-800">Health advice &amp; updates</p>
                        <p class="text-xs text-plum-400 mt-0.5">Receive health tips, treatment updates and offers from Prescribe &amp; Co.</p>
                    </div>
                </label>
                <p class="text-xs text-plum-400">You will always receive transactional emails about your orders and prescriptions regardless of this setting.</p>
                <button type="submit" class="btn-secondary btn-sm">Save preferences</button>
            </form>
        </div>

        {{-- ── GP Surgery ───────────────────────────────────────────────────── --}}
        <div class="card p-6">
            <h2 class="font-display text-lg font-bold text-plum-800 mb-2">GP surgery</h2>
            <p class="text-sm text-plum-500 mb-3">
                @if (auth()->user()->gpSurgery)
                    {{ auth()->user()->gpSurgery->name }}, {{ auth()->user()->gpSurgery->city }}
                @else
                    No GP surgery linked.
                @endif
            </p>
            <p class="text-xs text-plum-400">To update your GP surgery, please contact our team.</p>
        </div>

    </div>
</x-patient-layout>
