<?php

/**
 * Phase 9 route additions — merge into routes/web.php
 *
 * Replaces the stubs:
 *   Route::get('/dispensing/queue', fn () => view('coming-soon', ...))->name('dispensing.queue');
 *   Route::get('/labels',           fn () => view('coming-soon', ...))->name('labels.index');
 */

use App\Http\Controllers\Dispensing\DispensingController;
use App\Http\Controllers\Dispensing\LabelController;
use Illuminate\Support\Facades\Route;

// ── Dispensing — inside auth:staff + EnforceTwoFactor group ──────────────────

Route::prefix('dispensing')
    ->name('dispensing.')
    ->middleware('role:super_admin,superintendent_pharmacist,dispenser')
    ->group(function () {

        Route::get('/queue', [DispensingController::class, 'queue'])->name('queue');

        Route::post('/batch', [DispensingController::class, 'batchDispense'])->name('batch');

        Route::get('/{prescription}/dispense',  [DispensingController::class, 'showDispense'])->name('show');
        Route::post('/{prescription}/dispense', [DispensingController::class, 'dispense'])->name('dispense');

        // Label detail (accessible to dispensers)
        Route::get('/labels/{label}',          [DispensingController::class, 'showLabel'])->name('label');
        Route::get('/labels/{label}/pdf',      [DispensingController::class, 'labelPdf'])->name('label.pdf');
        Route::get('/labels/{label}/download', [DispensingController::class, 'labelDownload'])->name('label.download');
    });

// ── Labels print queue ────────────────────────────────────────────────────────

Route::prefix('labels')
    ->name('labels.')
    ->middleware('role:super_admin,superintendent_pharmacist,dispenser')
    ->group(function () {

        Route::get('/',                         [LabelController::class, 'index'])->name('index');
        Route::post('/mark-printed',            [LabelController::class, 'markPrinted'])->name('mark-printed');
        Route::post('/regenerate/{label}',      [LabelController::class, 'regenerate'])->name('regenerate');
    });
