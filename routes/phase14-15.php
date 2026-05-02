<?php

/**
 * Phase 14 & 15 routes — merge into routes/web.php
 * inside the auth:staff + EnforceTwoFactor group.
 */

use App\Http\Controllers\Analytics\AnalyticsController;
use App\Http\Controllers\Compliance\DsarController;
use Illuminate\Support\Facades\Route;

// ── Phase 14: Analytics & Reporting ──────────────────────────────────────────

Route::prefix('analytics')
    ->name('analytics.')
    ->middleware('role:super_admin,superintendent_pharmacist,finance')
    ->group(function () {
        Route::get('/',            [AnalyticsController::class, 'index'])->name('index');
        Route::get('/dispensing',  [AnalyticsController::class, 'dispensing'])->name('dispensing');
        Route::get('/cohorts',     [AnalyticsController::class, 'cohorts'])->name('cohorts');
        Route::get('/nps',         [AnalyticsController::class, 'nps'])->name('nps');

        // Exports
        Route::get('/export/funnel',           [AnalyticsController::class, 'exportFunnel'])->name('export.funnel');
        Route::get('/export/prescriber-rates', [AnalyticsController::class, 'exportPrescriberRates'])->name('export.prescriber-rates');
        Route::get('/export/nps',              [AnalyticsController::class, 'exportNps'])->name('export.nps');
    });

// ── Phase 15: DSAR & Erasure (extends Compliance module) ─────────────────────

Route::prefix('compliance/dsar')
    ->name('compliance.dsar.')
    ->middleware('role:super_admin,superintendent_pharmacist')
    ->group(function () {
        Route::get('/',                          [DsarController::class, 'index'])->name('index');
        Route::get('/create',                    [DsarController::class, 'create'])->name('create');
        Route::post('/',                         [DsarController::class, 'store'])->name('store');
        Route::get('/{dsar}',                    [DsarController::class, 'show'])->name('show');
        Route::patch('/{dsar}/status',           [DsarController::class, 'updateStatus'])->name('status');
        Route::post('/{dsar}/export',            [DsarController::class, 'exportData'])->name('export');
        Route::get('/{dsar}/download',           [DsarController::class, 'downloadExport'])->name('download');
        Route::post('/{dsar}/erase',             [DsarController::class, 'erasePatient'])->name('erase')
            ->middleware('role:super_admin'); // erasure: super admin only
    });
