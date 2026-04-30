<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ConsultationService;
use App\Services\ConsultationDraftService;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private ConsultationService $consultationService,
        private ConsultationDraftService $draftService,
        private TwoFactorService $twoFactorService,
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user        = Auth::user();
        $fingerprint = TwoFactorService::generateFingerprint(
            $request->userAgent(),
            $request->ip(),
            $request->header('Accept-Language', '')
        );

        // Check if 2FA should trigger for new device
        if ($this->twoFactorService->shouldTrigger($user, 'new_device_login', $fingerprint)) {
            // Store pending 2FA state in session and redirect
            $request->session()->put('2fa.pending', [
                'user_id'     => $user->id,
                'purpose'     => 'new_device_login',
                'fingerprint' => $fingerprint,
            ]);

            $this->twoFactorService->sendOtp($user, 'new_device_login', $request->ip(), $fingerprint);

            Auth::logout();
            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        // ── Flush consultation session/draft on login ─────────────────────────
        $sessionDraft = $this->draftService->getFromSession($request);
        $cookieUuid   = $request->cookie($this->draftService->cookieName());

        if ($sessionDraft) {
            $consultation = $this->consultationService->flushSessionToConsultation($user, $sessionDraft);
            $this->draftService->clearSession($request);
            if ($consultation) {
                return redirect()->route('patient.consultation.submitted', $consultation)
                    ->with('status', 'consultation-submitted');
            }
        } elseif ($cookieUuid) {
            $draft = $this->draftService->findByUuid($cookieUuid);
            if ($draft) {
                $consultation = $this->consultationService->flushDraftToConsultation($user, $draft);
                if ($consultation) {
                    return redirect()->route('patient.consultation.submitted', $consultation)
                        ->withCookie(\Cookie::forget($this->draftService->cookieName()))
                        ->with('status', 'consultation-submitted');
                }
            }
        }

        return redirect()->intended(route('patient.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
