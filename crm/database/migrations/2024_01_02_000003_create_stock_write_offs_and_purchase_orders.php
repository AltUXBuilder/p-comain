<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_write_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_batch_id')->constrained('stock_batches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('written_off_by')->constrained('staff');
            $table->integer('quantity');
            $table->enum('reason', ['damaged', 'expired', 'returned', 'recalled', 'other']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Purchase orders table
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('created_by')->constrained('staff');
            $table->enum('status', ['draft', 'sent', 'received', 'partial', 'cancelled'])->default('draft');
            $table->date('expected_date')->nullable();
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_write_offs');
    }
};
