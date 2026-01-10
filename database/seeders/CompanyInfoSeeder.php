<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class CompanyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Contact Info
            [
                'key' => 'contact_email',
                'value' => 'hello@janari.com',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Primary contact email address',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+62 812-3456-7890',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Primary contact phone number',
            ],
            [
                'key' => 'contact_address',
                'value' => "123 Innovation Dr.\nTech City, TC 90210",
                'type' => 'text',
                'group' => 'general',
                'description' => 'Physical office address',
            ],

            // Social Links
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/janari',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Twitter profile URL',
            ],
            [
                'key' => 'social_github',
                'value' => 'https://github.com/ja-cms',
                'type' => 'string',
                'group' => 'social',
                'description' => 'GitHub Profile URL',
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/janari',
                'type' => 'string',
                'group' => 'social',
                'description' => 'LinkedIn Profile URL',
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/janari',
                'type' => 'string',
                'group' => 'social',
                'description' => 'Instagram Profile URL',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Company information seeded successfully!');
    }
}
