<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_questionnaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'questionnaire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_questionnaire');
    }
};
