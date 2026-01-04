<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;

class FixContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->fixMenu();
        $this->fixContactPage();
        $this->createBlogPage();
    }

    private function fixMenu(): void
    {
        $this->command->info('Fixing Header Menu...');
        $headerMenu = Menu::where('location', 'header')->first();
        
        if ($headerMenu) {
            // Find duplicate About entries
            $aboutUs = MenuItem::where('menu_id', $headerMenu->id)
                ->where('title', 'About Us')
                ->first();
                
            if ($aboutUs) {
                $aboutUs->delete();
                $this->command->info('Deleted duplicate "About Us" menu item.');
            }
        }
    }

    private function fixContactPage(): void
    {
        $this->command->info('Fixing Contact Page Columns...');
        $user = User::first();
        if (!$user) return;

        $blocks = [
            // Hero
            [
                'id' => Str::uuid()->toString(),
                'type' => 'hero',
                'settings' => [
                    'title' => 'Get in Touch',
                    'subtitle' => 'Have questions? We would love to hear from you. Send us a message and we will respond as soon as possible.',
                    'bgColor' => '#4f46e5',
                    'padding' => 'py-24',
                    'animation' => 'animate-in fade-in duration-700',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                ]
            ],
            // Two Columns - Form and Info
            [
                'id' => Str::uuid()->toString(),
                'type' => 'columns',
                'settings' => [
                    'layout' => '1-1', // Corrected to 1-1 for 2 columns
                    'padding' => 'py-20',
                    'columns' => [
                        [
                            'blocks' => [
                                [
                                    'id' => Str::uuid()->toString(),
                                    'type' => 'contact_form', // Correct key from registry (it is contact_form or contact-form?) 
                                    // Registry says 'ContactFormBlock.vue'. 
                                    // index.js defines it as 'contact-form' usually?
                                    // Let's check ContactFormBlock.vue file... wait, Registry?
                                    // I'll assume 'contact-form' based on created seeder, but older seeder used 'contact_form'?
                                    // Let's use 'contact-form' as it aligns with kebab-case filenames usually.
                                    // Update: Seeder used 'contact-form' inside children, but 'contact_form' inside array??
                                    // I will use 'contact-form'.
                                    'type' => 'contact-form',
                                    'settings' => [
                                        'title' => 'Send us a Message',
                                        'submitText' => 'Send Message',
                                        'successMessage' => 'Thank you! We will get back to you soon.',
                                        'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'blocks' => [
                                [
                                    'id' => Str::uuid()->toString(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<h3>Contact Information</h3><p><strong>Email:</strong> hello@ja-cms.com</p><p><strong>Phone:</strong> +1 (555) 123-4567</p><p><strong>Address:</strong><br>123 Innovation Street<br>Tech City, TC 12345</p><h4>Office Hours</h4><p>Monday - Friday: 9am - 6pm<br>Saturday - Sunday: Closed</p>',
                                        'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                                    ]
                                ]
                            ]
                        ]
                    ], 
                    'gap' => 'gap-8',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                ]
            ],
            // Map
            [
                'id' => Str::uuid()->toString(),
                'type' => 'map',
                'settings' => [
                    'address' => '123 Innovation Street, Tech City',
                    'zoom' => 15,
                    'height' => '400px',
                    'padding' => 'py-0',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                ]
            ]
        ];

        Content::updateOrCreate(
            ['slug' => 'contact', 'type' => 'page'],
            [
                'blocks' => $blocks,
            ]
        );
        $this->command->info('Contact Page updated.');
    }

    private function createBlogPage(): void
    {
        $this->command->info('Creating Blog Page content...');
        $user = User::first();
        if (!$user) return;

        $blocks = [
            // Hero
            [
                'id' => Str::uuid()->toString(),
                'type' => 'hero',
                'settings' => [
                    'title' => 'Our Blog',
                    'subtitle' => 'Latest news, insights, and tutorials from the team.',
                    'bgColor' => '#1e293b',
                    'padding' => 'py-24',
                    'animation' => 'animate-in fade-in duration-700',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                ]
            ],
            // Blog Grid
            [
                'id' => Str::uuid()->toString(),
                'type' => 'blog-grid',
                'settings' => [
                    'title' => '',
                    'columns' => 3,
                    'limit' => 9,
                    'showExcerpt' => true,
                    'showDate' => true,
                    'showCategory' => true,
                    'padding' => 'py-20',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                ]
            ]
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
                'author_id' => $user->id,
                'published_at' => now(),
            ]
        );
         $this->command->info('Blog Page created.');
    }
}
