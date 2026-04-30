<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('email_attempted')->nullable(); // capture even for failed logins
            $table->enum('outcome', ['success', 'failed', 'blocked_ip', '2fa_failed', '2fa_required']);
            $table->string('ip_address', 45);
            $table->string('device_fingerprint')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('new_device')->default(false);
            $table->boolean('new_ip')->default(false);
            $table->boolean('suspicious')->default(false);
            $table->string('session_id')->nullable();
            $table->timestamp('created_at');
            $table->index(['staff_id', 'created_at']);
            $table->index(['ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
