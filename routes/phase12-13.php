<?php

/**
 * Phase 12 & 13 route additions — merge into routes/web.php.
 *
 * Replaces stubs:
 *   Route::get('/messages',   fn () => view('coming-soon', ...))->name('messages.index');
 *   Route::get('/compliance', fn () => view('coming-soon', ...))->name('compliance.index');
 *   Route::get('/finance',    fn () => view('coming-soon', ...))->name('finance.dashboard');
 */

use App\Http\Controllers\Workflow\WorkflowController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Compliance\ComplianceController;
use App\Http\Controllers\Messages\MessagesController;
use Illuminate\Support\Facades\Route;

// ── Phase 12: Workflow Automation Builder (Super Admin only) ─────────────────

Route::prefix('workflow')
    ->name('workflow.')
    ->middleware('role:super_admin')
    ->group(function () {
        Route::get('/',                          [WorkflowController::class, 'index'])->name('index');
        Route::get('/create',                    [WorkflowController::class, 'create'])->name('create');
        Route::post('/',                         [WorkflowController::class, 'store'])->name('store');
        Route::get('/{workflowRule}/edit',       [WorkflowController::class, 'edit'])->name('edit');
        Route::put('/{workflowRule}',            [WorkflowController::class, 'update'])->name('update');
        Route::post('/{workflowRule}/toggle',    [WorkflowController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{workflowRule}',         [WorkflowController::class, 'destroy'])->name('destroy');
    });

// ── Phase 13: Finance ─────────────────────────────────────────────────────────

Route::prefix('finance')
    ->name('finance.')
    ->middleware('role:super_admin,finance')
    ->group(function () {
        Route::get('/',                          [FinanceController::class, 'dashboard'])->name('dashboard');
        Route::get('/vat-report',                [FinanceController::class, 'vatReport'])->name('vat-report');
        Route::get('/vat-report/export',         [FinanceController::class, 'vatReportExport'])->name('vat-report.export');
        Route::get('/invoices',                  [FinanceController::class, 'invoices'])->name('invoices');
        Route::post('/invoices/order/{order}',   [FinanceController::class, 'generateInvoice'])->name('invoices.generate');
        Route::get('/invoices/{invoice}/pdf',    [FinanceController::class, 'invoicePdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/void',  [FinanceController::class, 'voidInvoice'])->name('invoices.void');
    });

// Stripe webhook — no auth, no IP whitelist (public endpoint)
Route::post('/webhook/stripe', [FinanceController::class, 'stripeWebhook'])
    ->name('webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\IpWhitelist::class]);

// ── Phase 13: Compliance & Governance ─────────────────────────────────────────

Route::prefix('compliance')
    ->name('compliance.')
    ->middleware('role:super_admin,superintendent_pharmacist')
    ->group(function () {
        Route::get('/',                  [ComplianceController::class, 'index'])->name('index');
        Route::get('/log',               [ComplianceController::class, 'log'])->name('log');
        Route::post('/log',              [ComplianceController::class, 'store'])->name('log.store');
        Route::get('/rejections',        [ComplianceController::class, 'rejections'])->name('rejections');
        Route::get('/rejections/export', [ComplianceController::class, 'rejectionsExport'])->name('rejections.export');
        Route::get('/audit-log',         [ComplianceController::class, 'auditLog'])->name('audit-log');
        Route::get('/audit-log/export',  [ComplianceController::class, 'auditLogExport'])->name('audit-log.export');
    });

// ── Phase 12: Messages & Communications Hub ────────────────────────────────────

Route::prefix('messages')
    ->name('messages.')
    ->middleware('role:super_admin,superintendent_pharmacist,prescriber,customer_support')
    ->group(function () {
        Route::get('/',                        [MessagesController::class, 'index'])->name('index');
        Route::post('/send',                   [MessagesController::class, 'send'])->name('send');
        Route::get('/consultation/{consultation}', [MessagesController::class, 'thread'])->name('thread');
        Route::get('/patient/{patient}',       [MessagesController::class, 'patientThread'])->name('patient');
        Route::get('/bulk',                    [MessagesController::class, 'bulk'])->name('bulk');
        Route::post('/bulk',                   [MessagesController::class, 'bulk'])->name('bulk.send')
            ->middleware('role:super_admin,superintendent_pharmacist');
    });
