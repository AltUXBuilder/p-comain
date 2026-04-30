<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * All CRM routes are protected by this middleware.
     * Requests from IPs not in the whitelist receive a 403.
     *
     * Whitelist is stored in config/crm.php under 'ip_whitelist'.
     * Can also be managed at runtime via the CRM settings panel
     * (stored in the database, cached with a short TTL).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow health-check pings to bypass (Hostinger uptime monitor)
        if ($request->is('health') || $request->is('ping')) {
            return $next($request);
        }

        $clientIp = $request->ip();

        // Merge static config list with DB-persisted list (cached 5 min)
        $whitelist = $this->resolveWhitelist();

        // Empty whitelist = lockdown mode — deny all (safety default)
        if (empty($whitelist)) {
            return $this->denyResponse($request, $clientIp);
        }

        foreach ($whitelist as $allowed) {
            $allowed = trim($allowed);
            if ($this->ipMatches($clientIp, $allowed)) {
                return $next($request);
            }
        }

        return $this->denyResponse($request, $clientIp);
    }

    /**
     * Merge the static config list with the DB-stored list.
     * DB list is cached for 5 minutes to avoid a query on every request.
     */
    protected function resolveWhitelist(): array
    {
        $static = config('crm.ip_whitelist', []);

        $dynamic = \Illuminate\Support\Facades\Cache::remember(
            'crm:ip_whitelist',
            300, // 5 minutes
            function () {
                try {
                    return \App\Models\IpWhitelistEntry::where('active', true)
                        ->pluck('ip_address')
                        ->toArray();
                } catch (\Throwable $e) {
                    // Table may not exist yet on fresh install — fail open to static list
                    return [];
                }
            }
        );

        return array_unique(array_merge($static, $dynamic));
    }

    /**
     * Match a client IP against an entry which may be:
     *   - an exact IPv4 or IPv6 address
     *   - a CIDR range (e.g. 192.168.1.0/24)
     */
    protected function ipMatches(string $clientIp, string $entry): bool
    {
        if (! str_contains($entry, '/')) {
            // Exact match
            return $clientIp === $entry;
        }

        // CIDR range
        return $this->ipInCidr($clientIp, $entry);
    }

    protected function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        // Only IPv4 CIDR supported for now
        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = $bits === 0 ? 0 : (~0 << (32 - $bits));
        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    protected function denyResponse(Request $request, string $ip): Response
    {
        // Log the blocked attempt
        \Illuminate\Support\Facades\Log::warning('CRM IP whitelist block', [
            'ip'         => $ip,
            'url'        => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'staff_id'   => Auth::guard('staff')->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        return response()->view('errors.ip-blocked', ['ip' => $ip], 403);
    }
}
