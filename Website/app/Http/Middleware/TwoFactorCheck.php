<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorCheck
{
    public function __construct(private TwoFactorService $twoFactorService) {}

    /**
     * Trigger 2FA if the patient hasn't verified it for this action in this session.
     * Usage: ->middleware('2fa:access_prescriptions') on a route group.
     */
    public function handle(Request $request, Closure $next, string $purpose = 'access_prescriptions'): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if this purpose has already been verified in this session (valid for 30 min)
        $verifiedAt = session("2fa.verified.{$purpose}");
        if ($verifiedAt && (now()->timestamp - $verifiedAt) < 1800) {
            return $next($request);
        }

        // Trigger 2FA
        $fingerprint = TwoFactorService::generateFingerprint(
            $request->userAgent(), $request->ip(), $request->header('Accept-Language', '')
        );

        $this->twoFactorService->sendOtp($user, $purpose, $request->ip(), $fingerprint);

        // Store where to return after verification
        $request->session()->put("2fa.pending", [
            'user_id'     => $user->id,
            'purpose'     => $purpose,
            'fingerprint' => $fingerprint,
        ]);

        return redirect()->route('two-factor.sensitive', [
            'purpose'  => $purpose,
            'redirect' => $request->fullUrl(),
        ]);
    }
}
