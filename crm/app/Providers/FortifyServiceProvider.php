<?php

namespace App\Providers;

use App\Actions\Fortify\AuthenticateStaff;
use App\Models\Staff;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Views ────────────────────────────────────────────────────────────

        Fortify::loginView(fn () => view('auth.login'));

        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));

        // ── Custom authentication — log every attempt ─────────────────────

        Fortify::authenticateUsing(function (Request $request) {
            $staff = Staff::where('email', strtolower($request->email))->first();

            if (! $staff) {
                \App\Models\SessionLog::recordAttempt(null, $request, false, 'unknown_email');
                return null;
            }

            if (! \Illuminate\Support\Facades\Hash::check($request->password, $staff->password)) {
                \App\Models\SessionLog::recordAttempt($staff->id, $request, false, 'bad_password');
                return null;
            }

            if (! $staff->active) {
                \App\Models\SessionLog::recordAttempt($staff->id, $request, false, 'inactive_account');
                return null;
            }

            // IP whitelist check happens in middleware — not here
            \App\Models\SessionLog::recordAttempt($staff->id, $request, true, null);

            return $staff;
        });

        // ── Rate limiting ────────────────────────────────────────────────────

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower($request->input(Fortify::username())) . '|' . $request->ip();
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
