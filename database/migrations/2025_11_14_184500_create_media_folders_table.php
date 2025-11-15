<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media_folders')) {
            Schema::create('media_folders', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug');
                $table->foreignId('parent_id')->nullable()->constrained('media_folders')->onDelete('cascade');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('parent_id');
                $table->index('slug');
            });
        }

        if (Schema::hasTable('media') && ! Schema::hasColumn('media', 'folder_id')) {
            Schema::table('media', function (Blueprint $table) {
                $table->foreignId('folder_id')->nullable()->after('id')->constrained('media_folders')->onDelete('set null');
                $table->index('folder_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropColumn('folder_id');
        });

        Schema::dropIfExists('media_folders');
    }
};
