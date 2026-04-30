<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // e.g. INV-2024-00001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('stripe_invoice_id')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'issued', 'paid', 'void'])->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
