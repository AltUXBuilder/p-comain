<?php

use App\Http\Controllers\Auth\WelcomeController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Patients\PatientController;
use App\Http\Controllers\Patients\PatientTimelineController;
use App\Http\Controllers\Consultations\ConsultationController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

// ── Health check ──────────────────────────────────────────────────────────
Route::get('/health', fn () => response()->json(['status' => 'ok']))->name('health');
Route::get('/ping',   fn () => response('pong'))->name('ping');

// ── Welcome / onboarding ──────────────────────────────────────────────────
Route::prefix('welcome')->name('welcome.')->group(function () {
    Route::get('/{token}',  [WelcomeController::class, 'show'])->name('show');
    Route::post('/{token}', [WelcomeController::class, 'setPassword'])->name('set-password');
});

// ── 2FA setup (authenticated but not yet enrolled) ────────────────────────
Route::middleware(['web', 'auth:staff'])->group(function () {
    Route::get('/two-factor/setup',    [TwoFactorSetupController::class, 'show'])->name('two-factor.enable');
    Route::post('/two-factor/enable',  [TwoFactorSetupController::class, 'enable'])->name('two-factor.enable.post');
    Route::post('/two-factor/confirm', [TwoFactorSetupController::class, 'confirm'])->name('two-factor.confirm');
});

// ── Stripe webhook (no auth, no IP whitelist) ─────────────────────────────
Route::post('/webhook/stripe', [\App\Http\Controllers\Finance\FinanceController::class, 'stripeWebhook'])
    ->name('webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\IpWhitelist::class]);

// ── Authenticated CRM routes ──────────────────────────────────────────────
Route::middleware(['web', 'auth:staff', \App\Http\Middleware\EnforceTwoFactor::class])
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Patients
    Route::prefix('patients')->name('patients.')
        ->middleware('role:super_admin,superintendent_pharmacist,prescriber,customer_support')
        ->group(function () {
            Route::get('/',                    [PatientController::class, 'index'])->name('index');
            Route::get('/{patient}',           [PatientController::class, 'show'])->name('show');
            Route::get('/{patient}/edit',      [PatientController::class, 'edit'])->name('edit');
            Route::put('/{patient}',           [PatientController::class, 'update'])->name('update');
            Route::post('/{patient}/flag',     [PatientController::class, 'flag'])->name('flag');
            Route::post('/{patient}/unflag',   [PatientController::class, 'unflag'])->name('unflag');
            Route::post('/{patient}/dnt',      [PatientController::class, 'doNotTreat'])->name('dnt');
            Route::post('/{patient}/deceased', [PatientController::class, 'markDeceased'])->name('deceased');
            Route::get('/{patient}/timeline',  [PatientTimelineController::class, 'show'])->name('timeline');
        });

    // Consultations
    Route::prefix('consultations')->name('consultations.')
        ->middleware('role:super_admin,superintendent_pharmacist,prescriber')
        ->group(function () {
            Route::get('/',                        [ConsultationController::class, 'index'])->name('index');
            Route::get('/queue',                   [ConsultationController::class, 'queue'])->name('queue');
            Route::get('/{consultation}',          [ConsultationController::class, 'show'])->name('show');
            Route::post('/{consultation}/approve', [ConsultationController::class, 'approve'])->name('approve');
            Route::post('/{consultation}/reject',  [ConsultationController::class, 'reject'])->name('reject');
            Route::post('/{consultation}/flag',    [ConsultationController::class, 'flag'])->name('flag');
        });

    // Staff management
    Route::prefix('staff')->name('staff.')->middleware('role:super_admin')->group(function () {
        Route::get('/',                        [StaffController::class, 'index'])->name('index');
        Route::get('/create',                  [StaffController::class, 'create'])->name('create');
        Route::post('/',                       [StaffController::class, 'store'])->name('store');
        Route::get('/{staff}',                 [StaffController::class, 'show'])->name('show');
        Route::get('/{staff}/edit',            [StaffController::class, 'edit'])->name('edit');
        Route::put('/{staff}',                 [StaffController::class, 'update'])->name('update');
        Route::post('/{staff}/deactivate',     [StaffController::class, 'deactivate'])->name('deactivate');
        Route::post('/{staff}/reactivate',     [StaffController::class, 'reactivate'])->name('reactivate');
        Route::post('/{staff}/resend-welcome', [StaffController::class, 'resendWelcome'])->name('resend-welcome');
        Route::post('/{staff}/force-logout',   [StaffController::class, 'forceLogout'])->name('force-logout');
    });

    // Profile / signature
    Route::get('/profile/signature',     [\App\Http\Controllers\Prescriptions\SignatureController::class, 'show'])->name('profile.signature');
    Route::post('/profile/signature',    [\App\Http\Controllers\Prescriptions\SignatureController::class, 'store'])->name('profile.signature.store');
    Route::delete('/profile/signature',  [\App\Http\Controllers\Prescriptions\SignatureController::class, 'destroy'])->name('profile.signature.destroy');

    // Settings
    Route::get('/settings', fn () => view('coming-soon', ['module' => 'Settings']))->name('settings.index')->middleware('role:super_admin');

    // Notifications
    Route::get('/notifications', fn () => view('coming-soon', ['module' => 'Notifications']))->name('notifications.index');

    // Module route files
    require __DIR__.'/prescriptions.php';
    require __DIR__.'/dispensing.php';
    require __DIR__.'/stock-orders.php';
    require __DIR__.'/phase12-13.php';
    require __DIR__.'/phase14-15.php';
    require __DIR__.'/phase17.php';
    require __DIR__.'/phase18.php';
    require __DIR__.'/audit-fixes.php';
    require __DIR__.'/patch.php';
});
