<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM-Only Migrations
 *
 * Run AFTER the shared website migrations (which create users, products,
 * consultations, prescriptions, orders, subscriptions, messages, questionnaires,
 * questions, etc.).
 *
 * This file covers every CRM-specific table listed in Section 5
 * "CRM-Only Migrations" of the build plan, plus supporting tables
 * built during phases 12-19 and the audit fixes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Staff accounts ────────────────────────────────────────────────────
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', [
                'super_admin',
                'superintendent_pharmacist',
                'prescriber',
                'dispenser',
                'customer_support',
                'finance',
            ]);
            $table->string('gphc_number', 7)->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('two_factor_confirmed')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->string('welcome_token', 64)->nullable()->unique();
            $table->timestamp('welcome_token_expires_at')->nullable();
            $table->timestamp('welcome_completed_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('out_of_office')->default(false);
            $table->foreignId('out_of_office_reassign_to')->nullable()->constrained('staff')->nullOnDelete();
            $table->integer('max_daily_consultations')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index('email');
            $table->index('role');
        });

        // ── Staff password reset tokens ───────────────────────────────────────
        // Already exists in 2024_01_01_000032 — skip if present
        if (! Schema::hasTable('staff_password_reset_tokens')) {
            Schema::create('staff_password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // ── Session logs — every login attempt ───────────────────────────────
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('success')->default(false);
            $table->string('failure_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['staff_id', 'created_at']);
        });

        // ── Audit log — append-only ───────────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('gphc_number', 7)->nullable();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['staff_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });

        // ── Staff notifications ───────────────────────────────────────────────
        Schema::create('staff_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('type');
            $table->text('message');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['staff_id', 'read_at']);
        });

        // ── Clinical notes ────────────────────────────────────────────────────
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->nullOnDelete();
            $table->text('body');
            $table->boolean('internal_only')->default(true);
            $table->timestamps();
            $table->index(['patient_id', 'created_at']);
        });

        // ── Consultation rejections / refusal register ────────────────────────
        Schema::create('consultation_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->cascadeOnDelete();
            $table->foreignId('prescriber_id')->constrained('staff');
            $table->string('gphc_number', 7)->nullable();
            $table->text('reason');
            $table->boolean('patient_notified')->default(false);
            $table->timestamps();
        });

        // ── Workflow rules ────────────────────────────────────────────────────
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event');
            $table->json('conditions')->nullable();
            $table->json('actions');
            $table->boolean('active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->integer('sort_order')->default(100);
            $table->timestamps();
            $table->index(['trigger_event', 'active']);
        });

        // ── Suppliers ─────────────────────────────────────────────────────────
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // ── Stock batches ─────────────────────────────────────────────────────
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->date('received_date')->nullable();
            $table->integer('quantity_received');
            $table->integer('quantity_remaining')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('cold_chain_maintained')->default(true);
            $table->enum('status', ['active', 'depleted', 'written_off', 'recalled'])->default('active');
            $table->timestamps();
            $table->index(['product_id', 'status', 'expiry_date']);
        });

        // ── Stock (aggregate per product) ─────────────────────────────────────
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('minimum_threshold')->default(10);
            $table->boolean('alert_sent')->default(false);
            $table->timestamp('last_reconciled_at')->nullable();
            $table->timestamps();
        });

        // ── GP surgeries directory ────────────────────────────────────────────
        Schema::create('gp_surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('ods_code', 10)->nullable()->unique();
            $table->timestamps();
            $table->fullText('name');
        });

        // ── GP notifications log ──────────────────────────────────────────────
        Schema::create('gp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff');
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->enum('method', ['letter', 'email', 'phone', 'fax', 'other']);
            $table->text('notes');
            $table->timestamp('notified_at');
            $table->timestamps();
        });

        // ── Invoices ──────────────────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('stripe_invoice_id')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'issued', 'paid', 'void'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        // ── Compliance logs (GDPR, MHRA, data breach, age verification) ───────
        Schema::create('compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });

        // ── Documents (prescription PDFs, patient uploads, regulatory vault, PILs) ──
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->foreignId('uploaded_by_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->boolean('is_private')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'type']);
        });

        // ── Saved filter presets (patient search) ─────────────────────────────
        Schema::create('saved_filter_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('name', 100);
            $table->json('filters');
            $table->timestamps();
        });

        // ── Erasure log (right to erasure audit trail) ────────────────────────
        Schema::create('erasure_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id');
            $table->foreignId('handled_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('fields_pseudonymised')->nullable();
            $table->boolean('prescription_records_retained')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('erased_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erasure_log');
        Schema::dropIfExists('saved_filter_presets');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('compliance_logs');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('gp_notifications');
        Schema::dropIfExists('gp_surgeries');
        Schema::dropIfExists('stock');
        Schema::dropIfExists('stock_batches');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('workflow_rules');
        Schema::dropIfExists('consultation_rejections');
        Schema::dropIfExists('clinical_notes');
        Schema::dropIfExists('staff_notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('session_logs');
        Schema::dropIfExists('staff_password_reset_tokens');
        Schema::dropIfExists('staff');
    }
};
