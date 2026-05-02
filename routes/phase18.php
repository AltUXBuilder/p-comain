<?php

/**
 * Phase 18 routes — merge into routes/web.php
 * inside the auth:staff + EnforceTwoFactor group.
 */

use App\Http\Controllers\Documents\DocumentController;
use Illuminate\Support\Facades\Route;

// ── Patient documents (clinical roles + customer support) ─────────────────────

Route::prefix('patients/{patient}/documents')
    ->name('documents.patient.')
    ->middleware('role:super_admin,superintendent_pharmacist,prescriber,customer_support')
    ->group(function () {
        Route::get('/',    [DocumentController::class, 'patientIndex'])->name('index');
        Route::post('/',   [DocumentController::class, 'patientUpload'])->name('upload');
    });

// ── Prescription PDF archive ──────────────────────────────────────────────────

Route::get('/documents/prescriptions', [DocumentController::class, 'prescriptionArchive'])
    ->name('documents.prescriptions')
    ->middleware('role:super_admin,superintendent_pharmacist,prescriber');

// ── Regulatory vault ──────────────────────────────────────────────────────────

Route::prefix('documents/vault')
    ->name('documents.vault.')
    ->middleware('role:super_admin,superintendent_pharmacist')
    ->group(function () {
        Route::get('/',    [DocumentController::class, 'vault'])->name('index');
        Route::post('/',   [DocumentController::class, 'vaultUpload'])->name('upload');
    });

// ── PIL library ───────────────────────────────────────────────────────────────

Route::prefix('documents/pils')
    ->name('documents.pils.')
    ->middleware('role:super_admin,superintendent_pharmacist')
    ->group(function () {
        Route::get('/',                    [DocumentController::class, 'pilLibrary'])->name('index');
        Route::post('/',                   [DocumentController::class, 'pilUpload'])->name('upload');
        Route::post('/{document}/attach',  [DocumentController::class, 'pilAttach'])->name('attach');
    });

// ── Individual document actions (all authenticated clinical staff) ─────────────

Route::prefix('documents')
    ->name('documents.')
    ->middleware('role:super_admin,superintendent_pharmacist,prescriber,dispenser,customer_support')
    ->group(function () {
        Route::get('/{document}/view',     [DocumentController::class, 'view'])->name('view');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::delete('/{document}',       [DocumentController::class, 'destroy'])->name('destroy')
            ->middleware('role:super_admin,superintendent_pharmacist');
    });

// Documents hub index
Route::get('/documents', fn () => view('documents.index'))->name('documents.index');
