<?php

use App\Services\ConsultationDraftService;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\Subscription;
use App\Models\Order;
use App\Models\Consultation;
use App\Models\WorkflowRule;
use App\Jobs\ProcessWorkflowRule;
use App\Services\EmailService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Schedule;

// ── Purge expired draft consultations (every 6 hours) ────────────────────────
Schedule::call(fn() => ConsultationDraftService::purgeExpired())
    ->everySixHours()
    ->name('purge-expired-drafts')
    ->withoutOverlapping();

// ── Subscription renewal reminders (daily 08:00) ─────────────────────────────
Schedule::call(function () {
    $reminderDays = config('pharmacy.subscriptions.renewal_reminder_days', 7);
    $emailService = app(EmailService::class);

    Subscription::where('stripe_status', 'active')
        ->whereNull('renewal_reminder_sent_at')
        ->whereNotNull('ends_at')
        ->whereDate('ends_at', now()->addDays($reminderDays)->toDateString())
        ->with('user')
        ->get()
        ->each(function ($subscription) use ($emailService) {
            $emailService->sendRenewalReminder($subscription->user, $subscription);
            $subscription->update(['renewal_reminder_sent_at' => now()]);
            ProcessWorkflowRule::dispatch('subscription.renewal_due_7_days', ['subscription_id' => $subscription->id]);
        });
})
->dailyAt('08:00')
->name('subscription-renewal-reminders')
->withoutOverlapping();

// ── Stock level alerts (daily 07:00) ─────────────────────────────────────────
Schedule::call(function () {
    Stock::belowMinimum()
        ->where('alert_sent', false)
        ->with('product')
        ->get()
        ->each(function ($stock) {
            ProcessWorkflowRule::dispatch('stock.below_minimum', [
                'product_id'   => $stock->product_id,
                'product_name' => $stock->product->name,
                'quantity'     => $stock->quantity_on_hand,
                'minimum'      => $stock->minimum_threshold,
            ]);
            $stock->update(['alert_sent' => true]);
        });
})
->dailyAt('07:00')
->name('stock-level-alerts')
->withoutOverlapping();

// ── Near-expiry stock alerts (daily 06:00) ────────────────────────────────────
Schedule::call(function () {
    foreach (config('pharmacy.stock.expiry_alert_days', [30, 60, 90]) as $days) {
        StockBatch::expiringSoon($days)
            ->with('product')
            ->get()
            ->each(function ($batch) use ($days) {
                ProcessWorkflowRule::dispatch('stock.expiring_soon', [
                    'batch_id'     => $batch->id,
                    'product_name' => $batch->product->name,
                    'batch_number' => $batch->batch_number,
                    'expiry_date'  => $batch->expiry_date->toDateString(),
                    'days_until'   => $days,
                ]);
            });
    }
})
->dailyAt('06:00')
->name('near-expiry-alerts')
->withoutOverlapping();

// ── Consultation queue escalation (every hour) ────────────────────────────────
Schedule::call(function () {
    $threshold = config('pharmacy.consultation_queue.escalation_hours', 4);

    Consultation::pending()
        ->where('submitted_at', '<', now()->subHours($threshold))
        ->whereNull('reviewed_at')
        ->get()
        ->each(function ($consultation) {
            ProcessWorkflowRule::dispatch('consultation.waiting_too_long', [
                'consultation_id' => $consultation->id,
                'waiting_hours'   => $consultation->submitted_at->diffInHours(now()),
            ]);
        });
})
->hourly()
->name('consultation-queue-escalation')
->withoutOverlapping();

// ── Cold chain dispatch overdue (every 2 hours) ───────────────────────────────
Schedule::call(function () {
    $window = config('pharmacy.cold_chain.dispatch_window_hours', 24);

    Order::coldChain()
        ->where('status', 'processing')
        ->where('created_at', '<', now()->subHours($window))
        ->get()
        ->each(function ($order) {
            ProcessWorkflowRule::dispatch('order.cold_chain_dispatch_overdue', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ]);
        });
})
->everyTwoHours()
->name('cold-chain-dispatch-overdue')
->withoutOverlapping();

// ── Purge used/expired 2FA tokens (daily 03:00) ───────────────────────────────
Schedule::call(function () {
    \App\Models\TwoFactorToken::where('expires_at', '<', now())
        ->orWhere('used', true)
        ->where('created_at', '<', now()->subDays(1))
        ->delete();
})
->dailyAt('03:00')
->name('purge-2fa-tokens');
