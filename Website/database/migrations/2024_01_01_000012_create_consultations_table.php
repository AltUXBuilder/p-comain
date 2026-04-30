<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prescriber_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->json('answers'); // {question_id: answer, ...}
            $table->enum('status', [
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'flagged',
                'awaiting_info',
                'expired',
            ])->default('submitted');
            // Flags
            $table->boolean('contraindication_flagged')->default(false);
            $table->json('contraindication_flags')->nullable(); // [{question_id, value, reason}]
            $table->boolean('high_risk')->default(false);
            // Review
            $table->text('prescriber_notes')->nullable(); // internal only
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            // Reorder
            $table->foreignId('parent_consultation_id')->nullable()->constrained('consultations')->nullOnDelete();
            $table->boolean('is_reorder')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['prescriber_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
