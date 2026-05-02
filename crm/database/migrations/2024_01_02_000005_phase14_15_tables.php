<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── NPS scores ────────────────────────────────────────────────────────
        Schema::create('nps_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('score');              // 0–10
            $table->text('comment')->nullable();
            $table->string('category')->nullable();    // detractor / passive / promoter
            $table->timestamp('collected_at');
            $table->timestamps();
            $table->index(['collected_at', 'score']);
        });

        // ── Patient acquisition channel ───────────────────────────────────────
        // Adds to shared users table (non-destructive)
        if (! Schema::hasColumn('users', 'acquisition_channel')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('acquisition_channel')->nullable()->after('gp_surgery_id');
                // e.g. 'organic_search', 'paid_search', 'social', 'referral', 'direct', 'email'
            });
        }

        // ── DSAR requests ─────────────────────────────────────────────────────
        Schema::create('dsar_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->enum('type', ['subject_access', 'erasure', 'rectification', 'portability', 'objection']);
            $table->enum('status', ['received', 'in_progress', 'completed', 'rejected'])->default('received');
            $table->string('requestor_email');         // may differ from account email
            $table->text('notes')->nullable();
            $table->timestamp('due_at');               // GDPR: 30 days from receipt
            $table->timestamp('completed_at')->nullable();
            $table->json('exported_data_path')->nullable(); // path(s) to exported ZIP
            $table->timestamps();
        });

        // ── Right to erasure log ──────────────────────────────────────────────
        Schema::create('erasure_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id'); // stored after pseudonymisation
            $table->foreignId('handled_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('fields_pseudonymised')->nullable(); // JSON list of nulled fields
            $table->boolean('prescription_records_retained')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('erased_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erasure_log');
        Schema::dropIfExists('dsar_requests');
        Schema::dropIfExists('nps_scores');
    }
};
