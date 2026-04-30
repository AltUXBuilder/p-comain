<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the stub prescriptions table created in Phase 0.
 * Run: php artisan migrate (safe — checks existence first via change())
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {

            // Status workflow: draft → pending_review → approved → sent_to_dispense → dispensed → archived
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'status')) {
                $table->enum('status', [
                    'draft',
                    'pending_review',
                    'approved',
                    'sent_to_dispense',
                    'dispensed',
                    'archived',
                ])->default('draft')->after('product_id');
            }

            // Clinical fields
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'dosage_instructions')) {
                $table->text('dosage_instructions')->nullable()->after('status');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'quantity')) {
                $table->string('quantity')->nullable()->after('dosage_instructions');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'legal_wording')) {
                $table->text('legal_wording')->nullable()->after('quantity');
            }

            // GPhC required fields on PDF
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'prescriber_gphc_number')) {
                $table->string('prescriber_gphc_number', 7)->nullable()->after('prescriber_id');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'prescriber_name')) {
                $table->string('prescriber_name')->nullable()->after('prescriber_gphc_number');
            }

            // Repeat prescription fields
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'is_repeat')) {
                $table->boolean('is_repeat')->default(false)->after('legal_wording');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'repeat_interval_days')) {
                $table->integer('repeat_interval_days')->nullable()->after('is_repeat');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'repeat_parent_id')) {
                $table->foreignId('repeat_parent_id')->nullable()->constrained('prescriptions')->nullOnDelete()->after('repeat_interval_days');
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'next_repeat_due')) {
                $table->date('next_repeat_due')->nullable()->after('repeat_parent_id');
            }

            // Signature override path (when prescriber re-draws for this prescription only)
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'signature_override_path')) {
                $table->string('signature_override_path')->nullable()->after('pdf_path');
            }

            // Workflow timestamps
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'sent_to_dispense_at')) {
                $table->timestamp('sent_to_dispense_at')->nullable();
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'dispensed_at')) {
                $table->timestamp('dispensed_at')->nullable();
            }
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }

            // Notes
            if (! \Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'prescriber_notes')) {
                $table->text('prescriber_notes')->nullable();
            }

        });
    }

    public function down(): void
    {
        // Non-destructive — removing columns not advised on shared DB
    }
};
