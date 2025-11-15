<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Content;
use App\Models\Comment;
use Spatie\Permission\Models\Role;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample data...');

        // Create users
        $users = $this->createUsers();
        
        // Create categories
        $categories = $this->createCategories();
        
        // Create tags
        $tags = $this->createTags();
        
        // Create contents
        $contents = $this->createContents($users, $categories, $tags);
        
        // Create comments
        $this->createComments($contents, $users);

        $this->command->info('Sample data created successfully!');
    }

    private function createUsers()
    {
        $this->command->info('Creating users...');
        
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $authorRole = Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web']);

        $users = [];

        // Editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'John Editor',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $editor->assignRole($editorRole);
        $users[] = $editor;

        // Author
        $author = User::firstOrCreate(
            ['email' => 'author@example.com'],
            [
                'name' => 'Jane Author',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $author->assignRole($authorRole);
        $users[] = $author;

        // Get admin user
        $admin = User::where('email', 'admin@jejakawan.com')->first();
        if ($admin) {
            $users[] = $admin;
        }

        return $users;
    }

    private function createCategories()
    {
        $this->command->info('Creating categories...');
        
        $categories = [];

        // Main categories
        $tech = Category::firstOrCreate(
            ['slug' => 'technology'],
            [
                'name' => 'Technology',
                'description' => 'Latest technology news and updates',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
        $categories[] = $tech;

        $business = Category::firstOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business',
                'description' => 'Business news and insights',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
        $categories[] = $business;

        $lifestyle = Category::firstOrCreate(
            ['slug' => 'lifestyle'],
            [
                'name' => 'Lifestyle',
                'description' => 'Lifestyle tips and trends',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );
        $categories[] = $lifestyle;

        // Sub-categories
        $webDev = Category::firstOrCreate(
            ['slug' => 'web-development'],
            [
                'name' => 'Web Development',
                'description' => 'Web development tutorials and guides',
                'is_active' => true,
                'parent_id' => $tech->id,
                'sort_order' => 1,
            ]
        );
        $categories[] = $webDev;

        $mobileDev = Category::firstOrCreate(
            ['slug' => 'mobile-development'],
            [
                'name' => 'Mobile Development',
                'description' => 'Mobile app development',
                'is_active' => true,
                'parent_id' => $tech->id,
                'sort_order' => 2,
            ]
        );
        $categories[] = $mobileDev;

        $startup = Category::firstOrCreate(
            ['slug' => 'startup'],
            [
                'name' => 'Startup',
                'description' => 'Startup news and stories',
                'is_active' => true,
                'parent_id' => $business->id,
                'sort_order' => 1,
            ]
        );
        $categories[] = $startup;

        return $categories;
    }

    private function createTags()
    {
        $this->command->info('Creating tags...');
        
        $tagNames = [
            'PHP', 'Laravel', 'Vue.js', 'JavaScript', 'React',
            'Tutorial', 'Guide', 'Tips', 'News', 'Update',
            'Web Design', 'UI/UX', 'Mobile', 'API', 'Database',
            'Security', 'Performance', 'SEO', 'Marketing', 'Business'
        ];

        $tags = [];
        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                [
                    'name' => $tagName,
                    'description' => "Content tagged with {$tagName}",
                ]
            );
            $tags[] = $tag;
        }

        return $tags;
    }

    private function createContents($users, $categories, $tags)
    {
        $this->command->info('Creating contents...');
        
        $sampleContents = [
            [
                'title' => 'Getting Started with Laravel 11',
                'excerpt' => 'Learn the basics of Laravel 11 and build your first application with this comprehensive guide.',
                'body' => $this->getLongContent('Laravel 11'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'web-development',
                'tags' => ['Laravel', 'PHP', 'Tutorial', 'Guide'],
                'views' => 1250,
            ],
            [
                'title' => 'Vue.js 3 Composition API: A Complete Guide',
                'excerpt' => 'Master the Composition API in Vue.js 3 and build more maintainable applications.',
                'body' => $this->getLongContent('Vue.js 3'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'web-development',
                'tags' => ['Vue.js', 'JavaScript', 'Tutorial'],
                'views' => 980,
            ],
            [
                'title' => 'Building RESTful APIs with Laravel',
                'excerpt' => 'Create powerful and scalable REST APIs using Laravel framework.',
                'body' => $this->getLongContent('RESTful APIs'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'web-development',
                'tags' => ['Laravel', 'API', 'Backend', 'Guide'],
                'views' => 750,
            ],
            [
                'title' => 'Top 10 Startup Trends in 2024',
                'excerpt' => 'Discover the latest trends shaping the startup ecosystem this year.',
                'body' => $this->getLongContent('Startup Trends'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'startup',
                'tags' => ['Startup', 'Business', 'News', 'Trends'],
                'views' => 2100,
            ],
            [
                'title' => 'Mobile App Development: iOS vs Android',
                'excerpt' => 'Compare iOS and Android development approaches and choose the right platform.',
                'body' => $this->getLongContent('Mobile Development'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'mobile-development',
                'tags' => ['Mobile', 'iOS', 'Android', 'Development'],
                'views' => 1650,
            ],
            [
                'title' => 'Web Security Best Practices',
                'excerpt' => 'Essential security practices every web developer should know.',
                'body' => $this->getLongContent('Web Security'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'web-development',
                'tags' => ['Security', 'Web Development', 'Best Practices'],
                'views' => 890,
            ],
            [
                'title' => 'SEO Optimization Tips for 2024',
                'excerpt' => 'Modern SEO strategies to improve your website\'s search engine rankings.',
                'body' => $this->getLongContent('SEO Optimization'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'business',
                'tags' => ['SEO', 'Marketing', 'Tips', 'Guide'],
                'views' => 1450,
            ],
            [
                'title' => 'React Hooks: A Comprehensive Guide',
                'excerpt' => 'Learn how to use React Hooks to build better React applications.',
                'body' => $this->getLongContent('React Hooks'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'web-development',
                'tags' => ['React', 'JavaScript', 'Tutorial'],
                'views' => 1120,
            ],
            [
                'title' => 'Database Design Principles',
                'excerpt' => 'Learn the fundamental principles of good database design.',
                'body' => $this->getLongContent('Database Design'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'web-development',
                'tags' => ['Database', 'Design', 'Best Practices'],
                'views' => 680,
            ],
            [
                'title' => 'Productivity Tips for Remote Workers',
                'excerpt' => 'Boost your productivity while working from home with these proven tips.',
                'body' => $this->getLongContent('Remote Work'),
                'type' => 'article',
                'status' => 'published',
                'category_slug' => 'lifestyle',
                'tags' => ['Productivity', 'Remote Work', 'Tips', 'Lifestyle'],
                'views' => 950,
            ],
            [
                'title' => 'Draft Article: Advanced Laravel Techniques',
                'excerpt' => 'This is a draft article about advanced Laravel techniques.',
                'body' => $this->getLongContent('Advanced Laravel'),
                'type' => 'article',
                'status' => 'draft',
                'category_slug' => 'web-development',
                'tags' => ['Laravel', 'Advanced'],
                'views' => 0,
            ],
            [
                'title' => 'About Us',
                'excerpt' => 'Learn more about our company and mission.',
                'body' => '<p>Welcome to our website! We are dedicated to providing quality content and services.</p><p>Our mission is to help people learn and grow through technology and innovation.</p>',
                'type' => 'page',
                'status' => 'published',
                'category_slug' => null,
                'tags' => [],
                'views' => 320,
            ],
        ];

        $contents = [];
        foreach ($sampleContents as $index => $contentData) {
            $category = $contentData['category_slug'] 
                ? Category::where('slug', $contentData['category_slug'])->first()
                : null;

            $author = $users[array_rand($users)];

            $content = Content::firstOrCreate(
                ['slug' => Str::slug($contentData['title'])],
                [
                    'title' => $contentData['title'],
                    'excerpt' => $contentData['excerpt'],
                    'body' => $contentData['body'],
                    'type' => $contentData['type'],
                    'status' => $contentData['status'],
                    'author_id' => $author->id,
                    'category_id' => $category?->id,
                    'published_at' => $contentData['status'] === 'published' 
                        ? now()->subDays(rand(1, 90)) 
                        : null,
                    'views' => $contentData['views'],
                    'meta_title' => $contentData['title'],
                    'meta_description' => $contentData['excerpt'],
                ]
            );

            // Attach tags
            if (!empty($contentData['tags'])) {
                $tagIds = [];
                foreach ($contentData['tags'] as $tagName) {
                    $tag = Tag::where('name', $tagName)->first();
                    if ($tag) {
                        $tagIds[] = $tag->id;
                    }
                }
                $content->tags()->sync($tagIds);
            }

            $contents[] = $content;
        }

        return $contents;
    }

    private function createComments($contents, $users)
    {
        $this->command->info('Creating comments...');
        
        $publishedContents = array_filter($contents, function($content) {
            return $content->status === 'published';
        });

        $commentTexts = [
            'Great article! Very helpful.',
            'Thanks for sharing this. I learned a lot.',
            'This is exactly what I was looking for.',
            'Could you provide more examples?',
            'Excellent explanation. Keep up the good work!',
            'I have a question about the third point.',
            'This helped me solve my problem. Thank you!',
            'Very informative. Looking forward to more content.',
            'I disagree with some points, but overall good article.',
            'Perfect timing! I needed this information.',
        ];

        foreach ($publishedContents as $content) {
            // Create 2-5 comments per content
            $commentCount = rand(2, 5);
            
            for ($i = 0; $i < $commentCount; $i++) {
                $user = $users[array_rand($users)];
                $commentText = $commentTexts[array_rand($commentTexts)];
                
                Comment::create([
                    'content_id' => $content->id,
                    'user_id' => $user->id,
                    'body' => $commentText,
                    'status' => rand(0, 10) > 1 ? 'approved' : 'pending', // 90% approved
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }

    private function getLongContent($topic)
    {
        return "
        <h2>Introduction to {$topic}</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        
        <h3>Key Features</h3>
        <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        
        <ul>
            <li>Feature one: Sed ut perspiciatis unde omnis iste natus error</li>
            <li>Feature two: Sit voluptatem accusantium doloremque laudantium</li>
            <li>Feature three: Totam rem aperiam, eaque ipsa quae ab illo</li>
        </ul>
        
        <h3>Getting Started</h3>
        <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
        
        <pre><code>// Example code
function example() {
    return 'Hello World';
}</code></pre>
        
        <h3>Conclusion</h3>
        <p>Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio.</p>
        ";
    }
}
