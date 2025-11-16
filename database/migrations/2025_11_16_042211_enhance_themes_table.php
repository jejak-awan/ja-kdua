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
        Schema::table('themes', function (Blueprint $table) {
            // Theme type: frontend, admin, email
            $table->string('type')->default('frontend')->after('slug');
            
            // Theme directory path
            $table->string('path')->nullable()->after('slug');
            
            // Parent theme for child themes
            $table->string('parent_theme')->nullable()->after('slug');
            
            // Theme dependencies (required plugins/themes)
            $table->json('dependencies')->nullable()->after('settings');
            
            // Feature support flags
            $table->json('supports')->nullable()->after('settings');
            
            // Theme status: active, inactive, broken
            $table->string('status')->default('active')->after('is_active');
            
            // Last update timestamp
            $table->timestamp('last_updated_at')->nullable()->after('updated_at');
            
            // Update URL for auto-updates
            $table->string('update_url')->nullable()->after('author_url');
            
            // Auto-update enabled
            $table->boolean('auto_update')->default(false)->after('is_active');
            
            // License information
            $table->string('license')->nullable()->after('author_url');
            
            // Minimum CMS version required
            $table->string('requires_cms_version')->nullable()->after('version');
            
            // Indexes for better query performance
            $table->index('type');
            $table->index('status');
            $table->index('parent_theme');
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropIndex(['type', 'is_active']);
            $table->dropIndex(['parent_theme']);
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            
            $table->dropColumn([
                'type',
                'path',
                'parent_theme',
                'dependencies',
                'supports',
                'status',
                'last_updated_at',
                'update_url',
                'auto_update',
                'license',
                'requires_cms_version',
            ]);
        });
    }
};
