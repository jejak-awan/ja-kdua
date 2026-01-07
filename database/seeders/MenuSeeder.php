<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Main Menu (Header)
        $mainMenu = Menu::firstOrCreate(
            ['location' => 'header'],
            [
                'name' => 'Header Main Menu',
                'slug' => 'header-main-menu',
            ]
        );

        // Clear existing items to avoid duplicates if re-run
        $mainMenu->items()->delete();

        // Home Link
        MenuItem::create([
            'menu_id' => $mainMenu->id,
            'title' => 'Home',
            'type' => 'url',
            'url' => '/',
            'sort_order' => 1,
            'open_in_new_tab' => false,
        ]);

        // "About Us" Page (if exists)
        $aboutPage = Content::where('type', 'page')->where('status', 'published')->first();
        if ($aboutPage) {
            MenuItem::create([
                'menu_id' => $mainMenu->id,
                'title' => $aboutPage->title,
                'type' => 'page',
                'target_id' => $aboutPage->id,
                'target_type' => Content::class,
                'sort_order' => 2,
            ]);
        }

        // Blog (Category Dropdown)
        $blogParent = MenuItem::create([
            'menu_id' => $mainMenu->id,
            'title' => 'Blog',
            'type' => 'url',
            'url' => '#',
            'sort_order' => 3,
        ]);

        // Add some categories as children
        $categories = Category::where('is_active', true)->take(3)->get();
        foreach ($categories as $index => $category) {
            MenuItem::create([
                'menu_id' => $mainMenu->id,
                'parent_id' => $blogParent->id,
                'title' => $category->name,
                'type' => 'category',
                'target_id' => $category->id,
                'target_type' => Category::class,
                'sort_order' => $index + 1,
            ]);
        }

        // Generic "Contact" Link
        MenuItem::create([
            'menu_id' => $mainMenu->id,
            'title' => 'Contact',
            'type' => 'url',
            'url' => '/contact',
            'sort_order' => 4,
        ]);

        // 2. Create Footer Menu
        $footerMenu = Menu::firstOrCreate(
            ['location' => 'footer'],
            [
                'name' => 'Footer Main Menu',
                'slug' => 'footer-main-menu',
            ]
        );
        $footerMenu->items()->delete();

        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'title' => 'Privacy Policy',
            'type' => 'url',
            'url' => '/privacy-policy',
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'menu_id' => $footerMenu->id,
            'title' => 'Terms of Service',
            'type' => 'url',
            'url' => '/terms-of-service',
            'sort_order' => 2,
        ]);

        // 3. Create Sidebar Menu
        $sidebarMenu = Menu::firstOrCreate(
            ['location' => 'sidebar'],
            [
                'name' => 'Sidebar Menu',
                'slug' => 'sidebar-menu',
            ]
        );
        $sidebarMenu->items()->delete();

        // Add some random posts to sidebar
        $posts = Content::where('type', 'post')->where('status', 'published')->take(5)->get();
        foreach ($posts as $index => $post) {
            MenuItem::create([
                'menu_id' => $sidebarMenu->id,
                'title' => $post->title,
                'type' => 'post',
                'target_id' => $post->id,
                'target_type' => Content::class,
                'sort_order' => $index + 1,
            ]);
        }

        $this->command->info('Menus seeded successfully!');
    }
}
