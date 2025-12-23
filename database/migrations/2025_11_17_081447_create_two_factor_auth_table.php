<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('two_factor_auth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->text('secret')->nullable(); // Encrypted secret key
            $table->json('backup_codes')->nullable(); // Array of backup codes (hashed)
            $table->boolean('enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('recovery_codes_generated_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_auth');
    }
};
