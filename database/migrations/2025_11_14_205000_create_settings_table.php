<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string'); // string, integer, boolean, json, text
                $table->string('group')->default('general'); // general, email, seo, security, etc.
                $table->text('description')->nullable();
                $table->boolean('is_public')->default(false); // Can be accessed via public API
                $table->timestamps();
                
                $table->index(['group', 'key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

