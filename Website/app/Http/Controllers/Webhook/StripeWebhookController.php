<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\EmailService;
use App\Services\AuditService;
use App\Jobs\ProcessWorkflowRule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private EmailService $emailService,
        private AuditService $auditService,
    ) {}

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('cashier.webhook.secret')
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload.', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        }

        Log::info("Stripe webhook received: {$event->type}", ['event_id' => $event->id]);

        match ($event->type) {
            'payment_intent.succeeded'                  => $this->handlePaymentIntentSucceeded($event->data->object),
            'payment_intent.payment_failed'             => $this->handlePaymentIntentFailed($event->data->object),
            'invoice.payment_failed'                    => $this->handleInvoicePaymentFailed($event->data->object),
            'invoice.payment_succeeded'                 => $this->handleInvoicePaymentSucceeded($event->data->object),
            'customer.subscription.updated'             => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted'             => $this->handleSubscriptionDeleted($event->data->object),
            'charge.dispute.created'                    => $this->handleDisputeCreated($event->data->object),
            default                                     => null,
        };

        return response('Webhook handled', 200);
    }

    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        // Order is created synchronously in PaymentController::confirm
        // This webhook handler is a safety net for any missed confirmations
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        if ($order && $order->status === 'pending_payment') {
            $order->update(['status' => 'payment_confirmed']);
            ProcessWorkflowRule::dispatch('order.payment_confirmed', ['order_id' => $order->id]);

            $this->auditService->log(null, 'webhook.payment_intent.succeeded', 'Order', $order->id, [
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);
        }
    }

    private function handlePaymentIntentFailed(object $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        if ($order) {
            $order->update(['status' => 'cancelled']);
        }

        // Notify patient
        if (isset($paymentIntent->metadata->user_id)) {
            $user = User::find($paymentIntent->metadata->user_id);
            if ($user) {
                $this->emailService->sendPaymentFailed($user);
                ProcessWorkflowRule::dispatch('subscription.payment_failed', ['user_id' => $user->id]);
            }
        }
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        if (!$invoice->customer) return;

        $user = User::where('stripe_id', $invoice->customer)->first();
        if ($user) {
            $this->emailService->sendPaymentFailed($user);
            ProcessWorkflowRule::dispatch('subscription.payment_failed', [
                'user_id'     => $user->id,
                'invoice_id'  => $invoice->id,
            ]);
        }
    }

    private function handleInvoicePaymentSucceeded(object $invoice): void
    {
        if (!$invoice->subscription) return;

        // Reset renewal reminder flag so next cycle gets a reminder
        $user = User::where('stripe_id', $invoice->customer)->first();
        if ($user) {
            $user->subscriptions()
                ->where('stripe_id', $invoice->subscription)
                ->update(['renewal_reminder_sent_at' => null]);
        }
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        $user = User::where('stripe_id', $subscription->customer)->first();
        if (!$user) return;

        $localSub = $user->subscriptions()->where('stripe_id', $subscription->id)->first();
        if ($localSub) {
            $localSub->update([
                'stripe_status' => $subscription->status,
                'ends_at'       => $subscription->cancel_at ? \Carbon\Carbon::createFromTimestamp($subscription->cancel_at) : null,
            ]);
        }
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $user = User::where('stripe_id', $subscription->customer)->first();
        if (!$user) return;

        $user->subscriptions()
            ->where('stripe_id', $subscription->id)
            ->update(['stripe_status' => 'canceled', 'ends_at' => now()]);
    }

    private function handleDisputeCreated(object $charge): void
    {
        Log::warning('Stripe dispute created', [
            'charge_id' => $charge->id,
            'amount'    => $charge->amount,
        ]);

        $order = Order::where('stripe_charge_id', $charge->id)->first();
        if ($order) {
            $this->auditService->log(null, 'stripe.dispute.created', 'Order', $order->id, [
                'charge_id' => $charge->id,
                'amount'    => $charge->amount,
            ]);
        }
    }
}
