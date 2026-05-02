<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. ORD-2024-00001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'pending_payment',
                'payment_confirmed',
                'processing',
                'dispatched',
                'delivered',
                'returned',
                'cancelled',
                'refunded',
                'failed_delivery',
            ])->default('pending_payment');
            // Pricing snapshot
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('GBP');
            // Payment
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->enum('payment_method', ['one_off', 'subscription'])->default('one_off');
            // Shipping
            $table->enum('carrier', ['royal_mail', 'dpd', 'evri', 'other'])->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->boolean('requires_cold_chain')->default(false);
            // Delivery address snapshot
            $table->string('delivery_name');
            $table->string('delivery_address_line_1');
            $table->string('delivery_address_line_2')->nullable();
            $table->string('delivery_city');
            $table->string('delivery_postcode');
            $table->string('delivery_country', 2)->default('GB');
            // Fulfilment
            $table->foreignId('dispatched_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('fulfilment_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'requires_cold_chain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
