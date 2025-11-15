<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('subject');
                $table->text('body'); // HTML template
                $table->text('text_body')->nullable(); // Plain text version
                $table->json('variables')->nullable(); // Available variables for this template
                $table->string('category')->default('general'); // general, system, marketing
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('slug');
                $table->index('category');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
