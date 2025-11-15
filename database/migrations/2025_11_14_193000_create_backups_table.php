<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('backups')) {
            Schema::create('backups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('database'); // database, files, full
                $table->string('path');
                $table->string('disk')->default('local');
                $table->bigInteger('size')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
                $table->text('error_message')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index('type');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
