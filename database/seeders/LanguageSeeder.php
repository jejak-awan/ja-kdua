<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag' => '🇺🇸',
                'is_default' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'id',
                'name' => 'Indonesian',
                'native_name' => 'Bahasa Indonesia',
                'flag' => '🇮🇩',
                'is_default' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->updateOrInsert(
                ['code' => $language['code']],
                $language
            );
        }

        $this->command->info('Languages seeded successfully!');
    }
}
