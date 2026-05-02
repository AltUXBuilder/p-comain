<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->integer('quantity_received');
            $table->integer('quantity_remaining');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->date('received_date');
            $table->foreignId('received_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('cold_chain_maintained')->default(true);
            $table->enum('status', ['active', 'depleted', 'written_off', 'recalled'])->default('active');
            $table->text('write_off_reason')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'status', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
