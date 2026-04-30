<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceTwoFactor
{
    /**
     * Force all authenticated staff to complete TOTP 2FA enrolment
     * before they can access any CRM page.
     *
     * Staff who have authenticated but not yet enrolled are redirected
     * to the 2FA setup wizard. This is distinct from the Fortify
     * two-factor challenge (which handles the per-login TOTP code prompt).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();

        if (! $staff) {
            return $next($request);
        }

        // If 2FA is not yet confirmed, redirect to enrolment
        // Allow the setup routes through to avoid a redirect loop
        $setupRoutes = [
            'two-factor.enable',
            'two-factor.confirm',
            'two-factor.qr-code',
            'two-factor.secret-key',
            'two-factor.recovery-codes',
            'logout',
        ];

        $currentRoute = $request->route()?->getName();

        if (
            ! $staff->hasTwoFactorEnabled()
            && ! in_array($currentRoute, $setupRoutes)
            && ! $request->is('two-factor*')
            && ! $request->is('logout')
        ) {
            return redirect()->route('two-factor.enable')
                ->with('info', 'You must set up two-factor authentication before accessing the CRM.');
        }

        return $next($request);
    }
}
