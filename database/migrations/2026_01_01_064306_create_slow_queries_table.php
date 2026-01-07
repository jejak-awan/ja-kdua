<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slow_queries', function (Blueprint $table) {
            $table->id();
            $table->text('query');
            $table->json('bindings')->nullable();
            $table->integer('duration'); // milliseconds
            $table->string('route')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();

            $table->index('created_at');
            $table->index('duration');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slow_queries');
    }
};
