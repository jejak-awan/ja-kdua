<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('form_fields')) {
            Schema::create('form_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
                $table->string('name');
                $table->string('label');
                $table->string('type'); // text, email, textarea, select, checkbox, radio, file, etc.
                $table->text('placeholder')->nullable();
                $table->text('help_text')->nullable();
                $table->json('options')->nullable(); // For select, radio, checkbox
                $table->json('validation_rules')->nullable();
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index('form_id');
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};

