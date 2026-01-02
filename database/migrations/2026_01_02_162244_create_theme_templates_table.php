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
        Schema::create('theme_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('conditions')->nullable(); // e.g. [{"type": "all_posts"}]
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            
            // Layout Data (Block IDs/JSON)
            $table->json('header_data')->nullable(); 
            $table->json('footer_data')->nullable();
            $table->json('body_data')->nullable(); // For Post Type Templates (replaces content area)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_templates');
    }
};
