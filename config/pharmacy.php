<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pharmacy Identity
    |--------------------------------------------------------------------------
    */
    'name'         => env('PHARMACY_NAME', 'Prescribe & Co'),
    'trading_name' => env('PHARMACY_TRADING_NAME', 'P&Co'),
    'gphc_number'  => env('PHARMACY_GPHC_NUMBER', ''),
    'address'      => [
        'line_1'   => env('PHARMACY_ADDRESS_1', ''),
        'line_2'   => env('PHARMACY_ADDRESS_2', ''),
        'city'     => env('PHARMACY_CITY', ''),
        'postcode' => env('PHARMACY_POSTCODE', ''),
        'country'  => 'GB',
    ],
    'email'        => env('PHARMACY_EMAIL', 'hello@prescribeandco.co.uk'),
    'phone'        => env('PHARMACY_PHONE', ''),
    'website'      => env('PHARMACY_WEBSITE', 'https://prescribeandco.co.uk'),

    /*
    |--------------------------------------------------------------------------
    | Regulatory
    |--------------------------------------------------------------------------
    */
    'regulatory' => [
        'gphc_registered'   => true,
        'mhra_compliant'    => true,
        'ico_registered'    => true,
        'ico_number'        => env('ICO_REGISTRATION_NUMBER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Age Verification
    |--------------------------------------------------------------------------
    */
    'age_verification' => [
        'minimum_age' => 18,
        // Products requiring age verification will check this at checkout
    ],

    /*
    |--------------------------------------------------------------------------
    | Draft Consultation Settings
    |--------------------------------------------------------------------------
    */
    'draft_consultation' => [
        'expiry_hours'  => 48,
        'cookie_name'   => 'pando_draft_uuid',
        'cookie_secure' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient 2FA Triggers
    | 2FA is NOT required on every login — only on these specific actions
    |--------------------------------------------------------------------------
    */
    'two_factor_triggers' => [
        'new_device_login',
        'access_prescriptions',
        'change_payment',
        'change_address',
    ],

    'two_factor' => [
        'otp_expiry_minutes' => 10,
        'otp_length'         => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Prescription Settings
    |--------------------------------------------------------------------------
    */
    'prescriptions' => [
        'number_prefix'     => 'PAND',
        'valid_days'        => 28, // private prescriptions valid for 28 days by default
        'pdf_storage_disk'  => 'private',
        'pdf_storage_path'  => 'prescriptions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dispensing Labels
    |--------------------------------------------------------------------------
    */
    'labels' => [
        'width_mm'          => 70,
        'height_mm'         => 35,
        'storage_disk'      => 'private',
        'storage_path'      => 'labels',
        'legal_warnings'    => [
            'Keep out of the reach and sight of children.',
            'Store below 25°C unless otherwise stated.',
        ],
        'cold_chain_warning' => 'Store in a refrigerator (2°C–8°C). Do not freeze.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stock Alerts
    |--------------------------------------------------------------------------
    */
    'stock' => [
        'expiry_alert_days' => [30, 60, 90],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consultation Queue Alerts
    |--------------------------------------------------------------------------
    */
    'consultation_queue' => [
        'escalation_hours' => 4, // alert if consultation waiting > 4 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Cold Chain Dispatch Window
    |--------------------------------------------------------------------------
    */
    'cold_chain' => [
        'dispatch_window_hours' => 24, // escalate if cold chain order not dispatched within this window
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Number / Invoice Number Prefixes
    |--------------------------------------------------------------------------
    */
    'order_prefix'   => 'ORD',
    'invoice_prefix' => 'INV',

    /*
    |--------------------------------------------------------------------------
    | Signature Storage
    |--------------------------------------------------------------------------
    */
    'signatures' => [
        'storage_disk' => 'private',
        'storage_path' => 'signatures',
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Renewal Reminder
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        'renewal_reminder_days' => 7,
    ],

];
