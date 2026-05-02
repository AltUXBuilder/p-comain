<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password')->nullable(); // set via welcome email link
            $table->enum('role', [
                'super_admin',
                'superintendent_pharmacist',
                'prescriber',
                'dispenser',
                'customer_support',
                'finance',
            ]);
            // GPhC — mandatory for prescriber and superintendent_pharmacist
            $table->string('gphc_number', 7)->nullable();
            // 2FA — TOTP enforced for all staff
            $table->string('two_factor_secret')->nullable(); // encrypted TOTP secret
            $table->boolean('two_factor_confirmed')->default(false);
            $table->json('two_factor_recovery_codes')->nullable();
            // Signature (canvas PNG path)
            $table->string('signature_path')->nullable();
            $table->timestamp('signature_set_at')->nullable();
            // Account status
            $table->boolean('active')->default(false); // activated after 2FA enrolment
            $table->boolean('out_of_office')->default(false);
            $table->foreignId('out_of_office_reassign_to')->nullable()->constrained('staff')->nullOnDelete();
            // Welcome email
            $table->string('welcome_token')->nullable();
            $table->timestamp('welcome_token_expires_at')->nullable();
            $table->timestamp('welcome_completed_at')->nullable();
            // Workload cap
            $table->integer('max_daily_consultations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
