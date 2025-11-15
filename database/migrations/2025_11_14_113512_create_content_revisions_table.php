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
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('body')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('slug');
            $table->json('meta')->nullable();
            $table->string('status')->default('draft');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
    }
};
