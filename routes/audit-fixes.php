<?php

/**
 * Audit Fix Routes — merge into routes/web.php
 * inside the auth:staff + EnforceTwoFactor group.
 */

use App\Http\Controllers\Clinical\GpNotificationController;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Patients\PatientFilterController;
use App\Http\Controllers\Questionnaire\QuestionnaireController;
use Illuminate\Support\Facades\Route;

// ── Gap 1: Saved filter presets ──────────────────────────────────────────────

Route::middleware('role:super_admin,superintendent_pharmacist,prescriber,dispenser,customer_support,finance')
    ->group(function () {
        Route::post('/patients/presets',         [PatientFilterController::class, 'savePreset'])->name('patients.presets.store');
        Route::delete('/patients/presets/{preset}', [PatientFilterController::class, 'deletePreset'])->name('patients.presets.destroy');
    });

// ── Gap 4: GP surgery ODS search ─────────────────────────────────────────────

Route::get('/gp-surgeries/search', [PatientFilterController::class, 'gpSurgerySearch'])
    ->name('gp-surgeries.search')
    ->middleware('role:super_admin,superintendent_pharmacist,prescriber,customer_support');

// ── Gaps 2 & 3: GP notification log + template letters ───────────────────────

Route::middleware('role:super_admin,superintendent_pharmacist,prescriber')
    ->group(function () {
        Route::post('/patients/{patient}/gp-notifications', [GpNotificationController::class, 'store'])->name('patients.gp-notifications.store');
        Route::get('/patients/{patient}/gp-letter',         [GpNotificationController::class, 'generateLetter'])->name('patients.gp-letter');
    });

// ── Gaps 6, 7, 8: Finance — ARR, dunning, refunds ────────────────────────────

Route::middleware('role:super_admin,finance')->group(function () {
    Route::get('/finance/dunning',              [FinanceController::class, 'dunning'])->name('finance.dunning');
    Route::post('/finance/dunning/{invoice}/retry', [FinanceController::class, 'retryPayment'])->name('finance.dunning.retry');
    Route::get('/finance/refunds',              [FinanceController::class, 'refunds'])->name('finance.refunds');
    Route::post('/finance/refunds/{order}/stripe', [FinanceController::class, 'issueRefund'])->name('finance.refund');
});

// ── Gap 9: Questionnaire Builder ─────────────────────────────────────────────

Route::prefix('questionnaires')
    ->name('questionnaire.')
    ->middleware('role:super_admin,superintendent_pharmacist')
    ->group(function () {
        Route::get('/',                          [QuestionnaireController::class, 'index'])->name('index');
        Route::get('/create',                    [QuestionnaireController::class, 'create'])->name('create');
        Route::post('/',                         [QuestionnaireController::class, 'store'])->name('store');
        Route::get('/{questionnaire}',           [QuestionnaireController::class, 'show'])->name('show');
        Route::put('/{questionnaire}',           [QuestionnaireController::class, 'update'])->name('update');
        Route::delete('/{questionnaire}',        [QuestionnaireController::class, 'destroy'])->name('destroy');
        Route::post('/{questionnaire}/duplicate',[QuestionnaireController::class, 'duplicate'])->name('duplicate');

        // Questions nested under questionnaire
        Route::post('/{questionnaire}/questions',               [QuestionnaireController::class, 'storeQuestion'])->name('questions.store');
        Route::put('/{questionnaire}/questions/{question}',     [QuestionnaireController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/{questionnaire}/questions/{question}',  [QuestionnaireController::class, 'destroyQuestion'])->name('questions.destroy');
        Route::post('/{questionnaire}/questions/reorder',       [QuestionnaireController::class, 'reorderQuestions'])->name('questions.reorder');
    });
