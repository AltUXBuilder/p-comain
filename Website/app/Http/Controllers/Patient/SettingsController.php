<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function updateAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'county'         => 'nullable|string|max:100',
            'postcode'       => ['required', 'string', 'max:10', 'regex:/^[A-Z]{1,2}[0-9][0-9A-Z]?\s?[0-9][A-Z]{2}$/i'],
        ]);

        $validated['postcode'] = strtoupper(str_replace(' ', '', $validated['postcode']));
        auth()->user()->update($validated);

        return back()->with('success', 'Delivery address updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        auth()->user()->update([
            'gdpr_marketing_consent' => $request->boolean('gdpr_marketing_consent'),
        ]);

        return back()->with('success', 'Notification preferences saved.');
    }
}
