<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique(); // e.g. PAND-2024-00001
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescriber_id')->constrained('staff')->restrictOnDelete();
            // Prescriber details snapshot (for PDF — in case staff record changes)
            $table->string('prescriber_name');
            $table->string('prescriber_gphc_number', 7);
            $table->string('prescriber_role');
            // Prescription details
            $table->integer('quantity')->default(1);
            $table->text('dosage_instructions');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            // Status workflow: draft → pending_review → approved → sent_to_dispense → dispensed → archived
            $table->enum('status', [
                'draft',
                'pending_review',
                'approved',
                'sent_to_dispense',
                'dispensed',
                'archived',
                'cancelled',
            ])->default('draft');
            // Signature
            $table->string('signature_path')->nullable(); // path to PNG used on this PDF
            $table->boolean('signature_overridden')->default(false); // true if re-drawn at sign-off
            $table->timestamp('signed_at')->nullable();
            // PDF
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            // Repeat
            $table->boolean('is_repeat')->default(false);
            $table->foreignId('original_prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->date('next_repeat_due')->nullable();
            $table->integer('repeat_interval_days')->nullable();
            // Pharmacy details snapshot
            $table->string('pharmacy_name')->default('Prescribe & Co');
            $table->string('pharmacy_address')->nullable();
            $table->string('pharmacy_gphc_number')->nullable();
            // Audit
            $table->string('approved_by_name')->nullable();
            $table->string('approved_by_gphc')->nullable();
            $table->string('approved_by_ip', 45)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
            $table->index(['prescriber_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
