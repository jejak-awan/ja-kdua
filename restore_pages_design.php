<?php




echo "Restoring pages design...\n";

// ==========================================
// 1. HOME PAGE RESTORATION
// ==========================================
$home = \App\Models\Content::where('slug', 'home')->first();
if ($home) {
    echo "Restoring Home page...\n";
    $homeBlocks = [
        // HERO SECTION
        [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'section',
            'settings' => [
                'padding' => ['top' => 128, 'bottom' => 128, 'left' => 0, 'right' => 0, 'unit' => 'px'],
                'background' => [
                    'color' => '#ffffff',
                    'image' => '',
                    'repeat' => 'no-repeat',
                    'position' => 'center',
                    'size' => 'cover'
                ],
                'css' => [
                    'classes' => 'bg-gradient-to-b from-primary/10 via-background to-background dark:from-primary/20'
                ]
            ],
            'children' => [
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'row',
                    'settings' => [],
                    'children' => [
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1, 'align' => 'center'],
                            'children' => [
                                // Badge
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<div class="text-center mb-8"><span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold bg-primary/10 text-primary ring-1 ring-inset ring-primary/20 backdrop-blur-sm">🚀 v1.0 Janari Edition</span></div>',
                                        'align' => 'center'
                                    ]
                                ],
                                // H1
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'heading',
                                    'settings' => [
                                        'content' => 'JA-CMS',
                                        'level' => 'h1',
                                        'align' => 'center',
                                        'typography' => ['fontSize' => '72px', 'fontWeight' => '700', 'lineHeight' => '1.1']
                                    ]
                                ],
                                // Subtitle
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<p class="text-xl md:text-2xl text-muted-foreground max-w-3xl mx-auto mb-4 font-medium text-center">Modern Content Management System</p><p class="text-lg text-muted-foreground max-w-2xl mx-auto mb-10 text-center">Built with Laravel & Vue.js for speed, flexibility, and a premium developer experience.</p>',
                                        'align' => 'center'
                                    ]
                                ],
                                // Buttons
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                                            <a href="/register" class="rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg hover:bg-primary/90 transition-all duration-300 hover:scale-105 hover:shadow-xl">Get Started Free</a>
                                            <a href="/about" class="rounded-full border border-border px-8 py-4 text-sm font-semibold text-foreground hover:bg-muted transition-all duration-300 flex items-center gap-2">Learn More <span class="text-primary">→</span></a>
                                        </div>',
                                        'align' => 'center'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],

        // ABOUT SECTION (Simplified)
        [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'section',
            'settings' => [
                'padding' => ['top' => 96, 'bottom' => 96, 'left' => 0, 'right' => 0, 'unit' => 'px'],
                'css' => ['classes' => 'bg-muted/50 dark:bg-muted/20']
            ],
            'children' => [
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'row',
                    'settings' => [],
                    'children' => [
                        // Left Column
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1],
                            'children' => [
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<span class="text-primary font-bold tracking-wider uppercase text-sm">Tentang JA-CMS</span>
                                        <h2 class="text-4xl font-bold text-foreground mt-2 mb-6">CMS Modern untuk Era Digital</h2>
                                        <p class="text-lg text-muted-foreground leading-relaxed mb-4">JA-CMS adalah Content Management System generasi baru yang dibangun dengan teknologi terkini. Menggabungkan kekuatan Laravel di backend dan Vue.js di frontend untuk pengalaman pengembangan yang seamless.</p>'
                                    ]
                                ]
                            ]
                        ],
                        // Right Column
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1],
                            'children' => [
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<div class="p-8 bg-card rounded-2xl border border-border shadow-lg">
                                            <pre class="text-sm text-muted-foreground font-mono overflow-x-auto"><code>// Create content with ease
const content = await Content.create({
  title: \'Welcome to JA-CMS\',
  type: \'page\',
  status: \'published\',
  blocks: [...]
});</code></pre></div>'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],

        // FEATURES SECTION
        [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'section',
            'settings' => [
                'padding' => ['top' => 96, 'bottom' => 96, 'left' => 0, 'right' => 0, 'unit' => 'px'],
                'css' => ['classes' => 'bg-background']
            ],
            'children' => [
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'row',
                    'settings' => [],
                    'children' => [
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1, 'align' => 'center'],
                            'children' => [
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'heading',
                                    'settings' => ['content' => 'Fitur Powerful', 'level' => 'h2', 'align'=>'center']
                                ],
                                [
                                    'id' => (string) \Illuminate\Support\Str::uuid(),
                                    'type' => 'text',
                                    'settings' => ['content' => '<p class="text-muted-foreground max-w-2xl mx-auto text-center">Everything you need to build and manage modern websites</p>', 'align'=>'center']
                                ]
                            ]
                        ]
                    ]
                ],
                // Modules
                [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'row',
                    'settings' => [],
                    'children' => [
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1],
                            'children' => [
                                ['id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="p-8 rounded-2xl bg-card border border-border shadow-sm"><h3 class="text-xl font-bold mb-3">Blazing Fast</h3><p class="text-muted-foreground">Optimized for speed with smart caching and lazy loading.</p></div>']]
                            ]
                        ],
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1],
                            'children' => [
                                ['id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="p-8 rounded-2xl bg-card border border-border shadow-sm"><h3 class="text-xl font-bold mb-3">Theme System</h3><p class="text-muted-foreground">Flexible theming with dark mode and custom colors.</p></div>']]
                            ]
                        ],
                        [
                            'id' => (string) \Illuminate\Support\Str::uuid(),
                            'type' => 'column',
                            'settings' => ['flexGrow' => 1],
                            'children' => [
                                ['id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="p-8 rounded-2xl bg-card border border-border shadow-sm"><h3 class="text-xl font-bold mb-3">Block Builder</h3><p class="text-muted-foreground">Visual page builder with drag-and-drop blocks.</p></div>']]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $home->blocks = $homeBlocks;
    $home->editor_type = 'builder';
    $home->save();
    echo "Home page restored.\n";
} else {
    echo "Home page not found.\n";
}

