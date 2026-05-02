<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensing_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dispensed_by')->constrained('staff')->restrictOnDelete();
            $table->string('dispensed_by_name'); // snapshot
            // Label fields
            $table->string('patient_name');
            $table->string('medication_name');
            $table->string('medication_strength')->nullable();
            $table->string('medication_form')->nullable();
            $table->text('dosage_instructions');
            $table->date('dispensing_date');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('pharmacy_name')->default('Prescribe & Co');
            $table->string('pharmacy_address')->nullable();
            $table->string('pharmacy_gphc_number')->nullable();
            // PDF
            $table->string('pdf_path')->nullable();
            $table->enum('label_format', ['standard', 'branded'])->default('branded');
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensing_labels');
    }
};
