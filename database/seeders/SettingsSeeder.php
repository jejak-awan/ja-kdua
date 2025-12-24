<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'JA CMS',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Site name displayed throughout the application',
            ],
            [
                'key' => 'site_description',
                'value' => 'A powerful content management system',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Site description for SEO and meta tags',
            ],
            [
                'key' => 'site_url',
                'value' => config('app.url'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Base URL of the website',
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@example.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Administrator email address',
            ],
            [
                'key' => 'timezone',
                'value' => config('app.timezone', 'UTC'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application',
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Date format used throughout the application',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Time format used throughout the application',
            ],
            [
                'key' => 'items_per_page',
                'value' => '15',
                'type' => 'integer',
                'group' => 'general',
                'description' => 'Number of items to display per page',
            ],

            // Email Settings
            [
                'key' => 'mail_driver',
                'value' => 'smtp',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Mail driver (smtp, sendmail, mailgun, etc.)',
            ],
            [
                'key' => 'mail_host',
                'value' => 'smtp.mailtrap.io',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP server host',
            ],
            [
                'key' => 'mail_port',
                'value' => '2525',
                'type' => 'integer',
                'group' => 'email',
                'description' => 'SMTP server port',
            ],
            [
                'key' => 'mail_username',
                'value' => '',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP username',
            ],
            [
                'key' => 'mail_password',
                'value' => '',
                'type' => 'string',
                'group' => 'email',
                'description' => 'SMTP password',
            ],
            [
                'key' => 'mail_encryption',
                'value' => 'tls',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Mail encryption (tls, ssl, or null)',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@example.com',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default from email address',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'JA CMS',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Default from name',
            ],

            // SEO Settings
            [
                'key' => 'meta_title',
                'value' => 'JA CMS - Content Management System',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta title for pages',
            ],
            [
                'key' => 'meta_description',
                'value' => 'A powerful content management system',
                'type' => 'text',
                'group' => 'seo',
                'description' => 'Default meta description for pages',
            ],
            [
                'key' => 'meta_keywords',
                'value' => 'cms, content management, blog',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Default meta keywords',
            ],
            [
                'key' => 'google_analytics_id',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Analytics tracking ID',
            ],
            [
                'key' => 'google_search_console',
                'value' => '',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Google Search Console verification code',
            ],
            [
                'key' => 'enable_sitemap',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'seo',
                'description' => 'Enable automatic sitemap generation',
            ],
            [
                'key' => 'enable_robots_txt',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'seo',
                'description' => 'Enable robots.txt file',
            ],

            // Security Settings
            [
                'key' => 'enable_registration',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Allow new user registration',
            ],
            [
                'key' => 'require_email_verification',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Require email verification for new users',
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Minimum password length',
            ],
            [
                'key' => 'login_attempts_limit',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Maximum failed login attempts before blocking',
            ],
            [
                'key' => 'block_duration_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Duration in minutes to block IP after failed attempts',
            ],
            [
                'key' => 'enable_2fa',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Enable two-factor authentication',
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Session lifetime in minutes',
            ],
            [
                'key' => 'single_session_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'security',
                'description' => 'Restrict users to single concurrent login session',
            ],
            [
                'key' => 'max_concurrent_sessions',
                'value' => '0',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Maximum concurrent sessions per user (0 = unlimited)',
            ],
            [
                'key' => 'log_retention_days',
                'value' => '90',
                'type' => 'integer',
                'group' => 'security',
                'description' => 'Days to retain activity and security logs (0 = keep forever)',
            ],

            // Performance Settings
            [
                'key' => 'enable_cache',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'performance',
                'description' => 'Enable application caching',
            ],
            [
                'key' => 'cache_driver',
                'value' => 'file',
                'type' => 'string',
                'group' => 'performance',
                'description' => 'Cache driver (file, redis, memcached)',
            ],
            [
                'key' => 'cache_ttl',
                'value' => '3600',
                'type' => 'integer',
                'group' => 'performance',
                'description' => 'Cache time to live in seconds',
            ],
            [
                'key' => 'enable_image_optimization',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'performance',
                'description' => 'Enable automatic image optimization',
            ],
            [
                'key' => 'enable_lazy_loading',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'performance',
                'description' => 'Enable lazy loading for images',
            ],
            [
                'key' => 'enable_cdn',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'performance',
                'description' => 'Enable CDN for static assets',
            ],
            [
                'key' => 'cdn_url',
                'value' => '',
                'type' => 'string',
                'group' => 'performance',
                'description' => 'CDN URL for static assets',
            ],

            // Media Settings
            [
                'key' => 'max_upload_size',
                'value' => '10240',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Maximum file upload size in KB',
            ],
            [
                'key' => 'allowed_image_types',
                'value' => 'jpg,jpeg,png,gif,webp',
                'type' => 'string',
                'group' => 'media',
                'description' => 'Allowed image file types (comma-separated)',
            ],
            [
                'key' => 'allowed_file_types',
                'value' => 'pdf,doc,docx,xls,xlsx,zip',
                'type' => 'string',
                'group' => 'media',
                'description' => 'Allowed file types (comma-separated)',
            ],
            [
                'key' => 'thumbnail_width',
                'value' => '300',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Default thumbnail width in pixels',
            ],
            [
                'key' => 'thumbnail_height',
                'value' => '300',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Default thumbnail height in pixels',
            ],
            [
                'key' => 'enable_watermark',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'media',
                'description' => 'Enable watermark on uploaded images',
            ],
            [
                'key' => 'watermark_text',
                'value' => '',
                'type' => 'string',
                'group' => 'media',
                'description' => 'Watermark text',
            ],
            [
                'key' => 'storage_driver',
                'value' => 'local',
                'type' => 'string',
                'group' => 'media',
                'description' => 'Storage driver (local, s3, etc.)',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Default settings seeded successfully!');
    }
}
