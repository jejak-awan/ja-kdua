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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('mega_menu_show_dividers')->default(false)->after('mega_menu_column');
            $table->boolean('show_heading_line')->default(false)->after('mega_menu_show_dividers');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['mega_menu_show_dividers', 'show_heading_line']);
        });
    }
};
