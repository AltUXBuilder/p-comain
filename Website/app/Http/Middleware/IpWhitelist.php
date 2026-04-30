<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $trustedIps = array_filter(
            array_map('trim', explode(',', env('TRUSTED_IPS', '127.0.0.1')))
        );

        // Support CIDR notation or direct IP matching
        $clientIp = $request->ip();

        foreach ($trustedIps as $trustedIp) {
            if ($this->ipMatches($clientIp, $trustedIp)) {
                return $next($request);
            }
        }

        abort(403, 'Access denied. Your IP address is not authorised to access this system.');
    }

    private function ipMatches(string $clientIp, string $trustedIp): bool
    {
        // Direct match
        if ($clientIp === $trustedIp) {
            return true;
        }

        // CIDR range match
        if (str_contains($trustedIp, '/')) {
            return $this->ipInCidr($clientIp, $trustedIp);
        }

        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
            filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong     = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $mask       = -1 << (32 - (int) $bits);
            return ($ipLong & $mask) === ($subnetLong & $mask);
        }

        return false;
    }
}
