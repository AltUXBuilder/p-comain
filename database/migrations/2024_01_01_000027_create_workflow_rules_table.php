<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event'); // e.g. consultation.approved, order.dispatched
            $table->json('conditions')->nullable(); // [{field, operator, value}]
            $table->json('actions'); // [{type, config}] e.g. [{type: send_email, config: {template_id}}]
            $table->boolean('active')->default(true);
            $table->boolean('is_system')->default(false); // pre-built rules cannot be deleted
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
    }
};
