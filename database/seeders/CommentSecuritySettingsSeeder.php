<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CommentSecuritySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'comments.security.moderation_enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'comments',
                'description' => 'Require administrative approval for new comments.',
                'is_public' => true,
            ],
            [
                'key' => 'comments.security.banned_words',
                'value' => json_encode(['cialis', 'viagra', 'cryptocurrency', 'casino', 'free money']),
                'type' => 'json',
                'group' => 'comments',
                'description' => 'List of words that will automatically mark a comment as spam.',
                'is_public' => false,
            ],
            [
                'key' => 'comments.security.max_links',
                'value' => 2,
                'type' => 'integer',
                'group' => 'comments',
                'description' => 'Maximum number of links allowed in a comment before it is flagged as spam.',
                'is_public' => true,
            ],
            [
                'key' => 'comments.security.enable_reply',
                'value' => true,
                'type' => 'boolean',
                'group' => 'comments',
                'description' => 'Enable nested replies.',
                'is_public' => true,
            ],
            [
                'key' => 'comments.security.allow_guests',
                'value' => true,
                'type' => 'boolean',
                'group' => 'comments',
                'description' => 'Allow guests to post comments.',
                'is_public' => true,
            ],
            [
                'key' => 'comments.security.guest_captcha',
                'value' => true,
                'type' => 'boolean',
                'group' => 'comments',
                'description' => 'Require Captcha for guest comments.',
                'is_public' => true,
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
