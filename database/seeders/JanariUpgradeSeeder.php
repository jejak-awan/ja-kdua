<?php

namespace Database\Seeders;

use App\Models\ContentTemplate;
use Illuminate\Database\Seeder;

class JanariUpgradeSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // 1. Product Landing (SaaS Style)
            [
                'name' => 'Janari: Product Application',
                'slug' => 'janari-product-landing',
                'description' => 'A premium SaaS landing page with a modern hero, bento features, and social proof.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'hero-product',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'Build Better. Faster. Together.',
                            'subtitle' => 'The ultimate platform for modern teams to collaborate, build, and scale without friction.',
                            'padding' => 'py-32',
                            '_css_class' => 'bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white',
                            'animation' => 'animate-in fade-in zoom-in-95 duration-1000',
                        ],
                    ],
                    [
                        'id' => 'logos-social',
                        'type' => 'columns',
                        'settings' => [
                            'layout' => '1',
                            'width' => 'max-w-4xl',
                            'padding' => 'py-12',
                            'columns' => [
                                [
                                    'blocks' => [
                                        [
                                            'id' => 'trust-heading',
                                            'type' => 'heading',
                                            'settings' => [
                                                'title' => 'Trusted by 10,000+ teams worldwide',
                                                'level' => 'h5',
                                                'align' => 'center',
                                                '_css_class' => 'opacity-50 uppercase tracking-widest text-xs mb-8'
                                            ]
                                        ],
                                        // Since we don't have a specific LogoCloud block, we can use columns or a custom class on IconList
                                        [
                                            'id' => 'logo-grid',
                                            'type' => 'columns',
                                            'settings' => [
                                                'layout' => '1-1-1-1',
                                                'columns' => [
                                                    ['blocks' => [['type' => 'image', 'settings' => ['url' => '/sample-logo-1.png', '_css_class' => 'grayscale opacity-50 hover:opacity-100 transition-opacity']]]],
                                                    ['blocks' => [['type' => 'image', 'settings' => ['url' => '/sample-logo-2.png', '_css_class' => 'grayscale opacity-50 hover:opacity-100 transition-opacity']]]],
                                                    ['blocks' => [['type' => 'image', 'settings' => ['url' => '/sample-logo-3.png', '_css_class' => 'grayscale opacity-50 hover:opacity-100 transition-opacity']]]],
                                                    ['blocks' => [['type' => 'image', 'settings' => ['url' => '/sample-logo-4.png', '_css_class' => 'grayscale opacity-50 hover:opacity-100 transition-opacity']]]],
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'id' => 'features-bento',
                        'type' => 'features',
                        'settings' => [
                            'title' => 'Everything you need to succeed',
                            'items' => [
                                ['title' => 'Real-time Sync', 'description' => 'Collaboration that feels like magic.', 'icon' => 'zap'],
                                ['title' => 'Advanced Analytics', 'description' => 'Insights that drive decisions.', 'icon' => 'bar-chart'],
                                ['title' => 'Enterprise Security', 'description' => 'Bank-grade encryption at every step.', 'icon' => 'shield'],
                                ['title' => 'Global CDN', 'description' => 'Server your users faster anywhere.', 'icon' => 'globe'],
                                ['title' => 'API First', 'description' => 'Integrate with everything seamlessly.', 'icon' => 'code'],
                                ['title' => 'Smart Storage', 'description' => 'Infinite scale for your files.', 'icon' => 'database'],
                            ],
                            'padding' => 'py-24',
                            '_css_class' => 'bg-slate-50 dark:bg-slate-950',
                        ],
                    ],
                    [
                        'id' => 'cta-product',
                        'type' => 'cta',
                        'settings' => [
                            'title' => 'Ready to experience the future?',
                            'subtitle' => 'Join thousands of developers building amazing things.',
                            'buttonText' => 'Get Started for Free',
                            'buttonUrl' => '/register',
                            'padding' => 'py-20',
                            '_css_class' => 'bg-primary text-primary-foreground text-center rounded-3xl mx-4 my-12 shadow-2xl',
                        ],
                    ],
                ]),
            ],
            // 2. Modern Blog Index
            [
                'name' => 'Janari: Modern Blog',
                'slug' => 'janari-blog-index',
                'description' => 'An elegant blog layout with a featured post area and clean category grid.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'blog-hero',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'The Jejakawan Blog',
                            'subtitle' => 'Thoughts, stories, and ideas from our team of engineers and designers.',
                            'padding' => 'py-24',
                            'align' => 'center',
                            '_css_class' => 'bg-white dark:bg-zinc-950 border-b',
                        ],
                    ],
                    [
                        'id' => 'main-blog-grid',
                        'type' => 'blog_grid',
                        'settings' => [
                            'limit' => 6,
                            'columns' => 3,
                            'layout' => 'grid',
                            'padding' => 'py-20',
                        ],
                    ],
                    [
                        'id' => 'blog-newsletter',
                        'type' => 'email_optin',
                        'settings' => [
                            'title' => 'Subscribe to our newsletter',
                            'description' => 'Get the latest posts delivered straight to your inbox.',
                            '_css_class' => 'bg-slate-100 dark:bg-slate-900 py-16 text-center border-t',
                        ],
                    ],
                ]),
            ],
            // 3. Tutorial Template
            [
                'name' => 'Janari: Tutorial Layout',
                'slug' => 'janari-tutorial',
                'description' => 'A focused layout for tutorials with a sidebar for navigation.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'tutorial-layout',
                        'type' => 'columns',
                        'settings' => [
                            'layout' => '1-3', // Sidebar on left
                            'gap' => 'gap-12',
                            'padding' => 'py-12',
                            'columns' => [
                                [
                                    'blocks' => [
                                        [
                                            'id' => 'tutorial-nav',
                                            'type' => 'sidebar',
                                            'settings' => [
                                                'title' => 'Learning Path',
                                                'menu' => 'tutorial_nav',
                                                '_css_class' => 'sticky top-24'
                                            ]
                                        ],
                                    ],
                                ],
                                [
                                    'blocks' => [
                                        [
                                            'id' => 'tut-head',
                                            'type' => 'post_title',
                                            'settings' => ['_css_class' => 'mb-6']
                                        ],
                                        [
                                            'id' => 'tut-meta',
                                            'type' => 'post_meta',
                                            'settings' => ['_css_class' => 'mb-8 opacity-60']
                                        ],
                                        [
                                            'id' => 'tut-content',
                                            'type' => 'post_content',
                                            'settings' => ['_css_class' => 'prose dark:prose-invert max-w-none']
                                        ],
                                        [
                                            'id' => 'tut-nav',
                                            'type' => 'post_nav',
                                            'settings' => ['_css_class' => 'mt-16 border-t pt-8']
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            // 4. Documentation Template
            [
                'name' => 'Janari: Documentation',
                'slug' => 'janari-docs',
                'description' => 'A structured documentation layout with persistent sidebar and search.',
                'type' => 'builder',
                'is_active' => true,
                'body_template' => json_encode([
                    [
                        'id' => 'docs-layout',
                        'type' => 'columns',
                        'settings' => [
                            'layout' => '1-3',
                            'gap' => 'gap-12',
                            'padding' => 'py-12',
                            'columns' => [
                                [
                                    'blocks' => [
                                        [
                                            'id' => 'docs-search',
                                            'type' => 'search',
                                            'settings' => ['_css_class' => 'mb-8']
                                        ],
                                        [
                                            'id' => 'docs-nav',
                                            'type' => 'sidebar',
                                            'settings' => [
                                                'title' => 'Documentation',
                                                'menu' => 'docs_nav',
                                                '_css_class' => 'sticky top-24'
                                            ]
                                        ],
                                    ],
                                ],
                                [
                                    'blocks' => [
                                        [
                                            'id' => 'alert-docs',
                                            'type' => 'alert',
                                            'settings' => [
                                                'title' => 'Beta Version',
                                                'content' => 'This documentation is for the beta version of Janari.',
                                                'type' => 'info',
                                                '_css_class' => 'mb-8'
                                            ]
                                        ],
                                        [
                                            'id' => 'docs-head',
                                            'type' => 'heading',
                                            'settings' => [
                                                'title' => 'Getting Started',
                                                'level' => 'h1',
                                                '_css_class' => 'mb-6 tracking-tight'
                                            ]
                                        ],
                                        [
                                            'id' => 'docs-text',
                                            'type' => 'text',
                                            'settings' => [
                                                'content' => 'Welcome to the official Janari documentation. Here you will find everything you need to build and scale your CMS.',
                                                '_css_class' => 'text-lg opacity-80 mb-12'
                                            ]
                                        ],
                                        [
                                            'id' => 'docs-code',
                                            'type' => 'code',
                                            'settings' => [
                                                'code' => 'npm install ja-cms@latest',
                                                'language' => 'bash',
                                                'title' => 'Installation',
                                                '_css_class' => 'mb-12'
                                            ]
                                        ],
                                    ],
                                ],
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

        // --- Create Sample Pages ---
        $pages = [
            ['title' => 'Product Application', 'slug' => 'product-demo', 'template_slug' => 'janari-product-landing'],
            ['title' => 'Blog Home', 'slug' => 'blog-home', 'template_slug' => 'janari-blog-index'],
            ['title' => 'Getting Started', 'slug' => 'docs-intro', 'template_slug' => 'janari-docs'],
            ['title' => 'First Tutorial', 'slug' => 'tutorial-lesson-1', 'template_slug' => 'janari-tutorial'],
        ];

        foreach ($pages as $p) {
            $template = ContentTemplate::where('slug', $p['template_slug'])->first();
            
            \App\Models\Content::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'type' => 'page',
                    'status' => 'published',
                    'author_id' => 1,
                    'excerpt' => 'Sample page showing the ' . $p['template_slug'] . ' template.',
                    'blocks' => $template ? json_decode($template->body_template, true) : null,
                    'body' => '',
                ]
            );
        }

        // --- Create Specialized Menus ---
        $this->createTutorialMenu();
        $this->createDocsMenu();
        $this->createProductMenu();
        $this->createBlogMenu();
    }

    private function createProductMenu()
    {
        $menu = \App\Models\Menu::firstOrCreate(
            ['location' => 'product_header'],
            ['name' => 'Product Header Menu', 'slug' => 'product-header']
        );
        $menu->items()->delete();

        $items = [
            ['title' => 'Features', 'url' => '#features'],
            ['title' => 'Pricing', 'url' => '#pricing'],
            ['title' => 'About', 'url' => '#about'],
            ['title' => 'Get Started', 'url' => '/register'],
        ];

        foreach ($items as $index => $item) {
            \App\Models\MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'type' => 'url',
                'url' => $item['url'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createBlogMenu()
    {
        $menu = \App\Models\Menu::firstOrCreate(
            ['location' => 'blog_header'],
            ['name' => 'Blog Header Menu', 'slug' => 'blog-header']
        );
        $menu->items()->delete();

        $items = [
            ['title' => 'Latest Posts', 'url' => '/blog-home'],
            ['title' => 'Technology', 'url' => '#'],
            ['title' => 'Design', 'url' => '#'],
            ['title' => 'News', 'url' => '#'],
            ['title' => 'Subscribe', 'url' => '#newsletter'],
        ];

        foreach ($items as $index => $item) {
            \App\Models\MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'type' => 'url',
                'url' => $item['url'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createTutorialMenu()
    {
        $menu = \App\Models\Menu::firstOrCreate(
            ['location' => 'tutorial_nav'],
            ['name' => 'Tutorial Navigation', 'slug' => 'tutorial-nav']
        );
        $menu->items()->delete();

        $items = [
            ['title' => 'Introduction', 'url' => '/tutorial-lesson-1'],
            ['title' => 'Setting up Environment', 'url' => '#'],
            ['title' => 'First Steps', 'url' => '#'],
            ['title' => 'Advanced Concepts', 'url' => '#'],
            ['title' => 'Summary', 'url' => '#'],
        ];

        foreach ($items as $index => $item) {
            \App\Models\MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'type' => 'url',
                'url' => $item['url'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function createDocsMenu()
    {
        $menu = \App\Models\Menu::firstOrCreate(
            ['location' => 'docs_nav'],
            ['name' => 'Documentation Menu', 'slug' => 'docs-nav']
        );
        $menu->items()->delete();

        $items = [
            ['title' => 'Introduction', 'url' => '/docs-intro'],
            ['title' => 'Installation', 'url' => '#'],
            ['title' => 'Configuration', 'url' => '#'],
            ['title' => 'Theming Guide', 'url' => '#'],
            ['title' => 'API Reference', 'url' => '#'],
            ['title' => 'Deployment', 'url' => '#'],
        ];

        foreach ($items as $index => $item) {
            \App\Models\MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'type' => 'url',
                'url' => $item['url'],
                'sort_order' => $index + 1,
            ]);
        }
    }
}
