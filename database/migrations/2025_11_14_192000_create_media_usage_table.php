<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('media_usage')) {
            Schema::create('media_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            $table->string('model_type'); // Content, Category, etc.
            $table->unsignedBigInteger('model_id');
            $table->string('field_name')->nullable(); // featured_image, og_image, etc.
            $table->timestamps();
            
            $table->index(['media_id', 'model_type', 'model_id']);
            $table->index('model_type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usage');
    }
};

