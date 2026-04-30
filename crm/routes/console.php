<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| CRM Scheduled Commands
|--------------------------------------------------------------------------
*/

// Generate repeat prescriptions daily at 06:00
Schedule::command('prescriptions:generate-repeats')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Repeat prescriptions generated successfully.');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Repeat prescription generation failed.');
    });
