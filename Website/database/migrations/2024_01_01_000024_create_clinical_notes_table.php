<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // patient
            $table->foreignId('staff_id')->constrained('staff')->restrictOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('internal_only')->default(true); // never visible to patient
            $table->boolean('gp_notified')->default(false);
            $table->timestamp('gp_notified_at')->nullable();
            $table->foreignId('gp_surgery_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
