<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('form'); // tablet, capsule, injection pen, cream, etc.
            $table->text('dosage_instructions')->nullable(); // pre-fills dispensing label
            $table->text('description')->nullable();
            $table->enum('product_type', ['POM', 'P', 'GSL'])->default('POM');
            // Pricing
            $table->decimal('price_one_off', 10, 2)->nullable();
            $table->json('subscription_tiers')->nullable(); // [{label, interval, price, stripe_price_id}]
            // Stock
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_stock_level')->default(10);
            $table->boolean('in_stock')->default(true);
            // Compliance
            $table->boolean('requires_cold_chain')->default(false);
            $table->boolean('requires_age_verification')->default(false);
            $table->boolean('has_questionnaire')->default(false);
            // Assets
            $table->string('pil_path')->nullable(); // Patient Information Leaflet
            $table->json('images')->nullable();
            // Supplier
            $table->string('supplier_name')->nullable();
            // Status
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
