<?php

namespace Tests\Feature\Stripe;

use App\Models\Order;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['cashier.webhook.secret' => $this->webhookSecret]);
        Queue::fake();
    }

    /**
     * Helper to build a signed webhook request.
     */
    private function webhookRequest(array $payload): \Illuminate\Testing\TestResponse
    {
        $json      = json_encode($payload);
        $timestamp = time();
        $sig       = hash_hmac('sha256', "{$timestamp}.{$json}", $this->webhookSecret);
        $header    = "t={$timestamp},v1={$sig}";

        return $this->withHeaders(['Stripe-Signature' => $header])
                    ->postJson(route('webhooks.stripe'), $payload);
    }

    /** @test */
    public function webhook_rejects_invalid_signature(): void
    {
        $this->withHeaders(['Stripe-Signature' => 'invalid'])
             ->postJson(route('webhooks.stripe'), ['type' => 'payment_intent.succeeded'])
             ->assertStatus(400);
    }

    /** @test */
    public function payment_intent_succeeded_updates_order_status(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test']);
        $order = Order::create([
            'user_id'                  => $user->id,
            'status'                   => 'pending_payment',
            'subtotal'                 => 39.99,
            'vat_amount'               => 0,
            'shipping_cost'            => 0,
            'total'                    => 39.99,
            'stripe_payment_intent_id' => 'pi_webhook_test',
            'payment_method'           => 'one_off',
            'requires_cold_chain'      => false,
            'delivery_name'            => 'Test',
            'delivery_address_line_1'  => '1 St',
            'delivery_city'            => 'London',
            'delivery_postcode'        => 'SW1A1AA',
            'delivery_country'         => 'GB',
        ]);

        $this->webhookRequest([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id'       => 'pi_webhook_test',
                    'status'   => 'succeeded',
                    'customer' => 'cus_test',
                    'amount'   => 3999,
                    'metadata' => [],
                ]
            ],
        ])->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'payment_confirmed',
        ]);

        Queue::assertPushed(\App\Jobs\ProcessWorkflowRule::class);
    }

    /** @test */
    public function payment_intent_failed_sends_failure_email(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_test_fail']);

        $mockEmail = Mockery::mock(EmailService::class);
        $mockEmail->shouldReceive('sendPaymentFailed')
                  ->once()
                  ->with(Mockery::on(fn($u) => $u->id === $user->id));
        $this->app->instance(EmailService::class, $mockEmail);

        $this->webhookRequest([
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id'       => 'pi_failed_test',
                    'status'   => 'requires_payment_method',
                    'customer' => 'cus_test_fail',
                    'metadata' => ['user_id' => (string) $user->id],
                ]
            ],
        ])->assertStatus(200);
    }

    /** @test */
    public function invoice_payment_failed_triggers_dunning_email(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_invoice_fail']);

        $mockEmail = Mockery::mock(EmailService::class);
        $mockEmail->shouldReceive('sendPaymentFailed')->once();
        $this->app->instance(EmailService::class, $mockEmail);

        $this->webhookRequest([
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'id'           => 'in_test',
                    'customer'     => 'cus_invoice_fail',
                    'subscription' => 'sub_test',
                    'amount_due'   => 4999,
                ]
            ],
        ])->assertStatus(200);

        Queue::assertPushed(\App\Jobs\ProcessWorkflowRule::class);
    }

    /** @test */
    public function invoice_payment_succeeded_resets_renewal_reminder_flag(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_invoice_ok']);

        // Create a subscription with the reminder flag set
        \App\Models\Subscription::create([
            'user_id'                  => $user->id,
            'product_id'               => \App\Models\Product::factory()->create()->id,
            'type'                     => 'monthly',
            'stripe_id'                => 'sub_renewal_test',
            'stripe_status'            => 'active',
            'stripe_price'             => 'price_test',
            'renewal_reminder_sent_at' => now()->subDays(7),
        ]);

        $this->webhookRequest([
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'id'           => 'in_paid_test',
                    'customer'     => 'cus_invoice_ok',
                    'subscription' => 'sub_renewal_test',
                ]
            ],
        ])->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'stripe_id'                => 'sub_renewal_test',
            'renewal_reminder_sent_at' => null,
        ]);
    }

    /** @test */
    public function customer_subscription_deleted_marks_subscription_cancelled(): void
    {
        $user = User::factory()->create(['stripe_id' => 'cus_sub_del']);

        \App\Models\Subscription::create([
            'user_id'       => $user->id,
            'product_id'    => \App\Models\Product::factory()->create()->id,
            'type'          => 'monthly',
            'stripe_id'     => 'sub_to_delete',
            'stripe_status' => 'active',
            'stripe_price'  => 'price_test',
        ]);

        $this->webhookRequest([
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'id'       => 'sub_to_delete',
                    'customer' => 'cus_sub_del',
                    'status'   => 'canceled',
                ]
            ],
        ])->assertStatus(200);

        $this->assertDatabaseHas('subscriptions', [
            'stripe_id'     => 'sub_to_delete',
            'stripe_status' => 'canceled',
        ]);
    }

    /** @test */
    public function unknown_webhook_events_are_handled_gracefully(): void
    {
        $this->webhookRequest([
            'type' => 'radar.early_fraud_warning.created',
            'data' => ['object' => ['id' => 'fraef_test']],
        ])->assertStatus(200);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
