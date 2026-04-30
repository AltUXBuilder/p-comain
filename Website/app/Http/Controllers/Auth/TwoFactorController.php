<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactorService) {}

    /**
     * Show the 2FA challenge screen (email OTP entry).
     */
    public function challenge(): View|RedirectResponse
    {
        if (!session()->has('2fa.pending')) {
            return redirect()->route('login');
        }
        $purpose = session('2fa.pending.purpose');
        return view('auth.two-factor', ['purpose' => $purpose]);
    }

    /**
     * Verify the OTP submitted by the patient.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $pending = session('2fa.pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($pending['user_id']);

        if (!$this->twoFactorService->verifyOtp($user, $pending['purpose'], $request->code)) {
            return back()->withErrors(['code' => 'The code is invalid or has expired. Please request a new one.']);
        }

        // OTP verified — log the user in
        Auth::login($user);
        $request->session()->forget('2fa.pending');
        $request->session()->regenerate();

        // Trust this device for future logins
        if ($pending['purpose'] === 'new_device_login') {
            $user->trustDevice($pending['fingerprint'], $request->ip());
        }

        return redirect()->intended(route('patient.dashboard'));
    }

    /**
     * Resend the OTP email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $pending = session('2fa.pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($pending['user_id']);
        $this->twoFactorService->sendOtp($user, $pending['purpose'], $request->ip(), $pending['fingerprint'] ?? '');

        return back()->with('status', 'A new verification code has been sent to your email address.');
    }

    /**
     * Trigger 2FA for a sensitive mid-session action (prescriptions, payment, address).
     * Called from middleware when patient tries to access a protected action.
     */
    public function sensitiveChallenge(Request $request): View
    {
        $purpose = $request->get('purpose', 'access_prescriptions');
        return view('auth.two-factor', ['purpose' => $purpose, 'sensitive' => true]);
    }

    /**
     * Verify OTP for a sensitive mid-session action.
     */
    public function sensitiveVerify(Request $request): RedirectResponse
    {
        $request->validate([
            'code'     => ['required', 'string', 'size:6'],
            'purpose'  => ['required', 'string'],
            'redirect' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $fingerprint = TwoFactorService::generateFingerprint(
            $request->userAgent(), $request->ip(), $request->header('Accept-Language', '')
        );

        if (!$this->twoFactorService->verifyOtp($user, $request->purpose, $request->code)) {
            return back()->withErrors(['code' => 'The code is invalid or has expired.']);
        }

        // Mark 2FA as completed for this session action
        $request->session()->put("2fa.verified.{$request->purpose}", now()->timestamp);

        $redirectTo = $request->redirect ?: route('patient.dashboard');
        return redirect($redirectTo);
    }
}
