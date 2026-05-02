<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('type', [
                'multiple_choice_single',
                'multiple_choice_multi',
                'yes_no',
                'free_text',
                'numeric',
                'bmi_calculator',
                'medical_history_checklist',
                'medications_list',
            ]);
            $table->json('options')->nullable(); // for multiple choice
            $table->boolean('mandatory')->default(true);
            $table->integer('sort_order')->default(0);
            // Branching: show this question only if parent_question_id answer matches parent_trigger_value
            $table->foreignId('parent_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->string('parent_trigger_value')->nullable();
            // Contraindication logic
            $table->boolean('has_contraindication')->default(false);
            $table->json('contraindication_rules')->nullable();
            // [{trigger_value, action: "flag"|"reject", message}]
            // Numeric validation
            $table->decimal('numeric_min', 8, 2)->nullable();
            $table->decimal('numeric_max', 8, 2)->nullable();
            // BMI thresholds
            $table->decimal('bmi_min', 5, 2)->nullable();
            $table->decimal('bmi_max', 5, 2)->nullable();
            $table->string('bmi_action')->nullable(); // flag | reject
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
