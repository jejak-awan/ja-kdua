<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('form_submissions')) {
            Schema::create('form_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->json('data'); // Field values
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->enum('status', ['new', 'read', 'archived'])->default('new');
                $table->timestamps();

                $table->index(['form_id', 'created_at']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
