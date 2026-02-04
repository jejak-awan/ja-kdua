<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Content;
use App\Models\NewsletterSubscriber;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the sample data seeds for development.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@jejakawan.com')->first();
        if (! $admin) {
            return;
        }

        $categories = Category::all();
        $tags = Tag::all();

        // 1. Sample Blog Posts
        $posts = [
            [
                'title' => 'The Future of AI in Content Management',
                'slug' => 'future-of-ai-cms',
                'excerpt' => 'How machine learning is revolutionizing the way we create and manage web content.',
                'body' => '<p>Artificial Intelligence is no longer just a buzzword. In the realm of Content Management Systems (CMS), AI is driving significant efficiencies...</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'Mastering the Ja-CMS Visual Builder',
                'slug' => 'mastering-visual-builder',
                'excerpt' => 'A comprehensive guide to building beautiful layouts without writing a single line of code.',
                'body' => '<p>The Ja-CMS block builder offers unparalleled flexibility. By combining different structural and content blocks, you can create anything from a simple blog to a complex landing page...</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Top 10 Design Trends for 2026',
                'slug' => 'design-trends-2026',
                'excerpt' => 'Stay ahead of the curve with these emerging web design patterns and aesthetics.',
                'body' => '<p>From glassmorphism to advanced micro-interactions, 2026 is shaping up to be a year of bold experiments in visual storytelling...</p>',
                'is_featured' => true,
            ],
        ];

        foreach ($posts as $postData) {
            $content = Content::updateOrCreate(
                ['slug' => $postData['slug']],
                array_merge($postData, [
                    'author_id' => $admin->id,
                    'category_id' => $categories->random()->id,
                    'status' => 'published',
                    'published_at' => now()->subDays(rand(1, 30)),
                ])
            );

            // Attach random tags
            $content->tags()->sync($tags->random(rand(2, 4))->pluck('id'));

            // Add sample comments
            for ($i = 0; $i < rand(1, 4); $i++) {
                Comment::create([
                    'content_id' => $content->id,
                    'name' => 'Sample Commenter '.($i + 1),
                    'email' => 'commenter'.($i + 1).'@example.com',
                    'body' => 'This is a great article on '.$postData['title'].'! Very helpful.',
                    'status' => 'approved',
                ]);
            }
        }

        // 2. Sample Newsletter Subscribers
        for ($i = 0; $i < 10; $i++) {
            NewsletterSubscriber::updateOrCreate(
                ['email' => "user{$i}@example.com"],
                [
                    'name' => "Sample User {$i}",
                    'status' => 'subscribed',
                    'subscribed_at' => now()->subDays(rand(1, 100)),
                ]
            );
        }

        // 3. Sample Form Submissions
        $contactForm = \App\Models\Form::where('slug', 'contact-form')->first();
        if ($contactForm) {
            $submissions = [
                [
                    'data' => ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'message' => 'I would like to inquire about your enterprise pricing.'],
                    'ip_address' => '192.168.1.10',
                    'created_at' => now()->subHours(2),
                ],
                [
                    'data' => ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'message' => 'Great CMS! How do I build a custom theme?'],
                    'ip_address' => '192.168.1.11',
                    'created_at' => now()->subDays(1),
                ],
                [
                    'data' => ['name' => 'Charlie Day', 'email' => 'charlie@example.com', 'message' => 'Found a bug in the mobile menu.'],
                    'ip_address' => '192.168.1.12',
                    'created_at' => now()->subDays(3),
                ],
            ];

            foreach ($submissions as $sub) {
                \App\Models\FormSubmission::create([
                    'form_id' => $contactForm->id,
                    'data' => $sub['data'],
                    'ip_address' => $sub['ip_address'],
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                    'status' => 'new',
                    'created_at' => $sub['created_at'],
                    'updated_at' => $sub['created_at'],
                ]);
            }
        }

        $this->command->info('Sample data seeded successfully!');
    }
}
