<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('staff_name'); // snapshot — preserved even if staff deleted
            $table->string('staff_role')->nullable();
            $table->string('staff_gphc_number')->nullable();
            $table->string('action'); // e.g. prescription.approved, consultation.rejected
            $table->string('entity_type')->nullable(); // Prescription, Consultation, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('context')->nullable(); // extra detail
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at'); // no updated_at — append-only
            $table->index(['staff_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
        });

        // Prevent UPDATE and DELETE on this table via DB-level protection
        // Note: also enforced at application layer — AuditService only ever inserts
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
