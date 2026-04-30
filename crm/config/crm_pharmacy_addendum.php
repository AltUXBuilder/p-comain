<?php

/**
 * Phase 8 addition — merge the 'pharmacy' key into config/crm.php
 *
 * Add this block inside the return array in config/crm.php:
 */

return [

    // ... existing keys (ip_whitelist, gphc_regex, etc.) ...

    /*
    |--------------------------------------------------------------------------
    | Pharmacy Details
    |--------------------------------------------------------------------------
    | Printed on every prescription PDF. Set in .env.
    */

    'pharmacy' => [
        'name'        => env('PHARMACY_NAME',        'Prescribe & Co'),
        'address'     => env('PHARMACY_ADDRESS',     ''),
        'gphc_number' => env('PHARMACY_GPHC_NUMBER', ''),
        'phone'       => env('PHARMACY_PHONE',       ''),
        'email'       => env('PHARMACY_EMAIL',       'pharmacy@prescribeandco.co.uk'),
    ],

];

/*
|--------------------------------------------------------------------------
| .env additions required
|--------------------------------------------------------------------------
|
| PHARMACY_NAME="Prescribe & Co"
| PHARMACY_ADDRESS="Your registered pharmacy address"
| PHARMACY_GPHC_NUMBER="1234567"
| PHARMACY_PHONE="+44 ..."
| PHARMACY_EMAIL="pharmacy@prescribeandco.co.uk"
|
*/
