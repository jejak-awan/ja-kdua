<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Form;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudioSeeder extends Seeder
{
    /**
     * Run the studio structure seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@jejakawan.com')->first();
        if (!$admin) return;

        // 1. Categories
        $categories = [
            ['name' => 'Uncategorized', 'slug' => 'uncategorized', 'description' => 'Default category'],
            ['name' => 'Tutorials', 'slug' => 'tutorials', 'description' => 'Helpful guides and walkthroughs'],
            ['name' => 'News', 'slug' => 'news', 'description' => 'Latest updates and announcements'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX and design inspiration'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], array_merge($cat, ['author_id' => $admin->id]));
        }

        // 2. Tags
        $tags = ['CMS', 'SaaS', 'Vue.js', 'Laravel', 'UI/UX', 'Premium'];
        foreach ($tags as $tag) {
            Tag::updateOrCreate(['slug' => Str::slug($tag)], [
                'name' => $tag,
                'author_id' => $admin->id
            ]);
        }

        // 3. Main Menu
        $mainMenu = Menu::updateOrCreate(['slug' => 'main-menu'], [
            'name' => 'Main Navigation',
            'location' => 'header',
            'is_active' => true
        ]);

        $menuItems = [
            ['title' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 2],
            ['title' => 'Services', 'url' => '/services', 'sort_order' => 3],
            ['title' => 'About', 'url' => '/about', 'sort_order' => 4],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 5],
        ];

        foreach ($menuItems as $item) {
            MenuItem::updateOrCreate([
                'menu_id' => $mainMenu->id,
                'title' => $item['title']
            ], $item);
        }

        // 4. Default Contact Form
        Form::updateOrCreate(['slug' => 'contact-form'], [
            'name' => 'Standard Contact Form',
            'description' => 'Basic contact form for general inquiries',
            'success_message' => 'Thank you! Your message has been sent successfully.',
            'is_active' => true,
            'author_id' => $admin->id,
            'settings' => [
                'email_notifications' => true,
                'notification_email' => 'admin@jejakawan.com'
            ]
        ]);

        $this->command->info('Studio structure seeded successfully!');
    }
}
