<?php

/**
 * PATCH — FortifyServiceProvider.php
 *
 * Replace the existing authenticateUsing() closure in app/Providers/FortifyServiceProvider.php
 * with the version below to add suspicious login detection.
 *
 * The only change is calling SuspiciousLoginDetector::analyse() on successful login.
 */

namespace App\Providers;

use App\Models\Staff;
use App\Services\SuspiciousLoginDetector;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));

        Fortify::authenticateUsing(function (Request $request) {
            $staff = Staff::where('email', strtolower($request->email))->first();

            if (! $staff) {
                \App\Models\SessionLog::recordAttempt(null, $request, false, 'unknown_email');
                return null;
            }

            if (! Hash::check($request->password, $staff->password)) {
                \App\Models\SessionLog::recordAttempt($staff->id, $request, false, 'bad_password');
                return null;
            }

            if (! $staff->active) {
                \App\Models\SessionLog::recordAttempt($staff->id, $request, false, 'inactive_account');
                return null;
            }

            // Record successful login
            \App\Models\SessionLog::recordAttempt($staff->id, $request, true, null);

            // ── Suspicious login detection (Phase 17 addition) ────────────────
            app(SuspiciousLoginDetector::class)->analyse($staff, $request);

            return $staff;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower($request->input(Fortify::username())) . '|' . $request->ip();
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
