<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('custom_fields')) {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->id();
                $table->foreignId('field_group_id')->nullable()->constrained('field_groups')->onDelete('cascade');
                $table->string('name');
                $table->string('slug');
                $table->string('type'); // text, textarea, number, date, select, checkbox, radio, file, image, etc.
                $table->text('label')->nullable();
                $table->text('description')->nullable();
                $table->text('default_value')->nullable();
                $table->json('options')->nullable(); // For select, radio, checkbox
                $table->json('validation_rules')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_searchable')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('field_group_id');
                $table->index('type');
                $table->unique(['field_group_id', 'slug']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
