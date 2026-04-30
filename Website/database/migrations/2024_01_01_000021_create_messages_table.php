<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // patient
            // Polymorphic sender: patient (user) or staff
            $table->enum('sender_type', ['patient', 'staff']);
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->boolean('internal_only')->default(false); // staff-only notes
            $table->timestamp('read_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['consultation_id']);
            $table->index(['user_id', 'sender_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
