<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Content;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for testing.
     */
    public function run(): void
    {
        // Clear existing data
        $this->command->info('Clearing existing test data...');

        // Seed permissions and roles
        $this->seedPermissionsAndRoles();

        // Create test users
        $this->seedUsers();

        // Create test categories
        $this->seedCategories();

        // Create test tags
        $this->seedTags();

        // Create test media folders
        $this->seedMediaFolders();

        // Create test media
        $this->seedMedia();

        // Create test content
        $this->seedContent();

        // Create test comments
        $this->seedComments();

        $this->command->info('Test database seeded successfully!');
    }

    /**
     * Seed permissions and roles.
     */
    protected function seedPermissionsAndRoles(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage content', 'create content', 'edit content', 'delete content', 'publish content',
            'manage categories', 'manage tags', 'manage media', 'manage comments',
            'manage users', 'manage roles', 'manage permissions',
            'manage settings', 'manage forms', 'view analytics',
            'manage themes', 'manage plugins', 'manage menus', 'manage widgets',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->givePermissionTo([
            'create content', 'edit content', 'manage categories', 'manage tags', 'manage media',
        ]);

        $authorRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);
        $authorRole->givePermissionTo(['create content', 'edit content']);
    }

    /**
     * Seed test users.
     */
    protected function seedUsers(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Editor user
        $editor = User::firstOrCreate(
            ['email' => 'editor@test.com'],
            [
                'name' => 'Editor User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $editor->assignRole('editor');

        // Author user
        $author = User::firstOrCreate(
            ['email' => 'author@test.com'],
            [
                'name' => 'Author User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $author->assignRole('author');

        // Regular user (no special permissions)
        User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * Seed test categories.
     */
    protected function seedCategories(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Technology related content'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business related content'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'description' => 'Lifestyle related content'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    /**
     * Seed test tags.
     */
    protected function seedTags(): void
    {
        $tags = [
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'Vue.js', 'slug' => 'vuejs'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'Web Development', 'slug' => 'web-development'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(['slug' => $tag['slug']], $tag);
        }
    }

    /**
     * Seed test media folders.
     */
    protected function seedMediaFolders(): void
    {
        MediaFolder::firstOrCreate(
            ['slug' => 'images'],
            ['name' => 'Images', 'slug' => 'images']
        );

        MediaFolder::firstOrCreate(
            ['slug' => 'documents'],
            ['name' => 'Documents', 'slug' => 'documents']
        );
    }

    /**
     * Seed test media.
     */
    protected function seedMedia(): void
    {
        // This would typically create actual files, but for testing we'll just create records
        // In actual tests, you might want to use Storage::fake() or similar
    }

    /**
     * Seed test content.
     */
    protected function seedContent(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        $category = Category::first();
        $tags = Tag::take(3)->get();

        if ($admin && $category) {
            Content::firstOrCreate(
                ['slug' => 'test-article-1'],
                [
                    'title' => 'Test Article 1',
                    'slug' => 'test-article-1',
                    'body' => 'This is a test article body content.',
                    'excerpt' => 'Test article excerpt',
                    'status' => 'published',
                    'author_id' => $admin->id,
                    'category_id' => $category->id,
                    'published_at' => now(),
                ]
            )->tags()->sync($tags->pluck('id'));
        }
    }

    /**
     * Seed test comments.
     */
    protected function seedComments(): void
    {
        $content = Content::first();
        $user = User::where('email', 'user@test.com')->first();

        if ($content && $user) {
            Comment::firstOrCreate(
                [
                    'content_id' => $content->id,
                    'user_id' => $user->id,
                ],
                [
                    'body' => 'This is a test comment.',
                    'status' => 'approved',
                ]
            );
        }
    }
}
