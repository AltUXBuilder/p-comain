<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class WelcomeController extends Controller
{
    /**
     * Step 1 — Show the welcome page (verify token is valid).
     *
     * GET /welcome/{token}
     */
    public function show(string $token)
    {
        $staff = $this->resolveStaffFromToken($token);

        if (! $staff) {
            return view('auth.welcome-invalid');
        }

        return view('auth.welcome', [
            'staff' => $staff,
            'token' => $token,
        ]);
    }

    /**
     * Step 2 — Set password.
     *
     * POST /welcome/{token}
     */
    public function setPassword(Request $request, string $token)
    {
        $staff = $this->resolveStaffFromToken($token);

        if (! $staff) {
            return redirect()->route('login')
                ->withErrors(['token' => 'This welcome link is invalid or has expired.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $staff->update([
            'password' => Hash::make($request->password),
        ]);

        // Log the staff member in via the staff guard
        Auth::guard('staff')->login($staff);

        // Redirect to 2FA enrolment — enforced before any CRM access
        return redirect()->route('two-factor.enable')
            ->with('success', 'Password set. Please set up two-factor authentication to activate your account.');
    }

    /**
     * Step 3 — After TOTP confirmation, mark welcome complete & activate account.
     *
     * Called by Fortify after the two_factor_confirm action succeeds.
     * We hook into this via the FortifyServiceProvider's pipeline.
     */
    public function completeEnrolment()
    {
        $staff = Auth::guard('staff')->user();

        if ($staff && ! $staff->welcome_completed_at) {
            $staff->completeWelcome();
            $staff->update(['active' => true]);

            \App\Models\AuditLog::record(
                staffId:    $staff->id,
                action:     'account_activated',
                entityType: 'staff',
                entityId:   $staff->id,
                metadata:   ['role' => $staff->role],
                request:    request()
            );
        }

        return redirect()->route('dashboard')
            ->with('success', 'Your account is active. Welcome to the P&Co CRM.');
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function resolveStaffFromToken(string $token): ?Staff
    {
        // Find staff whose hashed welcome_token matches
        $staff = Staff::whereNotNull('welcome_token')
            ->whereNotNull('welcome_token_expires_at')
            ->whereNull('welcome_completed_at')
            ->get()
            ->first(fn ($s) => hash('sha256', $token) === $s->welcome_token);

        if (! $staff) {
            return null;
        }

        if (! $staff->isWelcomeTokenValid($token)) {
            return null;
        }

        return $staff;
    }
}
