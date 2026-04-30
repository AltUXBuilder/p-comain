<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Treatment;
use App\Models\TreatmentCategory;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\StripeClient;
use Tests\TestCase;

class StripeServiceTest extends TestCase
{
    use RefreshDatabase;

    // ── createOrderFromPaymentIntent ──────────────────────────────────────────

    /** @test */
    public function creates_order_with_correct_totals_for_pom_product(): void
    {
        $category  = TreatmentCategory::create(['name' => 'ED', 'slug' => 'ed', 'active' => true]);
        $treatment = Treatment::create(['treatment_category_id' => $category->id, 'name' => 'Sildenafil', 'slug' => 'sildenafil', 'active' => true]);
        $product   = Product::create([
            'treatment_id'  => $treatment->id,
            'name'          => 'Sildenafil 50mg',
            'slug'          => 'sildenafil-50mg',
            'form'          => 'tablet',
            'product_type'  => 'POM',
            'price_one_off' => 24.99,
            'active'        => true,
            'in_stock'      => true,
        ]);

        $user = User::factory()->create(['stripe_id' => 'cus_unit_test']);

        $service = $this->app->make(StripeService::class);

        // We need to partially mock only the Stripe API calls
        // createOrderFromPaymentIntent is a pure DB operation so no mock needed
        $order = $service->createOrderFromPaymentIntent(
            $user, $product, 'pi_unit_test', 1, null,
            ['name' => 'Test User', 'address_line_1' => '1 St', 'city' => 'London', 'postcode' => 'SW1A1AA']
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(24.99, (float) $order->subtotal);
        $this->assertEquals(0.00,  (float) $order->vat_amount);  // POM is VAT exempt
        $this->assertEquals(24.99, (float) $order->total);
        $this->assertEquals('payment_confirmed', $order->status);
        $this->assertEquals('pi_unit_test', $order->stripe_payment_intent_id);
    }

    /** @test */
    public function creates_order_with_vat_for_gsl_product(): void
    {
        $category  = TreatmentCategory::create(['name' => 'Digestive', 'slug' => 'digestive', 'active' => true]);
        $treatment = Treatment::create(['treatment_category_id' => $category->id, 'name' => 'Rantitidine', 'slug' => 'rantitidine', 'active' => true]);
        $product   = Product::create([
            'treatment_id'  => $treatment->id,
            'name'          => 'GSL Product',
            'slug'          => 'gsl-test',
            'form'          => 'tablet',
            'product_type'  => 'GSL',
            'price_one_off' => 10.00,
            'active'        => true,
            'in_stock'      => true,
        ]);

        $user    = User::factory()->create(['stripe_id' => 'cus_gsl_test']);
        $service = $this->app->make(StripeService::class);

        $order = $service->createOrderFromPaymentIntent(
            $user, $product, 'pi_gsl_test', 1, null,
            ['name' => 'Test', 'address_line_1' => '1 St', 'city' => 'London', 'postcode' => 'SW1A1AA']
        );

        $this->assertEquals(10.00, (float) $order->subtotal);
        $this->assertEquals(2.00,  (float) $order->vat_amount); // 20% VAT
        $this->assertEquals(12.00, (float) $order->total);
    }

    /** @test */
    public function creates_order_item_with_correct_snapshot_data(): void
    {
        $category  = TreatmentCategory::create(['name' => 'Hair', 'slug' => 'hair', 'active' => true]);
        $treatment = Treatment::create(['treatment_category_id' => $category->id, 'name' => 'Fin', 'slug' => 'fin', 'active' => true]);
        $product   = Product::create([
            'treatment_id'  => $treatment->id,
            'name'          => 'Finasteride 1mg',
            'slug'          => 'fin-1mg',
            'strength'      => '1mg',
            'form'          => 'tablet',
            'product_type'  => 'POM',
            'price_one_off' => 24.99,
            'active'        => true,
            'in_stock'      => true,
        ]);

        $user    = User::factory()->create(['stripe_id' => 'cus_item_test']);
        $service = $this->app->make(StripeService::class);

        $order = $service->createOrderFromPaymentIntent(
            $user, $product, 'pi_item_test', 2, null,
            ['name' => 'Test', 'address_line_1' => '1 St', 'city' => 'London', 'postcode' => 'SW1A1AA']
        );

        $item = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($item);
        $this->assertEquals('Finasteride 1mg', $item->product_name);
        $this->assertEquals('1mg',   $item->product_strength);
        $this->assertEquals('tablet',$item->product_form);
        $this->assertEquals(2,        $item->quantity);
        $this->assertEquals(49.98,   (float) $item->line_total);
    }

    /** @test */
    public function creates_invoice_alongside_order(): void
    {
        $category  = TreatmentCategory::create(['name' => 'Skin', 'slug' => 'skin', 'active' => true]);
        $treatment = Treatment::create(['treatment_category_id' => $category->id, 'name' => 'Tret', 'slug' => 'tret', 'active' => true]);
        $product   = Product::create([
            'treatment_id'  => $treatment->id,
            'name'          => 'Tretinoin',
            'slug'          => 'tret-test',
            'form'          => 'cream',
            'product_type'  => 'POM',
            'price_one_off' => 34.99,
            'active'        => true,
            'in_stock'      => true,
        ]);

        $user    = User::factory()->create(['stripe_id' => 'cus_inv_test']);
        $service = $this->app->make(StripeService::class);

        $order = $service->createOrderFromPaymentIntent(
            $user, $product, 'pi_inv_test', 1, null,
            ['name' => 'Test', 'address_line_1' => '1 St', 'city' => 'London', 'postcode' => 'SW1A1AA']
        );

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'user_id'  => $user->id,
            'status'   => 'paid',
        ]);
    }

    /** @test */
    public function order_number_is_generated_with_correct_prefix(): void
    {
        $category  = TreatmentCategory::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'active' => true]);
        $treatment = Treatment::create(['treatment_category_id' => $category->id, 'name' => 'Test', 'slug' => 'test-t', 'active' => true]);
        $product   = Product::create([
            'treatment_id'  => $treatment->id,
            'name'          => 'Test Product',
            'slug'          => 'test-product',
            'form'          => 'tablet',
            'product_type'  => 'POM',
            'price_one_off' => 10.00,
            'active'        => true,
            'in_stock'      => true,
        ]);

        $user    = User::factory()->create(['stripe_id' => 'cus_ord_num']);
        $service = $this->app->make(StripeService::class);

        $order = $service->createOrderFromPaymentIntent(
            $user, $product, 'pi_ord_num', 1, null,
            ['name' => 'Test', 'address_line_1' => '1 St', 'city' => 'London', 'postcode' => 'SW1A1AA']
        );

        $year = now()->format('Y');
        $this->assertMatchesRegularExpression("/^ORD-{$year}-\d{5}$/", $order->order_number);
    }
}
