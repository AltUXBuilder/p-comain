<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\StripeClient;
use Stripe\PaymentIntent;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    /**
     * Create a Stripe PaymentIntent for a one-off product purchase.
     */
    public function createOneOffPaymentIntent(
        User        $user,
        Product     $product,
        int         $quantity = 1,
        array       $metadata = []
    ): PaymentIntent {
        $amount = (int) round($product->price_one_off * 100); // pence

        return $this->stripe->paymentIntents->create([
            'amount'               => $amount * $quantity,
            'currency'             => 'gbp',
            'customer'             => $this->getOrCreateStripeCustomer($user),
            'payment_method_types' => ['card'],
            'metadata'             => array_merge([
                'user_id'    => $user->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'type'       => 'one_off',
            ], $metadata),
            'receipt_email' => $user->email,
        ]);
    }

    /**
     * Create or retrieve a Stripe customer for the given user.
     */
    public function getOrCreateStripeCustomer(User $user): string
    {
        if ($user->stripe_id) {
            return $user->stripe_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name'  => $user->full_name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Create a subscription for the user on a given Stripe price.
     */
    public function createSubscription(
        User    $user,
        Product $product,
        string  $stripePriceId,
        string  $planLabel,
        string  $paymentMethodId
    ): \Laravel\Cashier\Subscription {
        // Ensure customer exists in Stripe
        $this->getOrCreateStripeCustomer($user);

        // Attach payment method
        $user->updateDefaultPaymentMethod($paymentMethodId);

        return $user->newSubscription($planLabel, $stripePriceId)->create($paymentMethodId, [
            'email' => $user->email,
        ]);
    }

    /**
     * Build a full Order record from a confirmed PaymentIntent.
     */
    public function createOrderFromPaymentIntent(
        User        $user,
        Product     $product,
        string      $paymentIntentId,
        int         $quantity,
        ?Prescription $prescription,
        array       $deliveryAddress
    ): Order {
        return DB::transaction(function () use ($user, $product, $paymentIntentId, $quantity, $prescription, $deliveryAddress) {

            $unitPrice   = $product->price_one_off;
            $lineTotal   = $unitPrice * $quantity;
            $vatRate     = $product->isPom() ? 0 : 20; // POMs are VAT exempt in UK
            $vatAmount   = round($lineTotal * ($vatRate / 100), 2);
            $total       = $lineTotal + $vatAmount;

            $order = Order::create([
                'user_id'                  => $user->id,
                'prescription_id'          => $prescription?->id,
                'status'                   => 'payment_confirmed',
                'subtotal'                 => $lineTotal,
                'vat_amount'               => $vatAmount,
                'shipping_cost'            => 0,
                'total'                    => $total,
                'stripe_payment_intent_id' => $paymentIntentId,
                'payment_method'           => 'one_off',
                'requires_cold_chain'      => $product->requires_cold_chain,
                'delivery_name'            => $deliveryAddress['name'],
                'delivery_address_line_1'  => $deliveryAddress['address_line_1'],
                'delivery_address_line_2'  => $deliveryAddress['address_line_2'] ?? null,
                'delivery_city'            => $deliveryAddress['city'],
                'delivery_postcode'        => $deliveryAddress['postcode'],
                'delivery_country'         => 'GB',
            ]);

            OrderItem::create([
                'order_id'        => $order->id,
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'product_strength'=> $product->strength,
                'product_form'    => $product->form,
                'quantity'        => $quantity,
                'unit_price'      => $unitPrice,
                'line_total'      => $lineTotal,
                'vat_rate'        => $vatRate,
            ]);

            // Generate invoice
            Invoice::create([
                'user_id'    => $user->id,
                'order_id'   => $order->id,
                'subtotal'   => $lineTotal,
                'vat_amount' => $vatAmount,
                'total'      => $total,
                'status'     => 'paid',
                'issued_at'  => now(),
                'paid_at'    => now(),
            ]);

            return $order;
        });
    }

    /**
     * Retrieve a PaymentIntent from Stripe.
     */
    public function retrievePaymentIntent(string $id): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($id);
    }

    /**
     * Construct a Stripe webhook event from raw payload.
     */
    public function constructWebhookEvent(string $payload, string $sig): \Stripe\Event
    {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $sig,
            config('cashier.webhook.secret')
        );
    }

    /**
     * Cancel a PaymentIntent.
     */
    public function cancelPaymentIntent(string $id): PaymentIntent
    {
        return $this->stripe->paymentIntents->cancel($id);
    }

    /**
     * Issue a full refund for an order.
     */
    public function refundOrder(Order $order): \Stripe\Refund
    {
        return $this->stripe->refunds->create([
            'payment_intent' => $order->stripe_payment_intent_id,
            'metadata'       => ['order_id' => $order->id],
        ]);
    }
}
