<?php

namespace Database\Seeders;

use App\Models\BuilderPreset;
use Illuminate\Database\Seeder;

class BuilderPresetSeeder extends Seeder
{
    /**
     * Seed the default system presets.
     */
    public function run(): void
    {
        $presets = [
            // Section Presets
            [
                'type' => 'section',
                'name' => 'White Clean',
                'settings' => [
                    'bgType' => 'color',
                    'bgColor' => '#ffffff',
                    'padding' => ['top' => '80', 'bottom' => '80', 'left' => '0', 'right' => '0']
                ],
                'is_system' => true,
            ],
            [
                'type' => 'section',
                'name' => 'Light Gray',
                'settings' => [
                    'bgType' => 'color',
                    'bgColor' => '#f9fafb',
                    'padding' => ['top' => '60', 'bottom' => '60', 'left' => '0', 'right' => '0']
                ],
                'is_system' => true,
            ],
            [
                'type' => 'section',
                'name' => 'Dark Impact',
                'settings' => [
                    'bgType' => 'color',
                    'bgColor' => '#111827',
                    'textColor' => '#ffffff',
                    'padding' => ['top' => '100', 'bottom' => '100', 'left' => '0', 'right' => '0']
                ],
                'is_system' => true,
            ],
            
            // Row Presets
            [
                'type' => 'row',
                'name' => 'Standard Width',
                'settings' => ['maxWidth' => '1280px'],
                'is_system' => true,
            ],
            [
                'type' => 'row',
                'name' => 'Narrow Reading',
                'settings' => ['maxWidth' => '800px'],
                'is_system' => true,
            ],
            [
                'type' => 'row',
                'name' => 'Full Width',
                'settings' => ['maxWidth' => '100%'],
                'is_system' => true,
            ],
            
            // Button Presets
            [
                'type' => 'button',
                'name' => 'Primary Rounded',
                'settings' => [
                    'bgType' => 'color',
                    'bgColor' => '#2059ea',
                    'textColor' => '#ffffff',
                    'borderStyle' => 'none',
                    'borderRadius' => 99
                ],
                'is_system' => true,
            ],
            [
                'type' => 'button',
                'name' => 'Modern Sharp',
                'settings' => [
                    'bgType' => 'color',
                    'bgColor' => '#111827',
                    'textColor' => '#ffffff',
                    'borderStyle' => 'none',
                    'borderRadius' => 4
                ],
                'is_system' => true,
            ],
            [
                'type' => 'button',
                'name' => 'Outline Primary',
                'settings' => [
                    'bgType' => 'none',
                    'borderStyle' => 'solid',
                    'borderWidth' => 2,
                    'borderColor' => '#2059ea',
                    'textColor' => '#2059ea',
                    'borderRadius' => 6
                ],
                'is_system' => true,
            ],
            
            // Text/Heading Presets
            [
                'type' => 'text',
                'name' => 'Clean Body',
                'settings' => [
                    'fontSize' => 16,
                    'lineHeight' => 1.6,
                    'textColor' => '#4b5563'
                ],
                'is_system' => true,
            ],
            [
                'type' => 'text',
                'name' => 'Muted Small',
                'settings' => [
                    'fontSize' => 14,
                    'lineHeight' => 1.4,
                    'textColor' => '#9ca3af'
                ],
                'is_system' => true,
            ],
            [
                'type' => 'heading',
                'name' => 'Lead Text',
                'settings' => [
                    'fontSize' => 20,
                    'lineHeight' => 1.5,
                    'fontWeight' => '500',
                    'textColor' => '#374151'
                ],
                'is_system' => true,
            ],
        ];

        foreach ($presets as $preset) {
            BuilderPreset::updateOrCreate(
                ['type' => $preset['type'], 'name' => $preset['name'], 'is_system' => true],
                $preset
            );
        }
    }
}
