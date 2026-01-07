<?php

namespace Database\Seeders;

use App\Models\ContentTemplate;
use Illuminate\Database\Seeder;

class ModernLayoutTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // 1. SaaS Luxury Landing 2026 (Pro)
            [
                'name' => 'SaaS Luxury Landing 2026',
                'slug' => 'saas-luxury-landing-2026',
                'description' => 'A high-end, dark-themed landing page with gradients, bento grids, and clear CTAs.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'hero-1',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'The Future of AI is Here',
                            'subtitle' => 'Unleash the power of next-gen intelligence with our seamless platform. Built for 2026 and beyond.',
                            'bgImage' => '',
                            'bgColor' => 'bg-slate-950', // Tailwind class directly? No, usually hex or class. Hero block supports classes? Wait, let's use a class if supported or a hex. Hero definition says bgColor is color input. Let's assume transparency and use a container approach if needed. Actually, let's use a dark hex 'bg-slate-950' isn't valid for color input usually. Let's use '#020617'.
                            'bgColor' => '#020617',
                            'padding' => 'py-32',
                            'animation' => 'animate-in fade-in zoom-in-95 duration-1000',
                        ],
                    ],
                    // I need to be careful with JSON structure here.
                    // To follow the plan "bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900", I might need a wrapper section or if I can pass classes.
                    // The Hero block has 'bgColor' (color picker) and 'bgImage'.
                    // It doesn't seem to support arbitrary classes easily unless I use 'Advanced -> CSS Classes'.
                    // Let's use CSS Classes for the gradient!
                    [
                        'id' => 'hero-pro',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'The Future of AI is Here',
                            'subtitle' => 'Unleash the power of next-gen intelligence with our seamless platform.',
                            'padding' => 'py-32',
                            'radius' => 'rounded-3xl',
                            '_css_class' => 'bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 text-white shadow-2xl mx-4',
                            'animation' => 'animate-in fade-in duration-1000',
                        ],
                    ],
                    [
                        'id' => 'feature-bento-1',
                        'type' => 'features',
                        'settings' => [
                            'title' => 'Engineered for Performance',
                            'items' => [
                                ['title' => 'Quantum Speed', 'description' => 'Process millions of data points.', 'icon' => 'zap'],
                                ['title' => 'Neural Security', 'description' => 'Advanced encryption.', 'icon' => 'lock'],
                                ['title' => 'Adaptive UI', 'description' => 'Evolves with you.', 'icon' => 'layout'],
                                ['title' => 'Global Scale', 'description' => 'Deploy worldwide.', 'icon' => 'globe'],
                            ],
                            'padding' => 'py-20',
                            'bgColor' => 'transparent',
                            '_css_class' => 'bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-white',
                        ],
                    ],
                    [
                        'id' => 'cta-1',
                        'type' => 'cta',
                        'settings' => [
                            'title' => 'Ready to transform your workflow?',
                            'buttonText' => 'Start Your Free Trial',
                            'buttonUrl' => '/register',
                            '_css_class' => 'bg-slate-100 dark:bg-slate-900 my-8 rounded-2xl mx-4 shadow-lg',
                        ],
                    ],
                ]),
            ],
            // 2. Creative Portfolio Bento (Pro)
            [
                'name' => 'Creative Studio Bento',
                'slug' => 'creative-studio-bento',
                'description' => 'A minimalist bento-style portfolio.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'portfolio-hero',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'We Design Experiences.',
                            'subtitle' => 'Award-winning digital studio based in Tokyo.',
                            'bgColor' => 'transparent',
                            'padding' => 'py-24',
                            '_css_class' => 'bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100',
                        ],
                    ],
                    [
                        'id' => 'works-grid',
                        'type' => 'portfolio',
                        'settings' => [
                            'columns' => 2,
                            'title' => 'Selected Works',
                        ],
                    ],
                ]),
            ],
            // 3. Modern Stats Section (Pro)
            [
                'name' => 'Modular Stats Section',
                'slug' => 'modern-stats-strip',
                'description' => 'Impactful statistics counter strip.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'stats-1',
                        'type' => 'columns',
                        'settings' => [
                            'layout' => '1-1-1',
                            '_css_class' => 'bg-primary/5 rounded-xl border border-primary/10 p-8',
                            'columns' => [
                                [
                                    'blocks' => [
                                        ['id' => 'count-1', 'type' => 'counter', 'settings' => ['number' => 500, 'label' => 'Customers', 'prefix' => '', '_css_class' => 'text-primary']],
                                    ],
                                ],
                                [
                                    'blocks' => [
                                        ['id' => 'count-2', 'type' => 'counter', 'settings' => ['number' => 120, 'label' => 'Projects', 'prefix' => '', '_css_class' => 'text-primary']],
                                    ],
                                ],
                                [
                                    'blocks' => [
                                        ['id' => 'count-3', 'type' => 'counter', 'settings' => ['number' => 24, 'label' => 'Countries', 'prefix' => '', '_css_class' => 'text-primary']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            // 4. Glassmorphism Pricing (Pro)
            [
                'name' => 'Premium Pricing Plans',
                'slug' => 'premium-pricing-2026',
                'description' => 'Elegant pricing table with glassmorphism effects.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'pricing-pro',
                        'type' => 'pricing',
                        'settings' => [
                            'title' => 'Choose Your Plan',
                            '_css_class' => 'backdrop-blur-md bg-white/30 border border-white/20 rounded-3xl shadow-xl',
                            'items' => [
                                ['name' => 'Starter', 'price' => '$0', 'features' => ['Basic Tools', '1 Project'], 'buttonText' => 'Get Started'],
                                ['name' => 'Pro', 'price' => '$49', 'features' => ['Everything', 'Unlimited'], 'buttonText' => 'Go Pro'],
                                ['name' => 'Biz', 'price' => '$199', 'features' => ['Enterprise', '24/7'], 'buttonText' => 'Contact'],
                            ],
                        ],
                    ],
                ]),
            ],
        ];

        foreach ($templates as $template) {
            ContentTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
