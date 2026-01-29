<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add settings for media optimization if they don't exist
        $settings = [
            [
                'key' => 'media_optimize_on_upload',
                'value' => '1',
                'group' => 'media',
                'type' => 'boolean',
                'label' => 'Optimize images on upload',
                'description' => 'Automatically resize and compress images when uploaded.',
            ],
            [
                'key' => 'media_auto_convert_webp',
                'value' => '1',
                'group' => 'media',
                'type' => 'boolean',
                'label' => 'Auto-convert to WebP',
                'description' => 'Convert uploaded images to WebP format for better compression.',
            ],
            [
                'key' => 'media_optimization_quality',
                'value' => '85',
                'group' => 'media',
                'type' => 'number',
                'label' => 'Optimization Quality',
                'description' => 'Image quality for optimization (1-100).',
            ],
            [
                'key' => 'media_max_width',
                'value' => '1920',
                'group' => 'media',
                'type' => 'number',
                'label' => 'Max Width',
                'description' => 'Maximum width for optimized images.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', [
            'media_optimize_on_upload',
            'media_auto_convert_webp',
            'media_optimization_quality',
            'media_max_width'
        ])->delete();
    }
};
