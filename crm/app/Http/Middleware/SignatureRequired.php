<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SignatureRequired
{
    /**
     * Prompt clinical staff to save a signature before they can
     * access prescription-related routes.
     *
     * Applied to the /prescriptions/* route group only.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();

        if (! $staff) {
            return $next($request);
        }

        // Only applies to clinical roles
        if (! $staff->isClinical() && ! $staff->isSuperAdmin()) {
            return $next($request);
        }

        // Allow the signature setup route through
        if ($request->routeIs('profile.signature*')) {
            return $next($request);
        }

        // If no signature on file, redirect to setup
        if (! $staff->signature_path) {
            return redirect()->route('profile.signature')
                ->with('info', 'Please draw and save your prescriber signature before accessing prescriptions.');
        }

        return $next($request);
    }
}