// ==========================================
// 2. BLOG PAGE RESTORATION
// ==========================================
// Check if Blog page exists, if not create it
$blog = \App\Models\Content::where('slug', 'blog')->first();
if (!$blog) {
    echo "Creating Blog page...\n";
    $blog = new \App\Models\Content();
    $blog->title = 'Blog';
    $blog->slug = 'blog';
    $blog->type = 'page';
    $blog->status = 'published';
    $blog->author_id = 1; // Assign to first user/admin
    $blog->published_at = now();
}

$blogBlocks = [
    // BLOG HERO
    [
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'section',
        'settings' => [
            'padding' => ['top' => 96, 'bottom' => 96, 'left' => 0, 'right' => 0, 'unit' => 'px'],
            'css' => ['classes' => 'bg-gradient-to-b from-primary/10 to-background dark:from-primary/20']
        ],
        'children' => [
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1, 'align' => 'center'],
                        'children' => [
                            [
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'type' => 'text',
                                'settings' => [
                                    'content' => '<span class="text-primary font-bold tracking-wider uppercase text-xs mb-4 block text-center">Blog JA-CMS</span>',
                                    'align' => 'center'
                                ]
                            ],
                            [
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'type' => 'heading',
                                'settings' => [
                                    'content' => 'The Journal',
                                    'level' => 'h1',
                                    'align' => 'center',
                                    'typography' => ['fontSize' => '48px', 'fontWeight' => '700']
                                ]
                            ],
                            [
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'type' => 'text',
                                'settings' => [
                                    'content' => '<p class="text-muted-foreground max-w-lg mx-auto text-center">Insights, tutorials, dan update terbaru dari tim JA-CMS.</p>',
                                    'align' => 'center'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],

    // BLOG POSTS GRID
    [
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'section',
        'settings' => [
            'padding' => ['top' => 60, 'bottom' => 60, 'left' => 0, 'right' => 0, 'unit' => 'px']
        ],
        'children' => [
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => [
                            [
                                'id' => (string) \Illuminate\Support\Str::uuid(),
                                'type' => 'blog',
                                'settings' => [
                                    'postsPerPage' => 9,
                                    'columns' => 3,
                                    'layout' => 'grid',
                                    'gap' => 24,
                                    'showImage' => true,
                                    'showExcerpt' => true,
                                    'showDate' => true,
                                    'showAuthor' => true,
                                    'showCategory' => true
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

$blog->blocks = $blogBlocks;
$blog->editor_type = 'builder';
$blog->save();
echo "Blog page restored/created.\n";
