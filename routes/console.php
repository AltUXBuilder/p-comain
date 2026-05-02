<?php

/**
 * routes/console.php — complete scheduler for both website and CRM apps.
 *
 * Covers every task specified in Section 11 of the build plan, plus
 * all per-phase scheduler additions from phases 9, 12, 14.
 */

use Illuminate\Support\Facades\Schedule;

// ── Every 48 hours: purge expired draft consultations ────────────────────────
Schedule::command('model:prune', ['--model' => \App\Models\DraftConsultation::class])
    ->everyTwoHours()
    ->name('prune-draft-consultations')
    ->withoutOverlapping();

// Explicit purge in case model:prune is not configured
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('draft_consultations')
        ->where('expires_at', '<', now())
        ->delete();
})->everyTwoHours()->name('purge-expired-drafts');

// ── Daily 08:00 — subscription renewals due in 7 days ────────────────────────
Schedule::call(function () {
    $renewalDate = now()->addDays(7)->startOfDay();

    \App\Models\Patient::whereHas('subscriptions', function ($q) use ($renewalDate) {
        $q->where('stripe_status', 'active')
          ->where(function ($q2) use ($renewalDate) {
              $q2->whereDate('trial_ends_at', $renewalDate)
                 ->orWhereDate('ends_at', $renewalDate);
          });
    })->each(function ($patient) use ($renewalDate) {
        app(\App\Services\WorkflowEngine::class)
            ->fire('subscription.renewal_due', $patient, ['renewal_date' => $renewalDate]);
    });
})->dailyAt('08:00')->name('subscription-renewal-reminders')->withoutOverlapping();

// ── Daily 07:00 — stock level threshold checks ───────────────────────────────
Schedule::call(function () {
    \App\Models\Stock::with('product')
        ->whereRaw('quantity_on_hand <= minimum_threshold')
        ->where('alert_sent', false)
        ->get()
        ->each(function ($stock) {
            app(\App\Services\StockService::class)
                ->checkThreshold($stock->product_id);
        });
})->dailyAt('07:00')->name('stock-threshold-check')->withoutOverlapping();

// ── Daily 06:00 — stock expiry alerts (30/60/90d) ────────────────────────────
Schedule::call(function () {
    $service = app(\App\Services\StockService::class);

    foreach ([30, 60, 90] as $days) {
        $batches = $service->nearExpiryReport($days);

        if ($batches->isEmpty()) continue;

        // Notify superintendent pharmacist and super admin
        \App\Models\Staff::whereIn('role', ['super_admin', 'superintendent_pharmacist'])
            ->where('active', true)
            ->each(function ($staff) use ($batches, $days) {
                \App\Models\StaffNotification::create([
                    'staff_id'    => $staff->id,
                    'type'        => 'stock_expiry',
                    'message'     => "Stock expiry alert: {$batches->count()} batch(es) expiring within {$days} days.",
                    'entity_type' => 'stock',
                    'entity_id'   => null,
                ]);
            });
    }
})->dailyAt('06:00')->name('stock-expiry-check')->withoutOverlapping();

// ── Daily 06:00 — repeat prescriptions due ───────────────────────────────────
Schedule::command('prescriptions:generate-repeats')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground();

// ── Every hour — consultations awaiting review over threshold ────────────────
Schedule::call(function () {
    $threshold = config('crm.consultation_review_threshold_hours', 24);

    $overdue = \App\Models\Consultation::where('status', 'awaiting_review')
        ->where('created_at', '<=', now()->subHours($threshold))
        ->get();

    if ($overdue->isEmpty()) return;

    \App\Models\Staff::whereIn('role', ['super_admin', 'superintendent_pharmacist'])
        ->where('active', true)
        ->each(function ($staff) use ($overdue, $threshold) {
            \App\Models\StaffNotification::create([
                'staff_id'    => $staff->id,
                'type'        => 'consultation_overdue',
                'message'     => "{$overdue->count()} consultation(s) have been awaiting review for over {$threshold} hours.",
                'entity_type' => 'consultation',
                'entity_id'   => null,
            ]);
        });
})->hourly()->name('consultation-review-escalation')->withoutOverlapping();

// ── Daily 09:00 — cold chain dispatch overdue ────────────────────────────────
Schedule::command('workflow:check-cold-chain-overdue')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();
