<?php

use Laravel\Fortify\Features;

return [

    /*
    |--------------------------------------------------------------------------
    | Fortify Guard
    |--------------------------------------------------------------------------
    | Use the staff guard — all CRM authentication flows use this.
    */

    'guard' => 'staff',

    /*
    |--------------------------------------------------------------------------
    | Fortify Password Broker
    |--------------------------------------------------------------------------
    */

    'passwords' => 'staff',

    /*
    |--------------------------------------------------------------------------
    | Username / Email
    |--------------------------------------------------------------------------
    */

    'username' => 'email',

    'email' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Home Path
    |--------------------------------------------------------------------------
    | Where staff are redirected after authentication.
    */

    'home' => '/dashboard',

    /*
    |--------------------------------------------------------------------------
    | Lowercase Usernames
    |--------------------------------------------------------------------------
    */

    'lowercase_usernames' => true,

    /*
    |--------------------------------------------------------------------------
    | Prefix
    |--------------------------------------------------------------------------
    | No prefix — CRM sits at root.
    */

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'limiters' => [
        'login'              => 'login',
        'two-factor'         => 'two-factor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Register New Users
    |--------------------------------------------------------------------------
    | Staff accounts are created by Super Admin only. Registration route
    | must NOT be exposed. Fortify registration is disabled.
    */

    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    | TOTP 2FA is mandatory. Registration and email verification are disabled
    | (staff onboarding uses the welcome token flow instead).
    */

    'features' => [
        // Features::registration(),       // DISABLED — Super Admin creates accounts
        // Features::resetPasswords(),     // DISABLED — welcome flow handles password set
        // Features::emailVerification(),  // DISABLED
        Features::twoFactorAuthentication([
            'confirm'        => true,     // require TOTP confirmation on enrolment
            'confirmPassword' => true,
            'window'         => 1,        // allow ±1 TOTP window for clock drift
        ]),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
    ],

];
