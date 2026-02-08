<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FoundationSeeder extends Seeder
{
    /**
     * Run the general foundation seeds.
     */
    public function run(): void
    {
        // 1. Roles & Permissions (Spatie)
        $this->seedRolesAndPermissions();

        // 2. System Settings
        $this->seedSettings();

        // 3. Languages
        $this->seedLanguages();

        // 4. Scheduled Tasks
        $this->seedScheduledTasks();

        // 5. Redis Settings
        $this->seedRedisSettings();

        $this->command->info('Foundation seeded successfully!');
    }

    protected function seedRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Content
            'view profile', 'view content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'view pending content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',

            // Media
            'view media', 'upload media', 'edit media', 'delete media',
            'view files', 'upload files', 'edit files', 'delete files', 'manage files',

            // Engagement
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments', 'manage comments',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'view submissions',
            'view newsletter', 'create newsletter', 'edit newsletter', 'delete newsletter',

            // Check Access (Users & Roles)
            'view users', 'create users', 'edit users', 'delete users', 'verify users',
            'view roles', 'create roles', 'edit roles', 'delete roles',

            // Appearance
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view menus', 'create menus', 'edit menus', 'delete menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets',

            // System & Settings
            'view settings', 'manage settings',
            'view plugins', 'install plugins', 'edit plugins', 'delete plugins',
            'view redirects', 'create redirects', 'edit redirects', 'delete redirects',
            'view scheduled tasks', 'manage scheduled tasks',
            'view backups', 'create backups', 'download backups', 'delete backups',
            'view system', 'manage system',

            // Logs & Analytics
            'view logs', 'delete logs',
            'view analytics', 'view activity logs', 'view security logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Standard Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminPermissions = Permission::whereNotIn('name', [
            'manage system', 'view security logs', 'manage backups', 'manage scheduled tasks', 'manage roles', 'delete users',
        ])->get();
        $admin->syncPermissions($adminPermissions);

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'view content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'view pending content',
            'view categories', 'view tags', 'view media', 'upload media', 'view comments', 'approve comments', 'view analytics',
        ]);

        $ispMember = Role::firstOrCreate(['name' => 'ISP Member', 'guard_name' => 'web']);
        $ispMember->syncPermissions([
            'view profile',
            'view content',
        ]);
    }

    protected function seedSettings(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'Ja-CMS Pro', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'Modern SaaS Content Management System', 'group' => 'general', 'type' => 'text'],
            ['key' => 'site_url', 'value' => 'https://jejakawan.com', 'group' => 'general', 'type' => 'string'],
            ['key' => 'admin_email', 'value' => 'admin@jejakawan.com', 'group' => 'general', 'type' => 'string'],
            ['key' => 'items_per_page', 'value' => '20', 'group' => 'general', 'type' => 'integer'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'group' => 'general', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'general', 'type' => 'string'],
            ['key' => 'time_format', 'value' => 'H:i:s', 'group' => 'general', 'type' => 'string'],

            // Email (SMTP)
            ['key' => 'mail_driver', 'value' => 'smtp', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_host', 'value' => 'smtp.mailtrap.io', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_port', 'value' => '2525', 'group' => 'email', 'type' => 'integer'],
            ['key' => 'mail_username', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_password', 'value' => '', 'group' => 'email', 'type' => 'password'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_address', 'value' => 'hello@jejakawan.com', 'group' => 'email', 'type' => 'string'],
            ['key' => 'mail_from_name', 'value' => 'JejakAwan CMS', 'group' => 'email', 'type' => 'string'],

            // SEO
            ['key' => 'meta_title', 'value' => 'JejakAwan CMS - Premium Content Platform', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'meta_description', 'value' => 'Publish your stories with style.', 'group' => 'seo', 'type' => 'text'],
            ['key' => 'meta_keywords', 'value' => 'cms, saas, content, publish', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'google_search_console', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'enable_sitemap', 'value' => '1', 'group' => 'seo', 'type' => 'boolean'],
            ['key' => 'enable_robots_txt', 'value' => '1', 'group' => 'seo', 'type' => 'boolean'],

            // Comments (Discussion)
            ['key' => 'comments.security.enable_reply', 'value' => '1', 'group' => 'comments', 'type' => 'boolean'],
            ['key' => 'comments.security.allow_guests', 'value' => '0', 'group' => 'comments', 'type' => 'boolean'],
            ['key' => 'comments.security.moderation_enabled', 'value' => '1', 'group' => 'comments', 'type' => 'boolean'],
            ['key' => 'comments.security.guest_captcha', 'value' => '1', 'group' => 'comments', 'type' => 'boolean'],
            ['key' => 'comments.security.max_links', 'value' => '2', 'group' => 'comments', 'type' => 'integer'],
            ['key' => 'comments.security.banned_words', 'value' => 'spam, casino, crypto', 'group' => 'comments', 'type' => 'text'],

            // Security
            ['key' => 'enable_registration', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'require_email_verification', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'enable_2fa', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'two_factor_method', 'value' => 'authenticator', 'group' => 'security', 'type' => 'string'],
            ['key' => 'two_factor_enforced_roles', 'value' => '["admin", "super-admin"]', 'group' => 'security', 'type' => 'json'],
            ['key' => 'password_min_length', 'value' => '8', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'password_require_uppercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_lowercase', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_number', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'password_require_symbol', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'session_lifetime', 'value' => '120', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'single_session_enabled', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'max_concurrent_sessions', 'value' => '3', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'login_attempts_limit', 'value' => '5', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'block_duration_minutes', 'value' => '30', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'log_retention_days', 'value' => '90', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'enable_captcha', 'value' => '0', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_method', 'value' => 'slider', 'group' => 'security', 'type' => 'string'],
            ['key' => 'captcha_on_login', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],
            ['key' => 'captcha_on_register', 'value' => '1', 'group' => 'security', 'type' => 'boolean'],

            // Performance
            ['key' => 'enable_cache', 'value' => '1', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'cache_driver', 'value' => 'file', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cache_ttl', 'value' => '3600', 'group' => 'performance', 'type' => 'integer'],
            ['key' => 'enable_cdn', 'value' => '0', 'group' => 'performance', 'type' => 'boolean'],
            ['key' => 'cdn_url', 'value' => '', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cdn_preset', 'value' => 'custom', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cdn_included_dirs', 'value' => 'assets, storage', 'group' => 'performance', 'type' => 'string'],
            ['key' => 'cdn_excluded_extensions', 'value' => '.php, .json', 'group' => 'performance', 'type' => 'string'],

            // Media
            ['key' => 'max_upload_size', 'value' => '10240', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'allowed_image_types', 'value' => 'jpg,jpeg,png,gif,webp,svg', 'group' => 'media', 'type' => 'string'],
            ['key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,zip,rar', 'group' => 'media', 'type' => 'string'],
            ['key' => 'storage_driver', 'value' => 'local', 'group' => 'media', 'type' => 'string'],
            ['key' => 'thumbnail_width', 'value' => '300', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'thumbnail_height', 'value' => '300', 'group' => 'media', 'type' => 'integer'],
            ['key' => 'enable_watermark', 'value' => '0', 'group' => 'media', 'type' => 'boolean'],
            ['key' => 'watermark_text', 'value' => 'JejakAwan', 'group' => 'media', 'type' => 'string'],

            // AI
            ['key' => 'ai_enabled', 'value' => '1', 'group' => 'ai', 'type' => 'boolean'],
            ['key' => 'ai_default_provider', 'value' => 'gemini', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'gemini_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'gemini_model', 'value' => 'gemini-pro', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'openai_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'openai_model', 'value' => 'gpt-4-turbo', 'group' => 'ai', 'type' => 'string'],
            ['key' => 'deepseek_api_key', 'value' => '', 'group' => 'ai', 'type' => 'password'],
            ['key' => 'deepseek_model', 'value' => 'deepseek-chat', 'group' => 'ai', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    protected function seedLanguages(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸', 'is_default' => 0, 'is_active' => 1],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'is_default' => 1, 'is_active' => 1],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->updateOrInsert(['code' => $language['code']], $language);
        }
    }

    protected function seedScheduledTasks(): void
    {
        $this->call(ScheduledTaskSeeder::class);
    }

    protected function seedRedisSettings(): void
    {
        $settings = [
            [
                'key' => 'redis_host',
                'value' => env('REDIS_HOST', '127.0.0.1'),
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis server host address',
                'is_encrypted' => false,
            ],
            [
                'key' => 'redis_port',
                'value' => env('REDIS_PORT', '6379'),
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis server port',
                'is_encrypted' => false,
            ],
            [
                'key' => 'redis_password',
                'value' => env('REDIS_PASSWORD', ''),
                'type' => 'string',
                'group' => 'connection',
                'description' => 'Redis server password',
                'is_encrypted' => true,
            ],
            [
                'key' => 'redis_database',
                'value' => env('REDIS_DB', '0'),
                'type' => 'integer',
                'group' => 'connection',
                'description' => 'Redis database index (0-15)',
                'is_encrypted' => false,
            ],
            [
                'key' => 'cache_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'cache',
                'description' => 'Enable Redis for caching',
                'is_encrypted' => false,
            ],
            [
                'key' => 'cache_prefix',
                'value' => 'ja_cms_cache',
                'type' => 'string',
                'group' => 'cache',
                'description' => 'Cache key prefix',
                'is_encrypted' => false,
            ],
            [
                'key' => 'session_enabled',
                'value' => env('SESSION_DRIVER') === 'redis' ? 'true' : 'false',
                'type' => 'boolean',
                'group' => 'session',
                'description' => 'Use Redis for sessions',
                'is_encrypted' => false,
            ],
            [
                'key' => 'queue_enabled',
                'value' => env('QUEUE_CONNECTION') === 'redis' ? 'true' : 'false',
                'type' => 'boolean',
                'group' => 'queue',
                'description' => 'Use Redis for queue jobs',
                'is_encrypted' => false,
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\RedisSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
