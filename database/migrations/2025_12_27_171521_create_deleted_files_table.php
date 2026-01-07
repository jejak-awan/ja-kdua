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
        Schema::create('deleted_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_path'); // Original file/folder path
            $table->string('trash_path'); // Path in trash folder
            $table->string('name'); // File/folder name
            $table->enum('type', ['file', 'folder'])->default('file');
            $table->bigInteger('size')->nullable(); // File size in bytes
            $table->string('extension')->nullable(); // File extension
            $table->string('mime_type')->nullable(); // MIME type
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index('original_path');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_files');
    }
};
