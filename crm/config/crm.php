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
