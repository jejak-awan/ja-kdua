<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BuilderPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates Home, Blog, About, Contact pages with builder blocks
     */
    public function run(): void
    {
        $this->command->info('Creating default pages with Builder blocks...');

        $user = User::first();
        
        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        // Delete existing pages first (by slug only to avoid conflicts)
        $slugs = ['home', 'blog', 'about', 'contact'];
        $deleted = Content::whereIn('slug', $slugs)->delete();
        if ($deleted > 0) {
            $this->command->info("Deleted {$deleted} existing page(s)");
        }

        // Create pages
        $this->createHomePage($user);
        $this->createBlogPage($user);
        $this->createAboutPage($user);
        $this->createContactPage($user);

        $this->command->info('Default pages created successfully!');
    }

    /**
     * Generate a UUID for block IDs
     */
    private function uuid(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Create a section block wrapper
     */
    private function section(array $children, array $settings = []): array
    {
        return [
            'id' => $this->uuid(),
            'type' => 'section',
            'settings' => array_merge([
                'padding' => ['top' => '80', 'right' => '0', 'bottom' => '80', 'left' => '0'],
            ], $settings),
            'children' => $children,
        ];
    }

    /**
     * Create a row block
     * Columns format: "1-1" for 2 equal columns, "2-1" for 2:1 ratio, etc.
     */
    private function row(array $children, array $settings = []): array
    {
        // Generate columns string from child count if not provided
        // e.g., 3 children = "1-1-1" (equal columns)
        if (!isset($settings['columns'])) {
            $count = count($children);
            $settings['columns'] = $count > 0 ? implode('-', array_fill(0, $count, '1')) : '1';
        } elseif (is_int($settings['columns'])) {
            // Convert number to string format
            $count = $settings['columns'];
            $settings['columns'] = implode('-', array_fill(0, $count, '1'));
        }
        
        return [
            'id' => $this->uuid(),
            'type' => 'row',
            'settings' => $settings,
            'children' => $children,
        ];
    }

    /**
     * Create a column block
     */
    private function column(array $children, array $settings = []): array
    {
        return [
            'id' => $this->uuid(),
            'type' => 'column',
            'settings' => $settings,
            'children' => $children,
        ];
    }

    /**
     * Create Home Page with Hero, Features, Testimonials, CTA
     */
    private function createHomePage($user): void
    {
        $blocks = [
            // Hero Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => '@dynamic:{{site_title}}',
                                'tag' => 'h1',
                                'alignment' => 'center',
                                'fontSize' => '48px',
                                'fontWeight' => '700',
                                'color' => '#ffffff',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'text',
                            'settings' => [
                                'content' => '<p style="text-align: center; font-size: 20px; color: rgba(255,255,255,0.9);">Build stunning websites with our powerful visual builder. Create, customize, and publish with ease.</p>',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'button',
                            'settings' => [
                                'text' => 'Get Started',
                                'url' => '/register',
                                'alignment' => 'center',
                                'bgColor' => '#ffffff',
                                'textColor' => '#4f46e5',
                                'borderRadius' => '8px',
                                'padding' => ['top' => '16', 'right' => '32', 'bottom' => '16', 'left' => '32'],
                            ],
                        ],
                    ]),
                ]),
            ], [
                'background' => [
                    'type' => 'gradient',
                    'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                ],
                'padding' => ['top' => '120', 'right' => '0', 'bottom' => '120', 'left' => '0'],
            ]),

            // Features Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Why Choose Us?',
                                'tag' => 'h2',
                                'alignment' => 'center',
                                'fontSize' => '36px',
                                'fontWeight' => '600',
                            ],
                        ],
                    ]),
                ]),
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blurb',
                            'settings' => [
                                'title' => 'Visual Builder',
                                'content' => 'Create pages visually with our intuitive drag-and-drop builder.',
                                'icon' => 'Layers',
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                    ]),
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blurb',
                            'settings' => [
                                'title' => 'Dynamic Content',
                                'content' => 'Integrate dynamic data seamlessly into your designs.',
                                'icon' => 'Database',
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                    ]),
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blurb',
                            'settings' => [
                                'title' => 'Fast & Responsive',
                                'content' => 'Optimized for speed and works perfectly on all devices.',
                                'icon' => 'Zap',
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                    ]),
                ], ['columns' => 3]),
            ]),

            // CTA Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'cta',
                            'settings' => [
                                'title' => 'Ready to Get Started?',
                                'subtitle' => 'Join thousands of creators building amazing websites.',
                                'buttonText' => 'Start Free Trial',
                                'buttonUrl' => '/register',
                                'alignment' => 'center',
                            ],
                        ],
                    ]),
                ]),
            ], [
                'background' => [
                    'type' => 'color',
                    'color' => '#f8fafc',
                ],
            ]),
        ];

        Content::updateOrCreate(
            ['slug' => 'home', 'type' => 'page'],
            [
                'title' => 'Home',
                'slug' => 'home',
                'type' => 'page',
                'status' => 'published',
                'body' => '',
                'blocks' => $blocks,
                'editor_type' => 'builder',
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'Home - ' . config('app.name'),
                'meta_description' => 'Welcome to our website. Build stunning websites with our powerful visual builder.',
            ]
        );

        $this->command->info('✓ Home page created');
    }

    /**
     * Create Blog Page with Posts List
     */
    private function createBlogPage($user): void
    {
        $blocks = [
            // Header Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Our Blog',
                                'tag' => 'h1',
                                'alignment' => 'center',
                                'fontSize' => '42px',
                                'fontWeight' => '700',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'text',
                            'settings' => [
                                'content' => '<p style="text-align: center; font-size: 18px; color: #64748b;">Discover our latest articles, tutorials, and insights.</p>',
                            ],
                        ],
                    ]),
                ]),
            ], [
                'padding' => ['top' => '60', 'right' => '0', 'bottom' => '40', 'left' => '0'],
            ]),

            // Blog Posts Grid
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blog',
                            'settings' => [
                                'layout' => 'grid',
                                'columns' => 3,
                                'postsPerPage' => 9,
                                'showExcerpt' => true,
                                'showDate' => true,
                                'showAuthor' => true,
                                'showCategory' => true,
                                'showFeaturedImage' => true,
                                'imageAspectRatio' => '16/9',
                            ],
                        ],
                    ]),
                ]),
            ], [
                'padding' => ['top' => '40', 'right' => '0', 'bottom' => '80', 'left' => '0'],
            ]),
        ];

        Content::updateOrCreate(
            ['slug' => 'blog', 'type' => 'page'],
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'type' => 'page',
                'status' => 'published',
                'body' => '',
                'blocks' => $blocks,
                'editor_type' => 'builder',
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'Blog - ' . config('app.name'),
                'meta_description' => 'Read our latest blog posts, tutorials, and insights.',
            ]
        );

        $this->command->info('✓ Blog page created');
    }

    /**
     * Create About Page with Team and Mission
     */
    private function createAboutPage($user): void
    {
        $blocks = [
            // Hero Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'About Us',
                                'tag' => 'h1',
                                'alignment' => 'center',
                                'fontSize' => '48px',
                                'fontWeight' => '700',
                                'color' => '#ffffff',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'text',
                            'settings' => [
                                'content' => '<p style="text-align: center; font-size: 20px; color: rgba(255,255,255,0.9);">Building the future of content management, one feature at a time.</p>',
                            ],
                        ],
                    ]),
                ]),
            ], [
                'background' => [
                    'type' => 'gradient',
                    'gradient' => 'linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%)',
                ],
                'padding' => ['top' => '100', 'right' => '0', 'bottom' => '100', 'left' => '0'],
            ]),

            // Mission Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Our Mission',
                                'tag' => 'h2',
                                'alignment' => 'left',
                                'fontSize' => '32px',
                                'fontWeight' => '600',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'text',
                            'settings' => [
                                'content' => '<p>We believe that everyone deserves powerful tools to share their ideas with the world. Our mission is to make content creation accessible, intuitive, and enjoyable.</p><p>From small businesses to large enterprises, we provide the tools you need to build stunning websites without writing a single line of code.</p>',
                            ],
                        ],
                    ]),
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'image',
                            'settings' => [
                                'src' => 'https://placehold.co/600x400/4f46e5/ffffff?text=Our+Mission',
                                'alt' => 'Our Mission',
                                'borderRadius' => '12px',
                            ],
                        ],
                    ]),
                ], ['columns' => 2]),
            ]),

            // Values Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Our Values',
                                'tag' => 'h2',
                                'alignment' => 'center',
                                'fontSize' => '32px',
                                'fontWeight' => '600',
                            ],
                        ],
                    ]),
                ]),
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blurb',
                            'settings' => [
                                'title' => 'Simplicity',
                                'content' => 'Complex solutions made simple.',
                                'icon' => 'Sparkles',
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                    ]),
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blurb',
                            'settings' => [
                                'title' => 'Innovation',
                                'content' => 'Always pushing boundaries.',
                                'icon' => 'Lightbulb',
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                    ]),
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'blurb',
                            'settings' => [
                                'title' => 'Quality',
                                'content' => 'Excellence in every detail.',
                                'icon' => 'Award',
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                    ]),
                ], ['columns' => 3]),
            ], [
                'background' => ['type' => 'color', 'color' => '#f8fafc'],
            ]),
        ];

        Content::updateOrCreate(
            ['slug' => 'about', 'type' => 'page'],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'type' => 'page',
                'status' => 'published',
                'body' => '',
                'blocks' => $blocks,
                'editor_type' => 'builder',
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'About Us - ' . config('app.name'),
                'meta_description' => 'Learn about our mission, values, and the team behind our platform.',
            ]
        );

        $this->command->info('✓ About page created');
    }

    /**
     * Create Contact Page with Form and Info
     */
    private function createContactPage($user): void
    {
        $blocks = [
            // Header Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Contact Us',
                                'tag' => 'h1',
                                'alignment' => 'center',
                                'fontSize' => '48px',
                                'fontWeight' => '700',
                                'color' => '#ffffff',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'text',
                            'settings' => [
                                'content' => '<p style="text-align: center; font-size: 20px; color: rgba(255,255,255,0.9);">Have questions? We\'d love to hear from you.</p>',
                            ],
                        ],
                    ]),
                ]),
            ], [
                'background' => [
                    'type' => 'gradient',
                    'gradient' => 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
                ],
                'padding' => ['top' => '80', 'right' => '0', 'bottom' => '80', 'left' => '0'],
            ]),

            // Contact Form and Info
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Send a Message',
                                'tag' => 'h2',
                                'fontSize' => '28px',
                                'fontWeight' => '600',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'contactform',
                            'settings' => [
                                'submitText' => 'Send Message',
                                'successMessage' => 'Thank you! We\'ll get back to you soon.',
                                'fields' => ['name', 'email', 'subject', 'message'],
                            ],
                        ],
                    ]),
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Get in Touch',
                                'tag' => 'h2',
                                'fontSize' => '28px',
                                'fontWeight' => '600',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'iconlist',
                            'settings' => [
                                'items' => [
                                    ['icon' => 'Mail', 'text' => 'hello@example.com'],
                                    ['icon' => 'Phone', 'text' => '+1 (555) 123-4567'],
                                    ['icon' => 'MapPin', 'text' => '123 Business Street, City 12345'],
                                ],
                                'iconColor' => '#4f46e5',
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'heading',
                            'settings' => [
                                'text' => 'Follow Us',
                                'tag' => 'h3',
                                'fontSize' => '20px',
                                'fontWeight' => '600',
                                'margin' => ['top' => '32', 'bottom' => '16'],
                            ],
                        ],
                        [
                            'id' => $this->uuid(),
                            'type' => 'sociallinks',
                            'settings' => [
                                'links' => [
                                    ['platform' => 'facebook', 'url' => '#'],
                                    ['platform' => 'twitter', 'url' => '#'],
                                    ['platform' => 'instagram', 'url' => '#'],
                                    ['platform' => 'linkedin', 'url' => '#'],
                                ],
                            ],
                        ],
                    ]),
                ], ['columns' => 2]),
            ]),

            // Map Section
            $this->section([
                $this->row([
                    $this->column([
                        [
                            'id' => $this->uuid(),
                            'type' => 'map',
                            'settings' => [
                                'address' => '123 Business Street, City',
                                'zoom' => 15,
                                'height' => '400px',
                            ],
                        ],
                    ]),
                ]),
            ], [
                'padding' => ['top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0'],
            ]),
        ];

        Content::updateOrCreate(
            ['slug' => 'contact', 'type' => 'page'],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'type' => 'page',
                'status' => 'published',
                'body' => '',
                'blocks' => $blocks,
                'editor_type' => 'builder',
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'Contact Us - ' . config('app.name'),
                'meta_description' => 'Get in touch with us. We\'d love to hear from you.',
            ]
        );

        $this->command->info('✓ Contact page created');
    }
}
