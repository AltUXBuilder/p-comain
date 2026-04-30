<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gp_surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ods_code')->nullable()->unique(); // NHS ODS code
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gp_surgeries');
    }
};
