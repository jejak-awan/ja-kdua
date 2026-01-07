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
        $tables = [
            'users',
            'forms',
            'form_submissions',
            'menus',
            'menu_items',
            'content_templates',
            'newsletter_subscribers',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (! Schema::hasColumn($table->getTable(), 'deleted_at')) {
                        $table->softDeletes();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'forms',
            'form_submissions',
            'menus',
            'menu_items',
            'content_templates',
            'newsletter_subscribers',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
