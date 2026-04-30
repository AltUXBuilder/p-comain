<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class TwoFactorSetupController extends Controller
{
    /**
     * Show the 2FA setup / QR code page.
     *
     * GET /two-factor/setup
     */
    public function show(Request $request)
    {
        $staff = Auth::guard('staff')->user();

        // If already confirmed, redirect away (shouldn't reach here normally)
        if ($staff->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor-setup', [
            'staff'  => $staff,
        ]);
    }

    /**
     * Enable 2FA — generate the secret and show QR code.
     * Called the first time the setup page loads (via Alpine fetch or direct POST).
     *
     * POST /two-factor/enable
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $staff = Auth::guard('staff')->user();

        $enable($staff);

        return back()->with('status', 'two-factor-authentication-enabled');
    }

    /**
     * Confirm 2FA enrolment — verify the first TOTP code.
     *
     * POST /two-factor/confirm
     */
    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $confirm(Auth::guard('staff')->user(), $request->code);
        } catch (\Laravel\Fortify\Actions\Exceptions\InvalidTwoFactorAuthenticationCodeException $e) {
            return back()->withErrors(['code' => 'The code you entered is incorrect. Please try again.']);
        }

        // Mark welcome as complete and activate account
        return app(WelcomeController::class)->completeEnrolment();
    }

    /**
     * Regenerate recovery codes.
     *
     * POST /two-factor/recovery-codes/regenerate
     */
    public function regenerateCodes(Request $request, GenerateNewRecoveryCodes $generate)
    {
        $generate(Auth::guard('staff')->user());

        return back()->with('status', 'recovery-codes-generated');
    }

    /**
     * Show recovery codes.
     *
     * GET /two-factor/recovery-codes
     */
    public function recoveryCodes(Request $request)
    {
        return view('auth.two-factor-recovery-codes', [
            'codes' => Auth::guard('staff')->user()->recoveryCodes(),
        ]);
    }
}
