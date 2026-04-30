<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescriber_id')->constrained('staff')->restrictOnDelete();
            $table->string('prescriber_gphc_number', 7);
            $table->text('reason');
            $table->enum('rejection_type', ['clinical', 'incomplete', 'contraindication', 'other'])->default('clinical');
            $table->boolean('patient_notified')->default(false);
            $table->timestamp('patient_notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_rejections');
    }
};
