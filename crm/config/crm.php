<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist (static)
    |--------------------------------------------------------------------------
    |
    | These IPs are always allowed regardless of the database-managed list.
    | Add your office/VPN IP range here. CIDR notation supported.
    |
    | Additional IPs can be managed at runtime in the CRM Settings panel
    | (stored in the ip_whitelist DB table, cached for 5 minutes).
    |
    | Leave empty to deny ALL external access (lockdown mode).
    | At least one entry is required for anyone to log in.
    |
    */

    'ip_whitelist' => array_filter(
        explode(',', env('CRM_IP_WHITELIST', '127.0.0.1,::1'))
    ),

    /*
    |--------------------------------------------------------------------------
    | GPhC Validation
    |--------------------------------------------------------------------------
    */

    'gphc_regex' => '/^\d{7}$/',

    /*
    |--------------------------------------------------------------------------
    | Welcome Token TTL (hours)
    |--------------------------------------------------------------------------
    */

    'welcome_token_ttl' => 72,

    /*
    |--------------------------------------------------------------------------
    | Session
    |--------------------------------------------------------------------------
    */

    'session_driver'   => 'database',
    'session_lifetime' => 480, // 8 hours

];

    // ── Additional config (Phase 19) ─────────────────────────────────────────
    'consultation_review_threshold_hours' => env('CRM_CONSULTATION_REVIEW_THRESHOLD_HOURS', 24),
 * Phase 8 addition — merge the 'pharmacy' key into config/crm.php
 *
 * Add this block inside the return array in config/crm.php:
 */

return [

    // ... existing keys (ip_whitelist, gphc_regex, etc.) ...

--
    'pharmacy' => [
        'name'        => env('PHARMACY_NAME',        'Prescribe & Co'),
        'address'     => env('PHARMACY_ADDRESS',     ''),
        'gphc_number' => env('PHARMACY_GPHC_NUMBER', ''),
        'phone'       => env('PHARMACY_PHONE',       ''),
