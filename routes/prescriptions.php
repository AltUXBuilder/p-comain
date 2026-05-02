<?php

/**
 * Phase 8 route additions — merge into routes/web.php
 *
 * Replaces the stub:
 *   Route::get('/prescriptions', fn () => view('coming-soon', ...))->name('prescriptions.index');
 *
 * With the full prescription module routes below.
 */

use App\Http\Controllers\Prescriptions\PrescriptionController;
use App\Http\Controllers\Prescriptions\SignatureController;
use Illuminate\Support\Facades\Route;

// ── Signature (profile) ──────────────────────────────────────────────────────
// Inside the auth:staff + EnforceTwoFactor group:

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/signature',    [SignatureController::class, 'show'])->name('signature');
    Route::post('/signature',   [SignatureController::class, 'save'])->name('signature.save');
    Route::delete('/signature', [SignatureController::class, 'destroy'])->name('signature.destroy');
});

// ── Prescriptions ─────────────────────────────────────────────────────────────
// Clinical roles + dispenser (send to dispense) — SignatureRequired applied inside

Route::prefix('prescriptions')
    ->name('prescriptions.')
    ->middleware([
        'role:super_admin,superintendent_pharmacist,prescriber,dispenser',
    ])
    ->group(function () {

        // Register (before {prescription} to avoid route collision)
        Route::get('/register',        [PrescriptionController::class, 'register'])->name('register')
            ->middleware('role:super_admin,superintendent_pharmacist,prescriber');
        Route::get('/register/export', [PrescriptionController::class, 'exportRegister'])->name('register.export')
            ->middleware('role:super_admin,superintendent_pharmacist');

        // Batch sign
        Route::post('/batch-sign',     [PrescriptionController::class, 'batchSign'])->name('batch-sign')
            ->middleware('role:super_admin,superintendent_pharmacist,prescriber');

        // Individual prescription CRUD + workflow
        Route::get('/',                         [PrescriptionController::class, 'index'])->name('index');
        Route::get('/{prescription}',           [PrescriptionController::class, 'show'])->name('show');
        Route::get('/{prescription}/edit',      [PrescriptionController::class, 'edit'])->name('edit')
            ->middleware('role:super_admin,superintendent_pharmacist,prescriber');
        Route::put('/{prescription}',           [PrescriptionController::class, 'update'])->name('update')
            ->middleware('role:super_admin,superintendent_pharmacist,prescriber');

        // Status transitions
        Route::post('/{prescription}/submit',          [PrescriptionController::class, 'submit'])->name('submit')
            ->middleware('role:super_admin,superintendent_pharmacist,prescriber');
        Route::post('/{prescription}/sign',            [PrescriptionController::class, 'sign'])->name('sign')
            ->middleware(['role:super_admin,superintendent_pharmacist,prescriber', \App\Http\Middleware\SignatureRequired::class]);
        Route::post('/{prescription}/send-to-dispense',[PrescriptionController::class, 'sendToDispense'])->name('send-to-dispense')
            ->middleware('role:super_admin,superintendent_pharmacist,prescriber,dispenser');
        Route::post('/{prescription}/archive',         [PrescriptionController::class, 'archive'])->name('archive')
            ->middleware('role:super_admin,superintendent_pharmacist');

        // PDF
        Route::get('/{prescription}/pdf',      [PrescriptionController::class, 'pdf'])->name('pdf');
        Route::get('/{prescription}/download', [PrescriptionController::class, 'download'])->name('download');

    });
