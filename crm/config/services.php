<?php

/**
 * config/services.php — add these carrier API keys.
 * Merge into the existing config/services.php return array.
 */

return [

    // ... existing services (stripe, mailgun, etc.) ...

    // ── Royal Mail Click & Drop ───────────────────────────────────────────────
    'royal_mail' => [
        'api_key'    => env('ROYAL_MAIL_API_KEY'),
        'oba_number' => env('ROYAL_MAIL_OBA_NUMBER'),
    ],

    // ── DPD ───────────────────────────────────────────────────────────────────
    'dpd' => [
        'username'       => env('DPD_USERNAME'),
        'password'       => env('DPD_PASSWORD'),
        'account_number' => env('DPD_ACCOUNT_NUMBER'),
    ],

    // ── Evri ──────────────────────────────────────────────────────────────────
    'evri' => [
        'client_id'     => env('EVRI_CLIENT_ID'),
        'client_secret' => env('EVRI_CLIENT_SECRET'),
    ],

];

/*
 * .env additions (add to both env.crm.production and local .env):
 *
 * ROYAL_MAIL_API_KEY=
 * ROYAL_MAIL_OBA_NUMBER=
 * DPD_USERNAME=
 * DPD_PASSWORD=
 * DPD_ACCOUNT_NUMBER=
 * EVRI_CLIENT_ID=
 * EVRI_CLIENT_SECRET=
 *
 * If these are blank, the dispatch form falls back to manual tracking number entry.
 * The "Generate label via API" checkbox on the dispatch modal controls whether
 * the API is attempted.
 */
