<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('type'); // e.g. consultation_waiting, stock_low, cold_chain_alert
            $table->string('title');
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['staff_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_notifications');
    }
};
