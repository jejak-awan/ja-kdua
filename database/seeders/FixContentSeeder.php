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
            // Ensure Home URL
            MenuItem::where('menu_id', $headerMenu->id)
                ->where('title', 'Home')
                ->update(['url' => '/']);

            // Ensure Blog URL
            MenuItem::where('menu_id', $headerMenu->id)
                ->where('title', 'Blog')
                ->update(['url' => '/blog']);
            
            // Ensure About URL
            MenuItem::where('menu_id', $headerMenu->id)
                ->where('title', 'About')
                ->orWhere('title', 'About Us')
                ->update(['url' => '/about', 'title' => 'About']);

            // Find and delete duplicate About entries (if still exist)
            $count = MenuItem::where('menu_id', $headerMenu->id)
                ->where('title', 'About')
                ->count();
                
            if ($count > 1) {
                MenuItem::where('menu_id', $headerMenu->id)
                    ->where('title', 'About')
                    ->skip(1)
                    ->take($count - 1)
                    ->delete();
                $this->command->info('Deleted duplicate "About" menu items.');
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
                    'layout' => '1-1',
                    'padding' => 'py-20',
                    'width' => 'max-w-7xl',
                    'columns' => [
                        [
                            'blocks' => [
                                [
                                    'id' => Str::uuid()->toString(),
                                    'type' => 'contact_form',
                                    'settings' => [
                                        'title' => 'Send us a Message',
                                        'description' => 'We\'ll get back to you within 24 hours.',
                                        'buttonText' => 'Send Message',
                                        'successMessage' => 'Thank you! We will get back to you soon.',
                                        'style' => 'bg-card border shadow-sm p-8 rounded-2xl',
                                        'fields' => [
                                            ['label' => 'Name', 'type' => 'text', 'required' => true, 'width' => 'w-full md:w-[calc(50%-1rem)]'],
                                            ['label' => 'Email', 'type' => 'email', 'required' => true, 'width' => 'w-full md:w-[calc(50%-1rem)]'],
                                            ['label' => 'Message', 'type' => 'textarea', 'required' => true, 'width' => 'w-full']
                                        ],
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
                                        'isProse' => false,
                                        'content' => '<div class="space-y-6">
    <div>
        <h3 class="text-3xl font-bold mb-2 tracking-tight">Contact Info</h3>
        <p class="text-muted-foreground text-sm">Have a specific inquiry or just want to say hi? Reach out to us below.</p>
    </div>
    <div class="space-y-4">
        <div class="flex items-center gap-4 group">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 transition-all group-hover:bg-primary group-hover:text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Email Us</p>
                <p class="font-semibold">hello@ja-cms.com</p>
            </div>
        </div>
        <div class="flex items-center gap-4 group">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 transition-all group-hover:bg-primary group-hover:text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Call Us</p>
                <p class="font-semibold">+1 (555) 123-4567</p>
            </div>
        </div>
        <div class="flex items-center gap-4 group">
            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 transition-all group-hover:bg-primary group-hover:text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60">Visit Us</p>
                <p class="font-semibold">123 Innovation Street, Tech City</p>
            </div>
        </div>
    </div>
    <div class="pt-6 border-t border-border">
        <h4 class="font-bold mb-3 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
            Office Hours
        </h4>
        <div class="grid grid-cols-2 gap-y-2 text-sm italic">
            <p class="text-muted-foreground">Mon - Fri</p>
            <p class="text-right font-medium">9am - 6pm</p>
            <p class="text-muted-foreground">Sat - Sun</p>
            <p class="text-right font-bold text-primary">Closed</p>
        </div>
    </div>
</div>',

                                        'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                                    ]
                                ]
                            ]
                        ]
                    ], 
                    'gap' => 'gap-16',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true]
                ]
            ],
            // Map
            [
                'id' => Str::uuid()->toString(),
                'type' => 'map',
                'settings' => [
                    'embedUrl' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.521260322283!2d106.8195613507864!3d-6.194741395493371!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5390917b759%3A0x1daecad134304c31!2sIndonesia%20Stock%20Exchange!5e0!3m2!1sen!2sid!4v1645421020061!5m2!1sen!2sid',
                    'height' => 450,
                    'radius' => 'rounded-2xl',
                    'padding' => 'py-0',
                    'width' => 'max-w-7xl',
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
                    'postType' => 'post',
                    'columns' => 3,
                    'limit' => 9,
                    'showImage' => true,
                    'showExcerpt' => true,
                    'showDate' => true,
                    'showAuthor' => true,
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
