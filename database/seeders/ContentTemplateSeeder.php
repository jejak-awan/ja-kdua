<?php

namespace Database\Seeders;

use App\Models\ContentTemplate;
use Illuminate\Database\Seeder;

class ContentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Standard Blog Post',
                'slug' => 'standard-blog-post',
                'description' => 'A standard layout for blog articles with introduction, body, and conclusion.',
                'type' => 'post',
                'title_template' => '{{ title }}',
                'excerpt_template' => '{{ excerpt }}',
                'body_template' => '
                    <h2>Introduction</h2>
                    <p>Write your introduction here...</p>
                    
                    <h2>Key Points</h2>
                    <ul>
                        <li>Point 1</li>
                        <li>Point 2</li>
                        <li>Point 3</li>
                    </ul>
                    
                    <h2>Conclusion</h2>
                    <p>Summarize your thoughts here...</p>
                ',
                'is_active' => true,
                'default_fields' => [],
            ],
            [
                'name' => 'Product Launch Announcement',
                'slug' => 'product-launch-announcement',
                'description' => 'Template for announcing new products or features.',
                'type' => 'post',
                'title_template' => 'Announcing: {{ product_name }}',
                'excerpt_template' => 'We are excited to announce our new product...',
                'body_template' => '
                    <h2>Introducing {{ product_name }}</h2>
                    <p>We are thrilled to unveil our latest innovation...</p>
                    
                    <h3>What is it?</h3>
                    <p>Description of the product...</p>
                    
                    <h3>Key Features</h3>
                    <ul>
                        <li>Feature A</li>
                        <li>Feature B</li>
                        <li>Feature C</li>
                    </ul>
                    
                    <h3>Pricing and Availability</h3>
                    <p>Details about pricing...</p>
                ',
                'is_active' => true,
                'default_fields' => [
                    'product_name' => '',
                    'launch_date' => '',
                ],
            ],
            [
                'name' => 'Knowledge Base Article',
                'slug' => 'knowledge-base-article',
                'description' => 'Structure for documentation and help articles.',
                'type' => 'page',
                'title_template' => 'How to {{ action }}',
                'excerpt_template' => 'Learn how to {{ action }} with this step-by-step guide.',
                'body_template' => '
                    <h2>Overview</h2>
                    <p>Brief explanation of the task...</p>
                    
                    <h2>Prerequisites</h2>
                    <ul>
                        <li>Requirement 1</li>
                        <li>Requirement 2</li>
                    </ul>
                    
                    <h2>Step-by-Step Guide</h2>
                    <ol>
                        <li>Step one details...</li>
                        <li>Step two details...</li>
                        <li>Step three details...</li>
                    </ol>
                    
                    <h2>Troubleshooting</h2>
                    <p>Common issues and fixes...</p>
                ',
                'is_active' => true,
                'default_fields' => [
                    'action' => 'do something',
                ],
            ],
            [
                'name' => 'Landing Page - Modern',
                'slug' => 'landing-page-modern',
                'description' => 'A modern landing page structure with hero section and features.',
                'type' => 'page',
                'title_template' => '{{ campaign_name }}',
                'excerpt_template' => 'Welcome to our specialized landing page.',
                'body_template' => "
                    <div class='hero-section'>
                        <h1>Headline</h1>
                        <p class='subtitle'>Subheadline goes here</p>
                        <button>Call to Action</button>
                    </div>
                    
                    <div class='features-grid'>
                        <div class='feature'>
                            <h3>Benefit 1</h3>
                            <p>Description...</p>
                        </div>
                        <div class='feature'>
                            <h3>Benefit 2</h3>
                            <p>Description...</p>
                        </div>
                    </div>
                ",
                'is_active' => true,
                'default_fields' => [
                    'campaign_name' => 'New Campaign',
                ],
            ],
            [
                'name' => 'Weekly Newsletter',
                'slug' => 'weekly-newsletter',
                'description' => 'Template for weekly email digests or updates.',
                'type' => 'custom',
                'title_template' => 'Weekly Digest: {{ date }}',
                'excerpt_template' => 'Here are the top stories for this week.',
                'body_template' => "
                    <h2>Top Stories</h2>
                    <ul>
                        <li><a href='#'>Story 1</a> - Summary...</li>
                        <li><a href='#'>Story 2</a> - Summary...</li>
                    </ul>
                    
                    <h2>Community Highlights</h2>
                    <p>What's happening in our community...</p>
                    
                    <h2>Upcoming Events</h2>
                    <p>Save the date for...</p>
                ",
                'is_active' => true,
                'default_fields' => [
                    'date' => date('Y-m-d'),
                ],
            ],
        ];

        // Builder Templates
        $builderTemplates = [
            [
                'name' => 'Modern Hero Section',
                'slug' => 'modern-hero-section',
                'description' => 'High-impact hero section with background image and dual buttons.',
                'type' => 'section',
                'body_template' => json_encode([
                    [
                        'id' => 'hero-seed-1',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'Build Faster with JA-CMS',
                            'subtitle' => 'The ultimate visual builder for modern web applications.',
                            'primaryButtonText' => 'Get Started',
                            'primaryButtonUrl' => '#',
                            'secondaryButtonText' => 'Live Demo',
                            'secondaryButtonUrl' => '#',
                            'align' => 'center',
                            'padding' => 'py-24',
                            'theme' => 'dark',
                        ],
                    ],
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Feature Grid options',
                'slug' => 'feature-grid-3-col',
                'description' => 'Three-column feature grid with icons.',
                'type' => 'section',
                'body_template' => json_encode([
                    [
                        'id' => 'features-seed-1',
                        'type' => 'feature-grid',
                        'settings' => [
                            'columns' => 3,
                            'items' => [
                                ['title' => 'Visual Editing', 'description' => 'Edit content directly on the page.', 'icon' => 'edit'],
                                ['title' => 'Responsive', 'description' => 'Looks great on all devices.', 'icon' => 'smartphone'],
                                ['title' => 'Fast Performance', 'description' => 'Optimized for speed and SEO.', 'icon' => 'zap'],
                            ],
                        ],
                    ],
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'SaaS Landing Page',
                'slug' => 'saas-landing-page',
                'description' => 'Complete landing page layout for software products.',
                'type' => 'builder',
                'body_template' => json_encode([
                    [
                        'id' => 'page-hero',
                        'type' => 'hero',
                        'settings' => [
                            'title' => 'Launch Your SaaS Today',
                            'subtitle' => 'Everything you need to build and scale.',
                            'primaryButtonText' => 'Start Free Trial',
                            'align' => 'center',
                            'padding' => 'py-32',
                        ],
                    ],
                    [
                        'id' => 'page-features',
                        'type' => 'feature-grid',
                        'settings' => [
                            'columns' => 3,
                            'title' => 'Why Choose Us?',
                            'items' => [
                                ['title' => 'Scalable', 'description' => 'Grows with your business.', 'icon' => 'trending-up'],
                                ['title' => 'Secure', 'description' => 'Enterprise-grade security.', 'icon' => 'shield'],
                                ['title' => 'Support', 'description' => '24/7 expert support.', 'icon' => 'life-buoy'],
                            ],
                        ],
                    ],
                    [
                        'id' => 'page-cta',
                        'type' => 'cta',
                        'settings' => [
                            'title' => 'Ready to get started?',
                            'buttonText' => 'Create Account',
                            'buttonUrl' => '/register',
                        ],
                    ],
                ]),
                'is_active' => true,
            ],
        ];

        $templates = array_merge($templates, $builderTemplates);

        foreach ($templates as $template) {
            ContentTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
