<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('date_of_birth');
            // Address
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->default('GB');
            // 2FA (email OTP — triggered only on specific actions)
            $table->boolean('two_factor_enabled')->default(true);
            $table->json('trusted_devices')->nullable(); // [{fingerprint, last_seen, ip}]
            // Compliance
            $table->boolean('gdpr_marketing_consent')->default(false);
            $table->timestamp('gdpr_consent_at')->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            // Status flags
            $table->boolean('do_not_treat')->default(false);
            $table->string('do_not_treat_reason')->nullable();
            $table->boolean('deceased')->default(false);
            $table->timestamp('deceased_at')->nullable();
            $table->boolean('age_verified')->default(false);
            $table->timestamp('age_verified_at')->nullable();
            $table->boolean('identity_verified')->default(false);
            $table->timestamp('identity_verified_at')->nullable();
            // GP Surgery
            $table->foreignId('gp_surgery_id')->nullable()->constrained('gp_surgeries')->nullOnDelete();
            // Stripe
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
