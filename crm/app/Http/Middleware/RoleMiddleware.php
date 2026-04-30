<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Enforce staff role restrictions.
     *
     * Usage in routes:
     *   ->middleware('role:super_admin')
     *   ->middleware('role:super_admin,superintendent_pharmacist')
     *
     * Multiple roles are OR-checked — the staff member needs ANY of them.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $staff = Auth::guard('staff')->user();

        if (! $staff) {
            return redirect()->route('login');
        }

        if (empty($roles)) {
            // No roles specified — just require authentication (already checked above)
            return $next($request);
        }

        if ($staff->hasAnyRole($roles)) {
            return $next($request);
        }

        // Audit the access-denied event
        \App\Models\AuditLog::record(
            staffId:    $staff->id,
            action:     'access_denied',
            entityType: 'route',
            entityId:   null,
            metadata:   [
                'url'            => $request->fullUrl(),
                'required_roles' => $roles,
                'staff_role'     => $staff->role,
            ],
            request: $request
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorised.'], 403);
        }

        return response()->view('errors.403', [], 403);
    }
}
