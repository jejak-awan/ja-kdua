<?php

use App\Models\Content;
use Illuminate\Support\Str;

$page = Content::where('slug', 'home')->first();

if (!$page) {
    echo "Home page not found!\n";
    exit;
}

echo "Restoring Home page design...\n";

$blocks = [
    // HERO SECTION
    [
        'id' => (string) Str::uuid(),
        'type' => 'section',
        'settings' => [
            'padding' => ['top' => 128, 'bottom' => 128, 'left' => 0, 'right' => 0, 'unit' => 'px'],
            'background' => [
                'color' => '#ffffff', // Simplified, original used complex gradient classes
                'image' => '',
                'repeat' => 'no-repeat',
                'position' => 'center',
                'size' => 'cover'
            ],
             // We can't easily replicate complex Tailwind gradients via simple settings yet without custom CSS class support
             // identifying if we can add a custom class? 
             'css' => [
                 'classes' => 'bg-gradient-to-b from-primary/10 via-background to-background dark:from-primary/20'
             ]
        ],
        'children' => [
            [
                'id' => (string) Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1, 'align' => 'center'],
                        'children' => [
                            // Badge
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'text',
                                'settings' => [
                                    'content' => '<span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold bg-primary/10 text-primary mb-8 ring-1 ring-inset ring-primary/20 backdrop-blur-sm">🚀 v1.0 Janari Edition</span>',
                                    'align' => 'center'
                                ]
                            ],
                            // H1
                            [
                                'id' => (string) Str::uuid(),
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
                                'id' => (string) Str::uuid(),
                                'type' => 'text',
                                'settings' => [
                                    'content' => '<p class="text-xl md:text-2xl text-muted-foreground max-w-3xl mx-auto mb-4 font-medium">Modern Content Management System</p><p class="text-lg text-muted-foreground max-w-2xl mx-auto mb-10">Built with Laravel & Vue.js for speed, flexibility, and a premium developer experience.</p>',
                                    'align' => 'center'
                                ]
                            ],
                            // Buttons (using HTML for now to keep layout)
                            [
                                'id' => (string) Str::uuid(),
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

    // ABOUT SECTION
    [
        'id' => (string) Str::uuid(),
        'type' => 'section',
        'settings' => [
            'padding' => ['top' => 96, 'bottom' => 96, 'left' => 0, 'right' => 0, 'unit' => 'px'],
            'css' => ['classes' => 'bg-muted/50 dark:bg-muted/20']
        ],
        'children' => [
             [
                'id' => (string) Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    // Left Column (Text)
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => [
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'text',
                                'settings' => [
                                    'content' => '<span class="text-primary font-bold tracking-wider uppercase text-sm">Tentang JA-CMS</span>
                                    <h2 class="text-4xl font-bold text-foreground mt-2 mb-6">CMS Modern untuk Era Digital</h2>
                                    <p class="text-lg text-muted-foreground leading-relaxed mb-4">JA-CMS adalah Content Management System generasi baru yang dibangun dengan teknologi terkini. Menggabungkan kekuatan Laravel di backend dan Vue.js di frontend untuk pengalaman pengembangan yang seamless.</p>
                                    <p class="text-muted-foreground leading-relaxed mb-8">Dirancang untuk developer yang menginginkan kecepatan, fleksibilitas, dan kemudahan kustomisasi tanpa mengorbankan performa.</p>'
                                ]
                            ],
                            // Stats Row (Nested)
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'row',
                                'settings' => ['css' => ['classes' => 'pt-6 border-t border-border']],
                                'children' => [
                                    [
                                        'id' => (string) Str::uuid(),
                                        'type' => 'column',
                                        'settings' => ['flexGrow' => 1],
                                        'children' => [['id' => (string) Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="text-3xl font-bold text-primary">50+</div><div class="text-sm text-muted-foreground mt-1">Components</div>']]]
                                    ],
                                    [
                                        'id' => (string) Str::uuid(),
                                        'type' => 'column',
                                        'settings' => ['flexGrow' => 1],
                                        'children' => [['id' => (string) Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="text-3xl font-bold text-primary">10x</div><div class="text-sm text-muted-foreground mt-1">Faster</div>']]]
                                    ],
                                    [
                                        'id' => (string) Str::uuid(),
                                        'type' => 'column',
                                        'settings' => ['flexGrow' => 1],
                                        'children' => [['id' => (string) Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="text-3xl font-bold text-primary">100%</div><div class="text-sm text-muted-foreground mt-1">Open Source</div>']]]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    // Right Column (Code/Image)
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => [
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'text',
                                'settings' => [
                                    'content' => '<div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 to-transparent rounded-2xl transform rotate-3"></div>
                            <div class="relative rounded-2xl bg-card border border-border p-8 shadow-2xl">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                </div>
                                <pre class="text-sm text-muted-foreground font-mono overflow-x-auto"><code>// Create content with ease
const content = await Content.create({
  title: \'Welcome to JA-CMS\',
  type: \'page\',
  status: \'published\',
  blocks: [...]
});

// Blazing fast queries
Content::published()
  ->with(\'author\', \'tags\')
  ->paginate(12);</code></pre>
                            </div>
                        </div>'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    
    // FEATURES SECTION (Simplified for brevity, user can add more)
    [
        'id' => (string) Str::uuid(),
         'type' => 'section',
        'settings' => [
            'padding' => ['top' => 96, 'bottom' => 96, 'left' => 0, 'right' => 0, 'unit' => 'px'],
            'css' => ['classes' => 'bg-background']
        ],
        'children' => [
             [
                'id' => (string) Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                         'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1, 'align' => 'center'],
                        'children' => [
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'heading',
                                'settings' => ['content' => 'Fitur Powerful', 'level' => 'h2', 'align'=>'center']
                            ],
                            [
                                'id' => (string) Str::uuid(),
                                'type' => 'text',
                                'settings' => ['content' => '<p class="text-muted-foreground max-w-2xl mx-auto">Everything you need to build and manage modern websites</p>', 'align'=>'center']
                            ]
                        ]
                    ]
                ]
            ],
            // Feature Grid (3 cols)
            [
                'id' => (string) Str::uuid(),
                'type' => 'row',
                'settings' => [],
                'children' => [
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => [['id' => (string) Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="p-8 rounded-2xl bg-card border border-border shadow-sm"><h3 class="text-xl font-bold mb-3">Blazing Fast</h3><p class="text-muted-foreground">Optimized for speed with smart caching and lazy loading.</p></div>']]]
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => [['id' => (string) Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="p-8 rounded-2xl bg-card border border-border shadow-sm"><h3 class="text-xl font-bold mb-3">Theme System</h3><p class="text-muted-foreground">Flexible theming with dark mode and custom colors.</p></div>']]]
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'type' => 'column',
                        'settings' => ['flexGrow' => 1],
                        'children' => [['id' => (string) Str::uuid(), 'type' => 'text', 'settings' => ['content' => '<div class="p-8 rounded-2xl bg-card border border-border shadow-sm"><h3 class="text-xl font-bold mb-3">Block Builder</h3><p class="text-muted-foreground">Visual page builder with drag-and-drop blocks.</p></div>']]]
                    ]
                ]
            ]
        ]
    ]

];

$page->blocks = $blocks;
$page->save();

echo "Home page restored successfully.\n";
