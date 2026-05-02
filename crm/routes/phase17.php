<?php

/**
 * Phase 17 route additions — merge into routes/web.php
 * inside the auth:staff + EnforceTwoFactor + role:super_admin group.
 *
 * Adds the active sessions viewer and session termination endpoints.
 * The staff CRUD routes (index, create, store, show, edit, update, etc.)
 * were already declared in Phase 6. This file only adds the session routes.
 */

use App\Http\Controllers\Staff\ActiveSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff/sessions')
    ->name('staff.sessions.')
    ->middleware([
        'web',
        'auth:staff',
        \App\Http\Middleware\EnforceTwoFactor::class,
        'role:super_admin',
    ])
    ->group(function () {
        Route::get('/',                             [ActiveSessionController::class, 'index'])->name('index');
        Route::post('/{sessionId}/terminate',       [ActiveSessionController::class, 'terminate'])->name('terminate');
        Route::post('/terminate-all/{staff}',       [ActiveSessionController::class, 'terminateAll'])->name('terminate-all');
    });
