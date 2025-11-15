<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
                $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
                $table->string('title');
                $table->string('url')->nullable();
                $table->string('type')->default('link'); // link, page, category, custom
                $table->unsignedBigInteger('target_id')->nullable(); // ID of page/category if type is page/category
                $table->string('target_type')->nullable(); // Content, Category, etc.
                $table->string('icon')->nullable();
                $table->string('css_class')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('open_in_new_tab')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['menu_id', 'parent_id']);
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
