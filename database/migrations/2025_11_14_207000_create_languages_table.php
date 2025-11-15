<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique(); // en, id, etc.
                $table->string('name'); // English, Indonesian
                $table->string('native_name')->nullable();
                $table->string('flag')->nullable(); // Flag emoji or icon
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('is_default');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

