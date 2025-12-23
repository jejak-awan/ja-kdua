<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        // Helper function to check if index exists (compatible with both MySQL and SQLite)
        $indexExists = function ($table, $index) use ($connection, $driver) {
            if ($driver === 'mysql') {
                $database = $connection->getDatabaseName();
                $result = $connection->select(
                    "SELECT COUNT(*) as count FROM information_schema.statistics 
                     WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                    [$database, $table, $index]
                );
                return $result[0]->count > 0;
            }
            
            // For SQLite, use hasIndex method
            return Schema::hasIndex($table, $index);
        };

        // Contents table - add composite indexes
        Schema::table('contents', function (Blueprint $table) use ($indexExists) {
            // Composite index for common queries (only if not exists)
            if (!$indexExists('contents', 'contents_status_published_at_index')) {
                $table->index(['status', 'published_at'], 'contents_status_published_at_index');
            }
            if (!$indexExists('contents', 'contents_status_type_index')) {
                $table->index(['status', 'type'], 'contents_status_type_index');
            }
            if (!$indexExists('contents', 'contents_author_status_index')) {
                $table->index(['author_id', 'status'], 'contents_author_status_index');
            }
            if (!$indexExists('contents', 'contents_category_status_index')) {
                $table->index(['category_id', 'status'], 'contents_category_status_index');
            }
            if (!$indexExists('contents', 'contents_created_at_index')) {
                $table->index('created_at', 'contents_created_at_index');
            }
        });

        // Media table - add indexes
        Schema::table('media', function (Blueprint $table) use ($indexExists) {
            if (!$indexExists('media', 'media_mime_type_index')) {
                $table->index('mime_type', 'media_mime_type_index');
            }
            if (!$indexExists('media', 'media_disk_index')) {
                $table->index('disk', 'media_disk_index');
            }
            if (!$indexExists('media', 'media_created_at_index')) {
                $table->index('created_at', 'media_created_at_index');
            }
            if (!$indexExists('media', 'media_mime_created_index')) {
                $table->index(['mime_type', 'created_at'], 'media_mime_created_index');
            }
        });

        // Categories table - add indexes
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('categories', 'categories_slug_index')) {
                    $table->index('slug', 'categories_slug_index');
                }
                if (Schema::hasColumn('categories', 'parent_id') && !$indexExists('categories', 'categories_parent_id_index')) {
                    $table->index('parent_id', 'categories_parent_id_index');
                }
            });
        }

        // Tags table - add indexes
        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('tags', 'tags_slug_index')) {
                    $table->index('slug', 'tags_slug_index');
                }
            });
        }

        // Comments table - add indexes
        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('comments', 'comments_content_status_index')) {
                    $table->index(['content_id', 'status'], 'comments_content_status_index');
                }
                if (!$indexExists('comments', 'comments_status_index')) {
                    $table->index('status', 'comments_status_index');
                }
                if (!$indexExists('comments', 'comments_created_at_index')) {
                    $table->index('created_at', 'comments_created_at_index');
                }
            });
        }

        // Media usage table - add indexes
        if (Schema::hasTable('media_usage')) {
            Schema::table('media_usage', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('media_usage', 'media_usage_media_id_index')) {
                    $table->index('media_id', 'media_usage_media_id_index');
                }
                if (!$indexExists('media_usage', 'media_usage_model_index')) {
                    $table->index(['model_type', 'model_id'], 'media_usage_model_index');
                }
            });
        }

        // Content tag pivot table - add indexes
        if (Schema::hasTable('content_tag')) {
            Schema::table('content_tag', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('content_tag', 'content_tag_composite_index')) {
                    $table->index(['content_id', 'tag_id'], 'content_tag_composite_index');
                }
            });
        }

        // Users table - add indexes
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('users', 'users_email_index')) {
                    $table->index('email', 'users_email_index');
                }
                if (!$indexExists('users', 'users_created_at_index')) {
                    $table->index('created_at', 'users_created_at_index');
                }
            });
        }

        // Activity logs table - add indexes
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('activity_logs', 'activity_logs_subject_index')) {
                    $table->index(['model_type', 'model_id'], 'activity_logs_subject_index');
                }
                if (!$indexExists('activity_logs', 'activity_logs_causer_id_index')) {
                    $table->index('user_id', 'activity_logs_causer_id_index');
                }
                if (!$indexExists('activity_logs', 'activity_logs_created_at_index')) {
                    $table->index('created_at', 'activity_logs_created_at_index');
                }
            });
        }

        // Search indexes table - add indexes
        if (Schema::hasTable('search_indexes')) {
            Schema::table('search_indexes', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('search_indexes', 'search_indexes_type_status_index')) {
                    $table->index(['type', 'status'], 'search_indexes_type_status_index');
                }
                if (!$indexExists('search_indexes', 'search_indexes_created_at_index')) {
                    $table->index('created_at', 'search_indexes_created_at_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex('contents_status_published_at_index');
            $table->dropIndex('contents_status_type_index');
            $table->dropIndex('contents_author_status_index');
            $table->dropIndex('contents_category_status_index');
            $table->dropIndex('contents_created_at_index');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_mime_type_index');
            $table->dropIndex('media_disk_index');
            $table->dropIndex('media_created_at_index');
            $table->dropIndex('media_mime_created_index');
        });

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_slug_index');
                if (Schema::hasColumn('categories', 'parent_id')) {
                    $table->dropIndex('categories_parent_id_index');
                }
            });
        }

        if (Schema::hasTable('tags')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropIndex('tags_slug_index');
            });
        }

        if (Schema::hasTable('comments')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex('comments_content_status_index');
                $table->dropIndex('comments_status_index');
                $table->dropIndex('comments_created_at_index');
            });
        }

        if (Schema::hasTable('media_usage')) {
            Schema::table('media_usage', function (Blueprint $table) {
                $table->dropIndex('media_usage_media_id_index');
                $table->dropIndex('media_usage_model_index');
            });
        }

        if (Schema::hasTable('content_tag')) {
            Schema::table('content_tag', function (Blueprint $table) {
                $table->dropIndex('content_tag_composite_index');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_email_index');
                $table->dropIndex('users_created_at_index');
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex('activity_logs_subject_index');
                $table->dropIndex('activity_logs_causer_id_index');
                $table->dropIndex('activity_logs_created_at_index');
            });
        }

        if (Schema::hasTable('search_indexes')) {
            Schema::table('search_indexes', function (Blueprint $table) {
                $table->dropIndex('search_indexes_type_status_index');
                $table->dropIndex('search_indexes_created_at_index');
            });
        }
    }
};

