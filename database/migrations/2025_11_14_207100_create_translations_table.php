<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
                $table->string('translatable_type'); // Content, Category, etc.
                $table->unsignedBigInteger('translatable_id');
                $table->string('field'); // title, body, description, etc.
                $table->text('value');
                $table->timestamps();
                
                $table->index(['translatable_type', 'translatable_id']);
                $table->index('language_id');
                $table->unique(['language_id', 'translatable_type', 'translatable_id', 'field']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};

