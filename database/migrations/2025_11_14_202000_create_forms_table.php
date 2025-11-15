<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('forms')) {
            Schema::create('forms', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('success_message')->nullable();
                $table->text('redirect_url')->nullable();
                $table->json('settings')->nullable(); // Email notifications, spam protection, etc.
                $table->boolean('is_active')->default(true);
                $table->integer('submission_count')->default(0);
                $table->timestamps();
                
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};

