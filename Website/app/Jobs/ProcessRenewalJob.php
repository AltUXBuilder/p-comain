<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRenewalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public int $subscriptionId
    ) {}

    public function handle(StripeService $stripeService): void
    {
        $subscription = Subscription::with('user')->find($this->subscriptionId);

        if (! $subscription || $subscription->stripe_status !== 'active') {
            Log::info("ProcessRenewalJob: subscription {$this->subscriptionId} not active, skipping.");
            return;
        }

        $patient = $subscription->user;
        if (! $patient) {
            Log::warning("ProcessRenewalJob: no patient for subscription {$this->subscriptionId}");
            return;
        }

        // Queue renewal reminder email via SendGrid
        \Illuminate\Support\Facades\Mail::to($patient->email)
            ->queue(new \App\Mail\RenewalReminder($patient, $subscription));

        Log::info("ProcessRenewalJob: renewal reminder queued for patient {$patient->id}");
    }
}
