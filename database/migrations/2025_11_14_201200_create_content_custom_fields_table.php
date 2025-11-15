<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('content_custom_fields')) {
            Schema::create('content_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
                $table->foreignId('custom_field_id')->constrained('custom_fields')->onDelete('cascade');
                $table->text('value')->nullable();
                $table->timestamps();
                
                $table->unique(['content_id', 'custom_field_id']);
                $table->index('content_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('content_custom_fields');
    }
};

