<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'gdpr_consent',
                'gdpr_consent_withdrawn',
                'age_verification',
                'identity_verification',
                'data_breach_incident',
                'dsar_request',
                'right_to_erasure',
                'mhra_yellow_card',
                'superintendent_sign_off',
                'gphc_inspection',
            ]);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at');
            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_logs');
    }
};
