<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draft_consultations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // stored in patient cookie
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_id')->nullable()->constrained()->nullOnDelete();
            $table->json('answers')->nullable(); // {question_id: answer, ...}
            $table->integer('current_step')->default(0);
            $table->timestamp('expires_at'); // 48 hours from creation
            $table->timestamps();
            $table->index(['uuid']);
            $table->index(['user_id']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_consultations');
    }
};
