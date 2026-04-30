<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 50)->unique(); // IPv4, IPv6, or CIDR
            $table->string('label')->nullable();         // e.g. "Office", "VPN UK"
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_whitelist');
    }
};
