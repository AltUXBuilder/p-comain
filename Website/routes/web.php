<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Marketing\HomeController;
use App\Http\Controllers\Marketing\TreatmentController;
use App\Http\Controllers\Marketing\ProductController;
use App\Http\Controllers\Marketing\BlogController;
use App\Http\Controllers\Marketing\ContactController;
use App\Http\Controllers\Patient\DashboardController;
use App\Http\Controllers\Patient\ConsultationController;
use App\Http\Controllers\Patient\PrescriptionController;
use App\Http\Controllers\Patient\OrderController;
use App\Http\Controllers\Patient\SubscriptionController;
use App\Http\Controllers\Patient\MessageController;
use App\Http\Controllers\Checkout\CartController;
use App\Http\Controllers\Checkout\PaymentController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// ── Public marketing pages ────────────────────────────────────────────────────
Route::get('/',                     [HomeController::class, 'index'])->name('home');
Route::get('/about',                [HomeController::class, 'about'])->name('about');
Route::get('/how-it-works',         [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/pricing',              [HomeController::class, 'pricing'])->name('pricing');
Route::get('/faq',                  [HomeController::class, 'faq'])->name('faq');
Route::get('/contact',              [ContactController::class, 'show'])->name('contact');
Route::post('/contact',             [ContactController::class, 'store'])->name('contact.submit');
Route::get('/delivery-returns',     fn() => view('marketing.delivery-returns'))->name('delivery-returns');
Route::get('/privacy',              fn() => view('marketing.privacy'))->name('privacy');
Route::get('/terms',                fn() => view('marketing.terms'))->name('terms');
Route::get('/cookies',              fn() => view('marketing.cookies'))->name('cookies');
Route::get('/accessibility',        fn() => view('marketing.accessibility'))->name('accessibility');

// ── Treatments & Products ─────────────────────────────────────────────────────
Route::get('/treatments',                           [TreatmentController::class, 'index'])->name('treatments.index');
Route::get('/treatments/{category:slug}',           [TreatmentController::class, 'category'])->name('treatments.category');
Route::get('/treatments/{category:slug}/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// ── Blog ──────────────────────────────────────────────────────────────────────
Route::get('/blog',          [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}',   [BlogController::class, 'show'])->name('blog.show');

// ── Consultation flows (no auth required to start) ────────────────────────────
Route::prefix('consultation')->name('consultation.')->group(function () {
    Route::get('/{product:slug}',   [ConsultationController::class, 'start'])->name('start');
    Route::get('/{product:slug}/gate', [ConsultationController::class, 'gate'])->name('gate');
    Route::post('/session/save',    [ConsultationController::class, 'saveSession'])->name('session.save');
    Route::get('/resume/{uuid}',    [ConsultationController::class, 'resume'])->name('resume');
});

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',  [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login',     [LoginController::class, 'create'])->name('login');
    Route::post('/login',    [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// Email verification
Route::get('/email/verify',               fn() => view('auth.verify-email'))->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}',   [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Password reset
Route::get('/forgot-password',    [\App\Http\Controllers\Auth\PasswordController::class, 'request'])->middleware('guest')->name('password.request');
Route::post('/forgot-password',   [\App\Http\Controllers\Auth\PasswordController::class, 'email'])->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordController::class, 'reset'])->middleware('guest')->name('password.reset');
Route::post('/reset-password',    [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->middleware('guest')->name('password.update');

// ── Patient 2FA ───────────────────────────────────────────────────────────────
Route::prefix('verify')->name('two-factor.')->group(function () {
    Route::get('/challenge',    [TwoFactorController::class, 'challenge'])->name('challenge');
    Route::post('/challenge',   [TwoFactorController::class, 'verify'])->name('verify');
    Route::post('/resend',      [TwoFactorController::class, 'resend'])->name('resend');
    Route::get('/sensitive',    [TwoFactorController::class, 'sensitiveChallenge'])->middleware('auth')->name('sensitive');
    Route::post('/sensitive',   [TwoFactorController::class, 'sensitiveVerify'])->middleware('auth')->name('sensitive.verify');
});

// ── Patient portal (auth + verified) ─────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('account')->name('patient.')->group(function () {

    Route::get('/dashboard',    [DashboardController::class, 'index'])->name('dashboard');

    // Consultations
    Route::get('/consultations',               [ConsultationController::class, 'index'])->name('consultations.index');
    Route::get('/consultations/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
    Route::get('/consultation/{consultation}/submitted', [ConsultationController::class, 'submitted'])->name('consultation.submitted');
    Route::get('/consultation/{product:slug}/checkout-ready', [ConsultationController::class, 'checkoutReady'])->name('consultation.checkout-ready');

    // Prescriptions (2FA triggered)
    Route::middleware('2fa:access_prescriptions')->group(function () {
        Route::get('/prescriptions',               [PrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');
        Route::get('/prescriptions/{prescription}/download', [PrescriptionController::class, 'download'])->name('prescriptions.download');
    });

    // Orders
    Route::get('/orders',         [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Subscriptions
    Route::get('/subscriptions',              [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{subscription}/pause',  [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::post('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // Messages
    Route::get('/messages',                   [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{consultation}',     [MessageController::class, 'thread'])->name('messages.thread');
    Route::post('/messages/{consultation}',    [MessageController::class, 'send'])->name('messages.send');

    // Settings
    Route::get('/settings',                   fn() => view('patient.settings'))->name('settings');

    // Payment management (2FA triggered)
    Route::middleware('2fa:change_payment')->group(function () {
        Route::post('/settings/payment',      [\App\Http\Controllers\Patient\SettingsController::class, 'updatePayment'])->name('settings.payment');
    });

    // Address management (2FA triggered)
    Route::middleware('2fa:change_address')->group(function () {
        Route::post('/settings/address',      [\App\Http\Controllers\Patient\SettingsController::class, 'updateAddress'])->name('settings.address');
    });
});

// ── Checkout ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/',           [CartController::class, 'index'])->name('index');
    Route::post('/initiate',  [PaymentController::class, 'initiate'])->name('initiate');
    Route::post('/confirm',              [PaymentController::class, 'confirm'])->name('confirm');
    Route::post('/confirm-subscription', [PaymentController::class, 'confirmSubscription'])->name('confirm-subscription');
    Route::get('/success',    [PaymentController::class, 'success'])->name('success');
    Route::get('/cancelled',  [PaymentController::class, 'cancelled'])->name('cancelled');
});

// ── Stripe Webhook ────────────────────────────────────────────────────────────
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');

// ── Additional patient settings routes ───────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('account')->name('patient.')->group(function () {
    Route::put('/settings/password',       [\App\Http\Controllers\Patient\SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/notifications', [\App\Http\Controllers\Patient\SettingsController::class, 'updateNotifications'])->name('settings.notifications');
});
