<?php

namespace Tests\Feature\Stripe;

use App\Models\Consultation;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Stripe\PaymentIntent;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private User    $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed a minimal product hierarchy
        $category = TreatmentCategory::create([
            'name' => 'Weight Loss', 'slug' => 'weight-loss', 'active' => true,
        ]);
        $treatment = Treatment::create([
            'treatment_category_id' => $category->id,
            'name' => 'Orlistat', 'slug' => 'orlistat', 'active' => true,
        ]);
        $this->product = Product::create([
            'treatment_id'       => $treatment->id,
            'name'               => 'Orlistat 120mg',
            'slug'               => 'orlistat-120mg',
            'form'               => 'capsule',
            'product_type'       => 'POM',
            'price_one_off'      => 39.99,
            'stock_quantity'     => 100,
            'in_stock'           => true,
            'active'             => true,
            'requires_cold_chain'=> false,
            'has_questionnaire'  => false,
        ]);

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'date_of_birth'     => now()->subYears(30),
            'address_line_1'    => '1 Test Street',
            'city'              => 'London',
            'postcode'          => 'SW1A1AA',
            'stripe_id'         => 'cus_test_123',
        ]);
    }

    // ── Cart tests ────────────────────────────────────────────────────────────

    /** @test */
    public function guest_is_redirected_to_login_when_accessing_checkout(): void
    {
        $this->get(route('checkout.index', ['product_id' => $this->product->id]))
             ->assertRedirect(route('login'));
    }

    /** @test */
    public function checkout_page_requires_email_verification(): void
    {
        $unverified = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($unverified)
             ->withSession(['product_id' => $this->product->id])
             ->get(route('checkout.index'))
             ->assertRedirect(route('verification.notice'));
    }

    /** @test */
    public function checkout_page_loads_for_non_pom_product_without_consultation(): void
    {
        $this->product->update(['product_type' => 'P']);

        $this->actingAs($this->user)
             ->withSession(['product_id' => $this->product->id])
             ->get(route('checkout.index'))
             ->assertOk()
             ->assertViewIs('checkout.index')
             ->assertViewHas('product');
    }

    /** @test */
    public function pom_checkout_requires_approved_consultation(): void
    {
        $this->actingAs($this->user)
             ->withSession(['product_id' => $this->product->id])
             ->get(route('checkout.index'))
             ->assertRedirect(route('treatments.index'));
    }

    /** @test */
    public function pom_checkout_loads_when_approved_consultation_exists(): void
    {
        $consultation = Consultation::create([
            'user_id'       => $this->user->id,
            'product_id'    => $this->product->id,
            'answers'       => [],
            'status'        => 'approved',
            'submitted_at'  => now(),
        ]);

        $this->actingAs($this->user)
             ->withSession([
                 'product_id'      => $this->product->id,
                 'consultation_id' => $consultation->id,
             ])
             ->get(route('checkout.index'))
             ->assertOk()
             ->assertViewHas('consultation');
    }

    /** @test */
    public function checkout_redirects_to_treatments_when_no_product_in_session(): void
    {
        $this->actingAs($this->user)
             ->get(route('checkout.index'))
             ->assertRedirect(route('treatments.index'));
    }

    // ── PaymentController::initiate tests ─────────────────────────────────────

    /** @test */
    public function initiate_creates_payment_intent_for_one_off(): void
    {
        $mockIntent = Mockery::mock(PaymentIntent::class);
        $mockIntent->client_secret    = 'pi_test_secret_xyz';
        $mockIntent->id               = 'pi_test_123';
        $mockIntent->amount           = 3999;
        $mockIntent->currency         = 'gbp';

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('createOneOffPaymentIntent')
             ->once()
             ->with(
                 Mockery::on(fn($u) => $u->id === $this->user->id),
                 Mockery::on(fn($p) => $p->id === $this->product->id),
                 1,
                 Mockery::type('array')
             )
             ->andReturn($mockIntent);

        $this->app->instance(StripeService::class, $mock);

        $this->actingAs($this->user)
             ->postJson(route('checkout.initiate'), [
                 'product_id'  => $this->product->id,
                 'quantity'    => 1,
                 'plan_type'   => 'one_off',
             ])
             ->assertOk()
             ->assertJsonStructure(['client_secret', 'payment_intent_id', 'amount', 'currency'])
             ->assertJson(['amount' => 3999]);
    }

    /** @test */
    public function initiate_returns_422_if_one_off_not_available(): void
    {
        $this->product->update(['price_one_off' => null]);

        $this->actingAs($this->user)
             ->postJson(route('checkout.initiate'), [
                 'product_id' => $this->product->id,
                 'quantity'   => 1,
                 'plan_type'  => 'one_off',
             ])
             ->assertStatus(422)
             ->assertJsonFragment(['error' => 'One-off purchase is not available for this product.']);
    }

    /** @test */
    public function initiate_requires_authenticated_user(): void
    {
        $this->postJson(route('checkout.initiate'), [
            'product_id' => $this->product->id,
            'quantity'   => 1,
            'plan_type'  => 'one_off',
        ])->assertStatus(401);
    }

    /** @test */
    public function initiate_validates_required_fields(): void
    {
        $this->actingAs($this->user)
             ->postJson(route('checkout.initiate'), [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['product_id', 'quantity', 'plan_type']);
    }

    // ── PaymentController::confirm tests ──────────────────────────────────────

    /** @test */
    public function confirm_creates_order_when_payment_intent_succeeds(): void
    {
        Queue::fake();

        $mockIntent = (object)[
            'status'   => 'succeeded',
            'customer' => $this->user->stripe_id,
        ];

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('retrievePaymentIntent')
             ->once()
             ->with('pi_test_123')
             ->andReturn($mockIntent);

        $mock->shouldReceive('createOrderFromPaymentIntent')
             ->once()
             ->andReturn(Order::create([
                 'user_id'                  => $this->user->id,
                 'status'                   => 'payment_confirmed',
                 'subtotal'                 => 39.99,
                 'vat_amount'               => 0,
                 'shipping_cost'            => 0,
                 'total'                    => 39.99,
                 'stripe_payment_intent_id' => 'pi_test_123',
                 'payment_method'           => 'one_off',
                 'requires_cold_chain'      => false,
                 'delivery_name'            => 'Test User',
                 'delivery_address_line_1'  => '1 Test St',
                 'delivery_city'            => 'London',
                 'delivery_postcode'        => 'SW1A1AA',
                 'delivery_country'         => 'GB',
             ]));

        $this->app->instance(StripeService::class, $mock);

        $this->actingAs($this->user)
             ->postJson(route('checkout.confirm'), [
                 'payment_intent_id' => 'pi_test_123',
                 'product_id'        => $this->product->id,
                 'quantity'          => 1,
                 'delivery_address'  => [
                     'name'          => 'Test User',
                     'address_line_1'=> '1 Test St',
                     'city'          => 'London',
                     'postcode'      => 'SW1A1AA',
                 ],
             ])
             ->assertOk()
             ->assertJsonFragment(['success' => true]);

        Queue::assertPushed(\App\Jobs\ProcessWorkflowRule::class);
    }

    /** @test */
    public function confirm_rejects_payment_intent_not_succeeded(): void
    {
        $mockIntent = (object)[
            'status'   => 'requires_payment_method',
            'customer' => $this->user->stripe_id,
        ];

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('retrievePaymentIntent')->andReturn($mockIntent);
        $this->app->instance(StripeService::class, $mock);

        $this->actingAs($this->user)
             ->postJson(route('checkout.confirm'), [
                 'payment_intent_id' => 'pi_test_pending',
                 'product_id'        => $this->product->id,
                 'quantity'          => 1,
                 'delivery_address'  => [
                     'name'           => 'Test User',
                     'address_line_1' => '1 Test St',
                     'city'           => 'London',
                     'postcode'       => 'SW1A1AA',
                 ],
             ])
             ->assertStatus(422)
             ->assertJsonFragment(['error' => 'Payment has not been completed.']);
    }

    /** @test */
    public function confirm_rejects_mismatched_stripe_customer(): void
    {
        $mockIntent = (object)[
            'status'   => 'succeeded',
            'customer' => 'cus_different_customer',
        ];

        $mock = Mockery::mock(StripeService::class);
        $mock->shouldReceive('retrievePaymentIntent')->andReturn($mockIntent);
        $this->app->instance(StripeService::class, $mock);

        $this->actingAs($this->user)
             ->postJson(route('checkout.confirm'), [
                 'payment_intent_id' => 'pi_test_mismatch',
                 'product_id'        => $this->product->id,
                 'quantity'          => 1,
                 'delivery_address'  => [
                     'name'           => 'Test User',
                     'address_line_1' => '1 Test St',
                     'city'           => 'London',
                     'postcode'       => 'SW1A1AA',
                 ],
             ])
             ->assertStatus(403);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
