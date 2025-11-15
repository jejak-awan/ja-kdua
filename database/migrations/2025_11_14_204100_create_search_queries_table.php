<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('search_queries')) {
            Schema::create('search_queries', function (Blueprint $table) {
                $table->id();
                $table->string('query');
                $table->integer('results_count')->default(0);
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('ip_address')->nullable();
                $table->string('user_agent')->nullable();
                $table->json('filters')->nullable(); // Applied filters
                $table->timestamp('searched_at');
                $table->timestamps();
                
                $table->index(['query', 'searched_at']);
                $table->index('searched_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};

