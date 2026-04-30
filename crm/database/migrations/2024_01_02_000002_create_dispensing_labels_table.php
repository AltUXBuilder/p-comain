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

            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('dispensed_by')->constrained('staff');
            $table->foreignId('patient_id')->constrained('users');

            // Batch / stock
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('stock_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();

            // Label content snapshot (stored at generation time — immutable record)
            $table->string('patient_name');
            $table->string('medication_name');
            $table->string('medication_strength')->nullable();
            $table->string('medication_form')->nullable();
            $table->text('dosage_instructions');
            $table->date('dispensing_date');
            $table->string('pharmacy_name');
            $table->string('pharmacy_address')->nullable();
            $table->string('pharmacy_gphc_number')->nullable();
            $table->string('dispensed_by_name');          // snapshot, not FK lookup
            $table->string('dispensed_by_gphc')->nullable();

            // Flags
            $table->boolean('cold_chain')->default(false);
            $table->boolean('printed')->default(false);
            $table->timestamp('printed_at')->nullable();

            // Format chosen at print time
            $table->enum('format', ['standard', 'branded'])->default('standard');

            // PDF path (stored in private disk)
            $table->string('pdf_path')->nullable();

            $table->timestamp('dispensed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensing_labels');
    }
};
