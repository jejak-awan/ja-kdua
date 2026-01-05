<?php

namespace Database\Seeders;

use App\Models\ContentTemplate;
use Illuminate\Database\Seeder;

class ModernLayoutTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // 1. SaaS Luxury Landing (Full Page)
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
                            'bgColor' => '#09090b',
                            'padding' => 'py-32',
                            'animation' => 'animate-in fade-in zoom-in-95 duration-1000'
                        ]
                    ],
                    [
                        'id' => 'feature-bento-1',
                        'type' => 'features',
                        'settings' => [
                            'title' => 'Engineered for Performance',
                            'items' => [
                                ['title' => 'Quantum Speed', 'description' => 'Process millions of data points in milliseconds.'],
                                ['title' => 'Neural Security', 'description' => 'Advanced encryption powered by neural networks.'],
                                ['title' => 'Adaptive UI', 'description' => 'A user interface that learns and evolves with you.'],
                                ['title' => 'Global Scale', 'description' => 'Deploy worldwide with zero latency.'],
                            ],
                            'padding' => 'py-20',
                            'bgColor' => 'transparent'
                        ]
                    ],
                    [
                        'id' => 'cta-1',
                        'type' => 'cta',
                        'settings' => [
                            'title' => 'Ready to transform your workflow?',
                            'buttonText' => 'Start Your Free Trial',
                            'buttonUrl' => '/register'
                        ]
                    ]
                ]),
            ],
            // 2. Creative Portfolio Bento (Full Page)
            [
                'name' => 'Creative Studio Bento',
                'slug' => 'creative-studio-bento',
                'description' => 'A minimalist bento-style portfolio for design agencies and creative freelancers.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'portfolio-hero',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'We Design Experiences.',
                            'subtitle' => 'Award-winning digital studio based in Tokyo. We craft beautiful interfaces and unique brand identities.',
                            'bgColor' => 'transparent',
                            'padding' => 'py-24'
                        ]
                    ],
                    [
                        'id' => 'works-grid',
                        'type' => 'portfolio',
                        'settings' => [
                            'columns' => 2,
                            'title' => 'Selected Works'
                        ]
                    ]
                ]),
            ],
            // 3. Modern Stats Section (Modular Section)
            [
                'name' => 'Modular Stats Section',
                'slug' => 'modern-stats-strip',
                'description' => 'Impactful statistics counter strip for business profiles.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'stats-1',
                        'type' => 'columns',
                        'settings' => [
                            'layout' => '1-1-1',
                            'columns' => [
                                [
                                    'blocks' => [
                                        ['id' => 'count-1', 'type' => 'counter', 'settings' => ['number' => 500, 'label' => 'Customers', 'prefix' => '']]
                                    ]
                                ],
                                [
                                    'blocks' => [
                                        ['id' => 'count-2', 'type' => 'counter', 'settings' => ['number' => 120, 'label' => 'Projects', 'prefix' => '']]
                                    ]
                                ],
                                [
                                    'blocks' => [
                                        ['id' => 'count-3', 'type' => 'counter', 'settings' => ['number' => 24, 'label' => 'Countries', 'prefix' => '']]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]),
            ],
            // 4. Glassmorphism Pricing (Modular Section)
            [
                'name' => 'Premium Pricing Plans',
                'slug' => 'premium-pricing-2026',
                'description' => 'Elegant pricing table with glassmorphism effects and highlighted plan.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'pricing-1',
                        'type' => 'pricing',
                        'settings' => [
                            'title' => 'Choose Your Plan',
                            'items' => [
                                ['name' => 'Starter', 'price' => '$0', 'features' => ['Basic Tools', '1 Project'], 'buttonText' => 'Get Started'],
                                ['name' => 'Professional', 'price' => '$49', 'features' => ['Advanced Tools', 'Unlimited Projects', 'Priority Support'], 'buttonText' => 'Go Pro'],
                                ['name' => 'Business', 'price' => '$199', 'features' => ['Enterprise Tools', 'Team Collaboration', '24/7 Support'], 'buttonText' => 'Contact Sales'],
                            ]
                        ]
                    ]
                ]),
            ],
            // 5. Clean FAQ Accordion
            [
                'name' => 'Support / FAQ Accordion',
                'slug' => 'clean-faq-accordion',
                'description' => 'Clean and accessible FAQ section using expandable accordions.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'faq-1',
                        'type' => 'accordion',
                        'settings' => [
                            'title' => 'Got Questions?',
                            'items' => [
                                ['question' => 'How does it work?', 'answer' => 'Our platform uses advanced algorithms to automate your daily tasks.'],
                                ['question' => 'Is my data secure?', 'answer' => 'We use military-grade encryption to protect your sensitive information.'],
                                ['question' => 'Can I cancel anytime?', 'answer' => 'Yes, you can cancel your subscription at any time from your settings.'],
                            ]
                        ]
                    ]
                ]),
            ],
            // 6. Impactful Blog Grid
            [
                'name' => 'Latest Insights Grid',
                'slug' => 'modern-blog-grid',
                'description' => 'A beautiful grid display of your latest blog posts or articles.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'blog-grid-1',
                        'type' => 'blog-grid',
                        'settings' => [
                            'title' => 'From the Blog',
                            'post_count' => 3,
                            'columns' => 3
                        ]
                    ]
                ]),
            ],
            // 7. Minimalist Team Section
            [
                'name' => 'Our Creative Team',
                'slug' => 'minimal-team-profiles',
                'description' => 'Display your team members with large avatars and brief bios.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'team-columns',
                        'type' => 'columns',
                        'settings' => [
                            'layout' => '1-1-1',
                            'columns' => [
                                [
                                    'blocks' => [
                                        ['id' => 'p1', 'type' => 'person', 'settings' => ['name' => 'Alex Rivera', 'role' => 'Founder & CEO', 'bio' => 'Visionary leader with 10+ years in AI.']]
                                    ]
                                ],
                                [
                                    'blocks' => [
                                        ['id' => 'p2', 'type' => 'person', 'settings' => ['name' => 'Elena Chen', 'role' => 'Creative Director', 'bio' => 'Designing the future of human-computer interaction.']]
                                    ]
                                ],
                                [
                                    'blocks' => [
                                        ['id' => 'p3', 'type' => 'person', 'settings' => ['name' => 'Marcus Thorne', 'role' => 'Lead Engineer', 'bio' => 'Scaling systems to handle millions of requests every second.']]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]),
            ],
            // 8. Logo Cloud / Trust Strip
            [
                'name' => 'Partner / Logo Strip',
                'slug' => 'logo-trust-strip',
                'description' => 'Showcase your partners, clients, or technologies you work with.',
                'type' => 'section',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'logos-1',
                        'type' => 'features',
                        'settings' => [
                            'title' => 'Trusted by Innovative Companies',
                            'items' => [
                                ['title' => 'TechFlow', 'description' => 'Industry Partner', 'icon' => 'activity'],
                                ['title' => 'CloudScale', 'description' => 'Infrastructure Partner', 'icon' => 'cloud'],
                                ['title' => 'SecureNet', 'description' => 'Security Partner', 'icon' => 'shield-check'],
                                ['title' => 'DataCore', 'description' => 'Storage Partner', 'icon' => 'database'],
                            ],
                            'columns' => 4,
                            'padding' => 'py-12'
                        ]
                    ]
                ]),
            ]
        ];

        foreach ($templates as $template) {
            ContentTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
