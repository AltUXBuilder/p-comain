<?php

namespace Tests\Feature\Stripe;

use App\Models\Product;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private User    $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = TreatmentCategory::create(['name' => 'Weight Loss', 'slug' => 'weight-loss', 'active' => true]);
        $treatment = Treatment::create([
            'treatment_category_id' => $category->id,
            'name' => 'Mounjaro', 'slug' => 'mounjaro', 'active' => true,
        ]);
        $this->product = Product::create([
            'treatment_id'      => $treatment->id,
            'name'              => 'Mounjaro 2.5mg',
            'slug'              => 'mounjaro-2-5mg',
            'form'              => 'injection pen',
            'product_type'      => 'POM',
            'price_one_off'     => null,
            'subscription_tiers'=> json_encode([
                ['label' => 'Monthly', 'interval' => 'month', 'interval_count' => 1, 'price' => 149.00, 'stripe_price_id' => 'price_monthly_test'],
            ]),
            'active'            => true,
            'in_stock'          => true,
        ]);

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'date_of_birth'     => now()->subYears(30),
            'stripe_id'         => 'cus_sub_lifecycle',
        ]);
    }

    /** @test */
    public function patient_can_view_subscription_management_page(): void
    {
        $this->actingAs($this->user)
             ->get(route('patient.subscriptions.index'))
             ->assertOk()
             ->assertViewIs('patient.subscriptions');
    }

    /** @test */
    public function patient_cannot_manage_another_users_subscription(): void
    {
        $otherUser = User::factory()->create(['stripe_id' => 'cus_other']);

        $sub = \App\Models\Subscription::create([
            'user_id'       => $otherUser->id,
            'product_id'    => $this->product->id,
            'type'          => 'monthly',
            'stripe_id'     => 'sub_other_test',
            'stripe_status' => 'active',
            'stripe_price'  => 'price_monthly_test',
        ]);

        $this->actingAs($this->user)
             ->post(route('patient.subscriptions.cancel', $sub->id))
             ->assertStatus(403);
    }

    /** @test */
    public function subscription_page_shows_empty_state_when_no_subscriptions(): void
    {
        $this->actingAs($this->user)
             ->get(route('patient.subscriptions.index'))
             ->assertOk()
             ->assertSee('No active subscriptions');
    }

    /** @test */
    public function subscription_page_shows_active_subscriptions(): void
    {
        \App\Models\Subscription::create([
            'user_id'       => $this->user->id,
            'product_id'    => $this->product->id,
            'type'          => 'Monthly',
            'stripe_id'     => 'sub_active_display',
            'stripe_status' => 'active',
            'stripe_price'  => 'price_monthly_test',
        ]);

        $this->actingAs($this->user)
             ->get(route('patient.subscriptions.index'))
             ->assertOk()
             ->assertSee('Mounjaro 2.5mg');
    }
}
