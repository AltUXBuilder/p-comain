<?php

use App\Http\Controllers\Auth\WelcomeController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Controllers\Patients\PatientTimelineController;
use App\Http\Controllers\Consultations\ConsultationController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM Routes
|--------------------------------------------------------------------------
|
| Middleware stack applied globally in bootstrap/app.php:
|   web, IpWhitelist
|
| Auth routes additionally get: auth:staff, EnforceTwoFactor
|
*/

// ── Health check (bypasses IP whitelist — see middleware) ─────────────────
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');
Route::get('/ping',   fn () => response('pong'))->name('ping');

// ── Welcome / onboarding ──────────────────────────────────────────────────
Route::prefix('welcome')->name('welcome.')->group(function () {
    Route::get('/{token}',  [WelcomeController::class, 'show'])->name('show');
    Route::post('/{token}', [WelcomeController::class, 'setPassword'])->name('set-password');
});

// ── Fortify auth routes (login, 2FA challenge, logout) ────────────────────
// Fortify registers these automatically. We override views via FortifyServiceProvider.

// ── 2FA setup routes (authenticated but not yet 2FA-enrolled) ────────────
Route::middleware(['web', 'auth:staff'])->group(function () {
    Route::get('/two-factor/setup',                          [TwoFactorSetupController::class, 'show'])->name('two-factor.enable');
    Route::post('/two-factor/enable',                        [TwoFactorSetupController::class, 'enable'])->name('two-factor.enable.post');
    Route::post('/two-factor/confirm',                       [TwoFactorSetupController::class, 'confirm'])->name('two-factor.confirm');
    Route::get('/two-factor/recovery-codes',                 [TwoFactorSetupController::class, 'recoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('/two-factor/recovery-codes/regenerate',     [TwoFactorSetupController::class, 'regenerateCodes'])->name('two-factor.recovery-codes.regenerate');
});

// ── Protected CRM area ────────────────────────────────────────────────────
// auth:staff + EnforceTwoFactor — all staff must have confirmed 2FA
Route::middleware(['web', 'auth:staff', \App\Http\Middleware\EnforceTwoFactor::class])->group(function () {

    // Dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ── Phase 7: Patient Management ───────────────────────────────────────

    Route::prefix('patients')->name('patients.')->middleware('role:super_admin,superintendent_pharmacist,prescriber,customer_support')->group(function () {
        Route::get('/',                  [PatientController::class, 'index'])->name('index');
        Route::get('/{patient}',         [PatientController::class, 'show'])->name('show');
        Route::get('/{patient}/edit',    [PatientController::class, 'edit'])->name('edit');
        Route::put('/{patient}',         [PatientController::class, 'update'])->name('update');
        Route::post('/{patient}/flag',   [PatientController::class, 'flag'])->name('flag');
        Route::post('/{patient}/unflag', [PatientController::class, 'unflag'])->name('unflag');
        Route::post('/{patient}/do-not-treat',     [PatientController::class, 'doNotTreat'])->name('do-not-treat');
        Route::post('/{patient}/deceased',         [PatientController::class, 'markDeceased'])->name('deceased');
        Route::get('/{patient}/timeline',          [PatientTimelineController::class, 'show'])->name('timeline');
    });

    // ── Consultation queue ────────────────────────────────────────────────

    Route::prefix('consultations')->name('consultations.')->middleware('role:super_admin,superintendent_pharmacist,prescriber')->group(function () {
        Route::get('/queue',             [ConsultationController::class, 'queue'])->name('queue');
        Route::get('/{consultation}',    [ConsultationController::class, 'show'])->name('show');
        Route::post('/{consultation}/approve', [ConsultationController::class, 'approve'])->name('approve');
        Route::post('/{consultation}/reject',  [ConsultationController::class, 'reject'])->name('reject');
        Route::post('/{consultation}/flag',    [ConsultationController::class, 'flag'])->name('flag');
    });

    // ── Prescriptions (Phase 8 — stubs) ──────────────────────────────────
    Route::get('/prescriptions', fn () => view('coming-soon', ['module' => 'Prescriptions']))->name('prescriptions.index');

    // ── Dispensing (Phase 9 — stubs) ─────────────────────────────────────
    Route::get('/dispensing/queue', fn () => view('coming-soon', ['module' => 'Dispensing Queue']))->name('dispensing.queue');
    Route::get('/labels',           fn () => view('coming-soon', ['module' => 'Labels']))->name('labels.index');

    // ── Orders ────────────────────────────────────────────────────────────
    Route::get('/orders',   fn () => view('coming-soon', ['module' => 'Orders']))->name('orders.index');
    Route::get('/messages', fn () => view('coming-soon', ['module' => 'Messages']))->name('messages.index');

    // ── Stock ─────────────────────────────────────────────────────────────
    Route::get('/stock', fn () => view('coming-soon', ['module' => 'Stock']))->name('stock.index');

    // ── Finance ───────────────────────────────────────────────────────────
    Route::get('/finance', fn () => view('coming-soon', ['module' => 'Finance']))->name('finance.dashboard')->middleware('role:super_admin,finance');

    // ── Staff management (Phase 17 — Super Admin only) ────────────────────
    Route::prefix('staff')->name('staff.')->middleware('role:super_admin')->group(function () {
        Route::get('/',                    [StaffController::class, 'index'])->name('index');
        Route::get('/create',              [StaffController::class, 'create'])->name('create');
        Route::post('/',                   [StaffController::class, 'store'])->name('store');
        Route::get('/{staff}',             [StaffController::class, 'show'])->name('show');
        Route::get('/{staff}/edit',        [StaffController::class, 'edit'])->name('edit');
        Route::put('/{staff}',             [StaffController::class, 'update'])->name('update');
        Route::post('/{staff}/deactivate', [StaffController::class, 'deactivate'])->name('deactivate');
        Route::post('/{staff}/reactivate', [StaffController::class, 'reactivate'])->name('reactivate');
        Route::post('/{staff}/resend-welcome', [StaffController::class, 'resendWelcome'])->name('resend-welcome');
        Route::post('/{staff}/force-logout',   [StaffController::class, 'forceLogout'])->name('force-logout');
    });

    // ── Compliance ────────────────────────────────────────────────────────
    Route::get('/compliance', fn () => view('coming-soon', ['module' => 'Compliance']))->name('compliance.index')->middleware('role:super_admin');

    // ── Settings (IP whitelist etc.) ──────────────────────────────────────
    Route::get('/settings', fn () => view('coming-soon', ['module' => 'Settings']))->name('settings.index')->middleware('role:super_admin');

    // ── Profile ───────────────────────────────────────────────────────────
    Route::get('/profile', fn () => view('coming-soon', ['module' => 'Profile']))->name('profile.edit');

    // ── Notifications ─────────────────────────────────────────────────────
    Route::get('/notifications', fn () => view('coming-soon', ['module' => 'Notifications']))->name('notifications.index');
    Route::get('/notifications/{notification}/read', fn ($n) => redirect()->route('notifications.index'))->name('notifications.read');

});
