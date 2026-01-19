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
            [
                'type' => 'section',
                'name' => 'Gradient Hero',
                'settings' => [
                    'bgType' => 'gradient',
                    'bgGradient' => 'linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)',
                    'textColor' => '#ffffff',
                    'padding' => ['top' => '120', 'bottom' => '120', 'left' => '0', 'right' => '0']
                ],
                'is_system' => true,
            ],
            [
                'type' => 'section',
                'name' => 'Soft Shadow Card',
                'settings' => [
                    'bgColor' => '#ffffff',
                    'margin' => ['top' => '40', 'bottom' => '40'],
                    'padding' => ['top' => '40', 'bottom' => '40', 'left' => '40', 'right' => '40'],
                    'borderRadius' => 16,
                    'boxShadow' => '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)'
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
                    'borderRadius' => 99,
                    'padding' => ['top' => '12', 'bottom' => '12', 'left' => '24', 'right' => '24']
                ],
                'is_system' => true,
            ],
            [
                'type' => 'button',
                'name' => 'Glass Primary',
                'settings' => [
                    'bgType' => 'color',
                    'bgColor' => 'rgba(32, 89, 234, 0.1)',
                    'textColor' => '#2059ea',
                    'borderStyle' => 'solid',
                    'borderWidth' => 1,
                    'borderColor' => 'rgba(32, 89, 234, 0.2)',
                    'borderRadius' => 8,
                    'backdropBlur' => 8
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
                'name' => 'Gradient Pop',
                'settings' => [
                    'bgType' => 'gradient',
                    'bgGradient' => 'linear-gradient(45deg, #f43f5e 0%, #fb923c 100%)',
                    'textColor' => '#ffffff',
                    'borderStyle' => 'none',
                    'borderRadius' => 8,
                    'boxShadow' => '0 4px 14px 0 rgba(244, 63, 94, 0.39)'
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
                'type' => 'heading',
                'name' => 'Display Large',
                'settings' => [
                    'fontSize' => 48,
                    'lineHeight' => 1.1,
                    'fontWeight' => '800',
                    'letterSpacing' => '-0.02em',
                    'textColor' => '#111827'
                ],
                'is_system' => true,
            ],
            [
                'type' => 'heading',
                'name' => 'Section Title',
                'settings' => [
                    'fontSize' => 32,
                    'lineHeight' => 1.2,
                    'fontWeight' => '700',
                    'textColor' => '#1f2937',
                    'textAlign' => 'center'
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
