<?php

namespace Tests\Feature\Stripe;

use App\Models\Consultation;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_number_increments_sequentially_within_year(): void
    {
        $user     = User::factory()->create(['stripe_id' => 'cus_seq']);
        $baseData = [
            'user_id'                  => $user->id,
            'status'                   => 'payment_confirmed',
            'subtotal'                 => 10.00,
            'vat_amount'               => 0,
            'shipping_cost'            => 0,
            'total'                    => 10.00,
            'payment_method'           => 'one_off',
            'requires_cold_chain'      => false,
            'delivery_name'            => 'Test',
            'delivery_address_line_1'  => '1 St',
            'delivery_city'            => 'London',
            'delivery_postcode'        => 'SW1A1AA',
            'delivery_country'         => 'GB',
        ];

        $order1 = Order::create(array_merge($baseData, ['stripe_payment_intent_id' => 'pi_1']));
        $order2 = Order::create(array_merge($baseData, ['stripe_payment_intent_id' => 'pi_2']));
        $order3 = Order::create(array_merge($baseData, ['stripe_payment_intent_id' => 'pi_3']));

        $year = now()->format('Y');
        $this->assertEquals("ORD-{$year}-00001", $order1->order_number);
        $this->assertEquals("ORD-{$year}-00002", $order2->order_number);
        $this->assertEquals("ORD-{$year}-00003", $order3->order_number);
    }

    /** @test */
    public function order_shows_on_patient_orders_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'stripe_id'         => 'cus_order_display',
        ]);

        Order::create([
            'user_id'                  => $user->id,
            'status'                   => 'dispatched',
            'subtotal'                 => 24.99,
            'vat_amount'               => 0,
            'shipping_cost'            => 0,
            'total'                    => 24.99,
            'stripe_payment_intent_id' => 'pi_display_test',
            'payment_method'           => 'one_off',
            'requires_cold_chain'      => false,
            'delivery_name'            => 'Test User',
            'delivery_address_line_1'  => '1 St',
            'delivery_city'            => 'London',
            'delivery_postcode'        => 'SW1A1AA',
            'delivery_country'         => 'GB',
        ]);

        $this->actingAs($user)
             ->get(route('patient.orders.index'))
             ->assertOk()
             ->assertViewIs('patient.orders')
             ->assertSee('ORD-');
    }

    /** @test */
    public function patient_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create(['stripe_id' => 'cus_owner']);
        $other = User::factory()->create(['email_verified_at' => now(), 'stripe_id' => 'cus_other2']);

        $order = Order::create([
            'user_id'                  => $owner->id,
            'status'                   => 'dispatched',
            'subtotal'                 => 24.99,
            'vat_amount'               => 0,
            'shipping_cost'            => 0,
            'total'                    => 24.99,
            'stripe_payment_intent_id' => 'pi_owner',
            'payment_method'           => 'one_off',
            'requires_cold_chain'      => false,
            'delivery_name'            => 'Owner',
            'delivery_address_line_1'  => '1 St',
            'delivery_city'            => 'London',
            'delivery_postcode'        => 'SW1A1AA',
            'delivery_country'         => 'GB',
        ]);

        $this->actingAs($other)
             ->get(route('patient.orders.show', $order))
             ->assertStatus(403);
    }

    /** @test */
    public function success_page_loads_without_order(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
             ->get(route('checkout.success'))
             ->assertOk()
             ->assertViewIs('checkout.confirmation');
    }

    /** @test */
    public function cancelled_page_loads(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
             ->get(route('checkout.cancelled'))
             ->assertOk()
             ->assertViewIs('checkout.cancelled');
    }
}
