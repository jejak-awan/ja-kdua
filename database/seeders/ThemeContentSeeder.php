<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Content;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ThemeContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating Janari Theme pages...');

        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        // 1. Categories & Tags
        $categories = $this->createCategories();
        $tags = $this->createTags();

        // 2. Menus
        $this->createMenus();

        // 3. Pages
        $this->createHomePage($user);
        $this->createAboutPage($user);
        $this->createContactPage($user);

        // 4. Blog Posts
        $this->createBlogPosts($user, $categories, $tags);

        $this->command->info('Theme content created successfully!');
    }

    private function createCategories(): array
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest tech news and tutorials'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX and design inspiration'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business insights and strategies'],
            ['name' => 'Tutorial', 'slug' => 'tutorial', 'description' => 'Step-by-step guides'],
        ];

        $result = [];
        foreach ($categories as $cat) {
            $result[$cat['slug']] = Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
        return $result;
    }

    private function createTags(): array
    {
        $tagNames = ['Laravel', 'Vue.js', 'CMS', 'Web Development', 'Design', 'Tutorial', 'Guide', 'Tips'];
        $result = [];
        foreach ($tagNames as $name) {
            $slug = Str::slug($name);
            $result[] = Tag::firstOrCreate(['slug' => $slug], ['name' => $name, 'slug' => $slug]);
        }
        return $result;
    }

    private function createMenus(): void
    {
        // Header
        $header = Menu::firstOrCreate(['location' => 'header'], ['name' => 'Main Navigation', 'slug' => 'main-nav', 'location' => 'header']);
        MenuItem::where('menu_id', $header->id)->delete();
        
        $items = [
            ['title' => 'Home', 'url' => '/', 'sort_order' => 0],
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 1],
            ['title' => 'About', 'url' => '/about', 'sort_order' => 2],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 3],
        ];
        foreach ($items as $item) MenuItem::create(array_merge($item, ['menu_id' => $header->id, 'type' => 'custom']));

        // Header Home (Clone of header for compatibility)
        $headerHome = Menu::firstOrCreate(['location' => 'header_home'], ['name' => 'Home Navigation', 'slug' => 'home-nav', 'location' => 'header_home']);
        MenuItem::where('menu_id', $headerHome->id)->delete();
        foreach ($items as $item) MenuItem::create(array_merge($item, ['menu_id' => $headerHome->id, 'type' => 'custom']));

        // Footer 1
        $f1 = Menu::firstOrCreate(['location' => 'footer_col_1'], ['name' => 'Quick Links', 'slug' => 'footer-1', 'location' => 'footer_col_1']);
        MenuItem::where('menu_id', $f1->id)->delete();
        foreach ($items as $item) MenuItem::create(array_merge($item, ['menu_id' => $f1->id, 'type' => 'custom']));

        // Footer 2
        $f2 = Menu::firstOrCreate(['location' => 'footer_col_2'], ['name' => 'Resources', 'slug' => 'footer-2', 'location' => 'footer_col_2']);
        MenuItem::where('menu_id', $f2->id)->delete();
        $resItems = [
            ['title' => 'Documentation', 'url' => '/docs', 'sort_order' => 0],
            ['title' => 'Support', 'url' => '/support', 'sort_order' => 1],
            ['title' => 'Privacy', 'url' => '/privacy', 'sort_order' => 2],
        ];
        foreach ($resItems as $item) MenuItem::create(array_merge($item, ['menu_id' => $f2->id, 'type' => 'custom']));
    }

    private function createHomePage($user): void
    {
        $blocks = [
            // HERO SECTION
            [
                'id' => Str::uuid(),
                'type' => 'section',
                'settings' => [
                    'padding' => ['top' => '0px', 'right' => '0px', 'bottom' => '0px', 'left' => '0px'],
                    'minHeight' => 0,
                    'verticalAlign' => 'start',
                    'bgColor' => 'transparent',
                    '_custom_css' => ['overflow' => 'hidden', 'position' => 'relative'] // For absolute blobs
                ],
                'settings' => [
                    'blocks' => [
                        // 1. Background Blobs (Using HtmlBlock)
                        [
                            'id' => Str::uuid(),
                            'type' => 'html',
                            'settings' => [
                                'content' => '<div class="absolute inset-0 -z-10 overflow-hidden"><div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[800px] h-[600px] rounded-full bg-primary/20 dark:bg-primary/30 blur-3xl"></div><div class="absolute top-1/4 -left-32 w-96 h-96 rounded-full bg-violet-500/20 dark:bg-violet-500/30 blur-3xl"></div><div class="absolute top-1/3 -right-32 w-96 h-96 rounded-full bg-rose-500/20 dark:bg-rose-500/30 blur-3xl"></div></div>',
                                '_position' => 'absolute', // Ensure it sits behind
                                '_z_index' => 0,
                                'padding' => 'py-0'
                            ]
                        ],
                        // 2. Hero Content (Text + Buttons)
                        [
                            'id' => Str::uuid(),
                            'type' => 'container',
                            'settings' => [
                                'maxWidth' => 1000,
                                'padding' => ['top' => '128px', 'bottom' => '128px', 'left' => '16px', 'right' => '16px'],
                                'containerAlignment' => 'center',
                                '_z_index' => 10
                            ],
                            'settings' => [
                                'blocks' => [
                                    // Badge
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'html',
                                        'settings' => [
                                            'content' => '<div class="text-center mb-8"><span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold bg-primary/10 text-primary ring-1 ring-inset ring-primary/20 backdrop-blur-sm">🚀 v1.0 Janari Edition</span></div>'
                                        ]
                                    ],
                                    // Title
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'heading',
                                        'settings' => [
                                            'level' => 'h1',
                                            'content' => 'JA-CMS',
                                            'fontSize' => 72,
                                            'fontWeight' => '700',
                                            'align' => 'center',
                                            'marginBottom' => 24
                                        ]
                                    ],
                                    // Subtitle
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'text',
                                        'settings' => [
                                            'content' => '<p class="text-xl md:text-2xl text-muted-foreground font-medium">Modern Content Management System</p><p class="text-lg text-muted-foreground mt-4">Built with Laravel & Vue.js for speed, flexibility, and a premium developer experience.</p>',
                                            'alignment' => 'center',
                                            'maxWidth' => 800
                                        ]
                                    ],
                                    // Buttons
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'html', // Using HTML for exact button layout/style matching Home.vue
                                        'settings' => [
                                            'content' => '<div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-10"><a href="/register" class="rounded-full bg-primary px-8 py-4 text-sm font-semibold text-primary-foreground shadow-lg hover:bg-primary/90 transition-all duration-300 hover:scale-105 hover:shadow-xl">Get Started Free</a><a href="/about" class="rounded-full border border-border px-8 py-4 text-sm font-semibold text-foreground hover:bg-muted transition-all duration-300 flex items-center gap-2">Learn More <span class="text-primary">→</span></a></div>'
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
                'id' => Str::uuid(),
                'type' => 'section',
                'settings' => [
                    'bgColor' => 'transparent', // Using muted via nested columns or container
                    'padding' => ['top' => '96px', 'bottom' => '96px', 'left' => '0', 'right' => '0'],
                    '_css_class' => 'bg-muted/50 dark:bg-muted/20'
                ],
                'settings' => [
                    'blocks' => [
                        [
                            'id' => Str::uuid(),
                            'type' => 'container',
                            'settings' => [
                                'maxWidth' => 1200,
                            ],
                            'settings' => [
                                'blocks' => [
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'columns',
                                        'settings' => [
                                            'columns' => [50, 50],
                                            'gap' => 64,
                                        ],
                                        'settings' => [
                                            'blocks' => [
                                                // Col 1: Text
                                                [
                                                    'id' => Str::uuid(),
                                                    'type' => 'column',
                                                    'settings' => [
                                                        'blocks' => [
                                                            [
                                                                'id' => Str::uuid(),
                                                                'type' => 'text',
                                                                'settings' => [
                                                                    'content' => '<span class="text-primary font-bold tracking-wider uppercase text-sm">Tentang JA-CMS</span><h2 class="text-4xl font-bold text-foreground mt-2 mb-6">CMS Modern untuk Era Digital</h2><div class="space-y-4 text-lg text-muted-foreground"><p>JA-CMS adalah Content Management System generasi baru yang dibangun dengan teknologi terkini. Menggabungkan kekuatan Laravel di backend dan Vue.js di frontend untuk pengalaman pengembangan yang seamless.</p><p>Dirancang untuk developer yang menginginkan kecepatan, fleksibilitas, dan kemudahan kustomisasi tanpa mengorbankan performa.</p></div>'
                                                                ]
                                                            ],
                                                            // Stats Grid (using HTML for quick layout)
                                                            [
                                                                'id' => Str::uuid(),
                                                                'type' => 'html',
                                                                'settings' => [
                                                                    'content' => '<div class="grid grid-cols-3 gap-6 pt-6 border-t border-border mt-8"><div><div class="text-3xl font-bold text-primary">50+</div><div class="text-sm text-muted-foreground mt-1">Components</div></div><div><div class="text-3xl font-bold text-primary">10x</div><div class="text-sm text-muted-foreground mt-1">Faster</div></div><div><div class="text-3xl font-bold text-primary">100%</div><div class="text-sm text-muted-foreground mt-1">Open Source</div></div></div>'
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ],
                                                // Col 2: Code Block
                                                [
                                                    'id' => Str::uuid(),
                                                    'type' => 'column',
                                                    'settings' => [
                                                        'blocks' => [
                                                            [
                                                                'id' => Str::uuid(),
                                                                'type' => 'code',
                                                                'settings' => [
                                                                    'code' => "// Create content with ease\nconst content = await Content.create({\n  title: 'Welcome to JA-CMS',\n  type: 'page',\n  status: 'published',\n  blocks: [...]\n});\n\n// Blazing fast queries\nContent::published()\n  ->with('author', 'tags')\n  ->paginate(12);",
                                                                    'language' => 'javascript',
                                                                    'window_chrome' => true,
                                                                    'theme' => 'dark',
                                                                    'padding' => 'py-0' // Handled by block wrapper
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
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
                'id' => Str::uuid(),
                'type' => 'features',
                'settings' => [
                    'title' => 'Fitur Powerful',
                    'padding' => 'py-24',
                    'items' => [
                        ['title' => 'Blazing Fast', 'description' => 'Optimized for speed with smart caching, lazy loading, and efficient database queries.', 'icon' => 'zap'],
                        ['title' => 'Theme System', 'description' => 'Flexible theming with Janari Theme included. Dark mode, custom colors, and more.', 'icon' => 'palette'],
                        ['title' => 'Block Builder', 'description' => 'Visual page builder with drag-and-drop blocks. No coding required.', 'icon' => 'layers'],
                        ['title' => 'Secure by Default', 'description' => 'Built-in authentication, role-based permissions, and security best practices.', 'icon' => 'shield'],
                        ['title' => 'Developer Friendly', 'description' => 'Clean code, extensive documentation, and easy customization for developers.', 'icon' => 'code-2'],
                        ['title' => 'Multi-language', 'description' => 'Full i18n support. Build multilingual websites with ease.', 'icon' => 'globe']
                    ]
                ]
            ],

            // ADVANTAGES SECTION
            [
                'id' => Str::uuid(),
                'type' => 'section',
                'settings' => [
                    'bgColor' => 'transparent',
                    '_css_class' => 'bg-muted/40 dark:bg-muted/20',
                    'padding' => ['top' => '96px', 'bottom' => '96px', 'left' => '0', 'right' => '0']
                ],
                'settings' => [
                    'blocks' => [
                        [
                         'id' => Str::uuid(),
                         'type' => 'container',
                         'settings' => ['maxWidth' => 1200],
                         'settings' => [
                             'blocks' => [
                                // Title
                                [
                                    'id' => Str::uuid(),
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => '<div class="text-center mb-16"><span class="text-primary font-bold tracking-wider uppercase text-sm">Keunggulan</span><h2 class="text-4xl font-bold mt-4 mb-6">Mengapa JA-CMS?</h2></div>'
                                    ]
                                ],
                                // 2 Col Grid for advantages
                                [
                                    'id' => Str::uuid(),
                                    'type' => 'columns',
                                    'settings' => [
                                        'columns' => [50, 50],
                                        'gap' => 48,
                                    ],
                                    'settings' => [
                                        'blocks' => [
                                            // Col 1
                                            [
                                                'id' => Str::uuid(),
                                                'type' => 'column',
                                                'settings' => [
                                                    'blocks' => [
                                                        $this->createAdvantageItem(1, 'Modern Tech Stack', 'Laravel 11 + Vue 3 + Tailwind CSS. The best tools for modern web development.'),
                                                        $this->createAdvantageItem(2, 'Zero Configuration', 'Works out of the box. No complex setup required. Just deploy and start building.')
                                                    ]
                                                ]
                                            ],
                                            // Col 2
                                            [
                                                'id' => Str::uuid(),
                                                'type' => 'column',
                                                'settings' => [
                                                    'blocks' => [
                                                        $this->createAdvantageItem(3, 'SEO Optimized', 'Built-in SEO tools, meta tags, sitemaps, and structured data support.'),
                                                        $this->createAdvantageItem(4, 'API-First Design', 'RESTful API ready for headless CMS usage or mobile app integration.')
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                             ]
                         ]   
                        ]
                    ]
                ]
            ],

            // TEAM SECTION
            [
                'id' => Str::uuid(),
                'type' => 'section',
                'settings' => [
                    'padding' => ['top' => '96px', 'bottom' => '96px', 'left' => '0', 'right' => '0']
                ],
                'settings' => [
                    'blocks' => [
                         [
                            'id' => Str::uuid(),
                            'type' => 'container',
                            'settings' => ['maxWidth' => 1200],
                            'settings' => [
                                'blocks' => [
                                    // Title
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'text',
                                        'settings' => [
                                            'content' => '<div class="text-center mb-16"><span class="text-primary font-bold tracking-wider uppercase text-sm">Tim Developer</span><h2 class="text-4xl font-bold mt-4 mb-6">Meet Our Team</h2><p class="text-muted-foreground">The passionate developers behind JA-CMS</p></div>'
                                        ]
                                    ],
                                    // Grid 4 Cols for Team
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'columns',
                                        'settings' => [
                                            'columns' => [25, 25, 25, 25],
                                            'gap' => 32,
                                        ],
                                        'settings' => [
                                            'blocks' => [
                                                $this->createTeamMember('Ari Nurcahya', 'Lead Developer', 'AN'),
                                                $this->createTeamMember('Sarah Amira', 'Frontend Engineer', 'SA'),
                                                $this->createTeamMember('Budi Santoso', 'Backend Engineer', 'BS'),
                                                $this->createTeamMember('Maya Putri', 'UI/UX Designer', 'MP'),
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                         ]
                    ]
                ]
            ],

            // CTA SECTION
            [
                'id' => Str::uuid(),
                'type' => 'section',
                'settings' => [
                    'bgColor' => '#0f172a', // slate-900
                    'padding' => ['top' => '96px', 'bottom' => '96px', 'left' => '0', 'right' => '0'],
                    '_custom_css' => ['position' => 'relative', 'overflow' => 'hidden']
                ],
                'settings' => [
                    'blocks' => [
                        [
                            'id' => Str::uuid(),
                            'type' => 'html',
                            'settings' => [
                                'content' => '<div class="absolute inset-0 bg-gradient-to-br from-primary/30 to-transparent dark:from-white/10 dark:to-transparent"></div>',
                                '_position' => 'absolute',
                                '_z_index' => 0
                            ]
                        ],
                        [
                            'id' => Str::uuid(),
                            'type' => 'container',
                            'settings' => [
                                'maxWidth' => 800,
                                'containerAlignment' => 'center',
                                '_z_index' => 10
                            ],
                            'settings' => [
                                'blocks' => [
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'text',
                                        'settings' => [
                                            'content' => '<h2 class="text-4xl md:text-5xl font-bold mb-6 text-white text-center">Siap Memulai?</h2><p class="text-xl text-white/80 text-center mb-10">Mulai bangun website impianmu dengan JA-CMS. Gratis, open source, dan powerful.</p>'
                                        ]
                                    ],
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'html',
                                        'settings' => [
                                            'content' => '<div class="flex flex-col sm:flex-row items-center justify-center gap-4"><a href="/register" class="rounded-full bg-white text-slate-900 px-8 py-4 text-sm font-bold shadow-lg hover:bg-white/90 transition-all duration-300 hover:scale-105">Daftar Sekarang</a><a href="https://github.com/ja-cms" target="_blank" class="rounded-full border-2 border-white/50 px-8 py-4 text-sm font-semibold text-white hover:bg-white/10 transition-all duration-300 flex items-center gap-2"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.43.372.823 1.102.823 2.222 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg> Star on GitHub</a></div>'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        Content::updateOrCreate(
            ['slug' => 'home', 'type' => 'page'],
            [
                'title' => 'Home',
                'slug' => 'home',
                'status' => 'published',
                'author_id' => $user->id,
                'blocks' => $blocks,
                'published_at' => now(),
            ]
        );
        $this->command->info('Home page created.');
    }

    private function createAdvantageItem($num, $title, $desc) {
        return [
            'id' => Str::uuid(),
            'type' => 'html',
            'settings' => [
                'content' => "<div class='flex gap-6 mb-8'><div class='shrink-0 w-12 h-12 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-lg shadow-lg'>{$num}</div><div><h3 class='text-xl font-bold mb-2 text-foreground'>{$title}</h3><p class='text-muted-foreground'>{$desc}</p></div></div>"
            ]
        ];
    }

    private function createTeamMember($name, $role, $initials) {
        return [
            'id' => Str::uuid(),
            'type' => 'column',
            'settings' => [
                'blocks' => [
                    [
                        'id' => Str::uuid(),
                        'type' => 'person',
                        'settings' => [
                            'name' => $name,
                            'role' => $role,
                            'avatar' => '', // Using default fallback
                            'bio' => ''
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createAboutPage($user): void {
        $blocks = [
            [
                'id' => Str::uuid(),
                'type' => 'hero',
                'settings' => [
                    'title' => 'About JA-CMS',
                    'subtitle' => 'We are building the future of content management - simple, powerful, and beautiful.',
                    'bgColor' => '#1e293b',
                    'padding' => ['top' => '96px', 'bottom' => '96px'],
                ]
            ],
            [
                'id' => Str::uuid(),
                'type' => 'container',
                'settings' => ['maxWidth' => 800, 'padding' => ['top' => '64px', 'bottom' => '64px']],
                'settings' => [
                    'blocks' => [
                        [
                            'id' => Str::uuid(),
                            'type' => 'text',
                            'settings' => [
                                'content' => '<h2>Our Story</h2><p>JA-CMS was born from a simple idea: content management should be easy, fast, and beautiful. We believe that everyone deserves powerful tools to share their ideas with the world.</p><p>Our team of passionate developers and designers work tirelessly to create the best possible experience for our users. We combine modern technology with intuitive design to deliver a CMS that just works.</p>'
                            ]
                        ]
                    ]
                ]
            ]
        ];
         Content::updateOrCreate(
            ['slug' => 'about', 'type' => 'page'],
            [
                'title' => 'About Us',
                'blocks' => $blocks,
                'status' => 'published',
                'author_id' => $user->id
            ]
        );
    }
    
    private function createContactPage($user): void {
         $blocks = [
            // Dark Hero Section with Mesh Gradient
            [
                'id' => Str::uuid(),
                'type' => 'hero',
                'settings' => [
                    'title' => "Let's Connect",
                    'subtitle' => "Have a question or a project in mind? We'd love to hear from you. Reach out and let's build something amazing together.",
                    'bgType' => 'gradient',
                    'gradientStart' => '#0f172a',
                    'gradientEnd' => '#1e293b',
                    'gradientDirection' => 'to bottom right',
                    'overlayEnabled' => true,
                    'overlayColor' => 'rgba(2, 6, 23, 0.7)',
                    'minHeight' => 450,
                    'titleSize' => 64,
                    'titleColor' => '#ffffff',
                    'subtitleColor' => 'rgba(255, 255, 255, 0.7)',
                    'titleAlign' => 'left',
                    'padding' => ['top' => '120px', 'bottom' => '80px', 'left' => '24px', 'right' => '24px'],
                ]
            ],
            // Content Section
            [
                'id' => Str::uuid(),
                'type' => 'container',
                'settings' => [
                    'maxWidth' => 1200,
                    'padding' => ['top' => '0px', 'bottom' => '80px'],
                    'blocks' => [
                        [
                            'id' => Str::uuid(),
                            'type' => 'columns',
                            'settings' => [
                                'customWidths' => [40, 60],
                                'layout' => 'custom',
                                'stackOn' => 'sm',
                                'gap' => 48,
                                'blocks' => [
                                    // Info Column
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'column',
                                        'settings' => [
                                            'blocks' => [
                                                [
                                                    'id' => Str::uuid(),
                                                    'type' => 'text',
                                                    'settings' => [
                                                        'content' => '<div class="md:-mt-20 relative z-10 space-y-6">
                                                            <div class="p-8 rounded-[32px] bg-card border shadow-2xl space-y-8 animate-fade-right">
                                                                <div class="flex items-start gap-4">
                                                                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-xs font-black uppercase tracking-widest text-muted-foreground mb-1">Email Us</p>
                                                                        <p class="font-bold text-lg">hello@janari.com</p>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-start gap-4">
                                                                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-xs font-black uppercase tracking-widest text-muted-foreground mb-1">Call Us</p>
                                                                        <p class="font-bold text-lg">+62 21 555 1234</p>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-start gap-4">
                                                                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500">
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-xs font-black uppercase tracking-widest text-muted-foreground mb-1">Our Office</p>
                                                                        <p class="font-bold text-lg">Central Park, Jakarta</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    // Form Column
                                    [
                                        'id' => Str::uuid(),
                                        'type' => 'column',
                                        'settings' => [
                                            'blocks' => [
                                                [
                                                    'id' => Str::uuid(),
                                                    'type' => 'contact_form',
                                                    'settings' => [
                                                        'title' => 'Send Example Message',
                                                        'description' => 'Tell us about your project or just say hi!',
                                                        'customStyle' => 'bg-card/80 backdrop-blur-xl border border-white/10 p-10 rounded-[40px] shadow-2xl md:-mt-20 relative z-10 animate-fade-left',
                                                        'buttonText' => 'Send Message'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Map Section
            [
                'id' => Str::uuid(),
                'type' => 'container',
                'settings' => [
                    'maxWidth' => 1200,
                    'padding' => ['bottom' => '80px'],
                    'blocks' => [
                        [
                            'id' => Str::uuid(),
                            'type' => 'map',
                            'settings' => [
                                'address' => 'Jakarta, Indonesia',
                                'height' => 450,
                                'zoom' => 15,
                                'radius' => 'rounded-[40px]',
                                'border' => true
                            ]
                        ]
                    ]
                ]
            ]
        ];
         Content::updateOrCreate(
            ['slug' => 'contact', 'type' => 'page'],
            [
                'title' => 'Contact',
                'blocks' => $blocks,
                'status' => 'published',
                'author_id' => $user->id
            ]
        );
    }

    private function createBlogPosts($user, $categories, $tags): void {
        $posts = [
            [
                'title' => 'Getting Started with JA-CMS',
                'slug' => 'getting-started',
                'excerpt' => 'Learn how to create stunning pages using the visual builder.',
                'category' => 'tutorial',
                'tags' => ['Guide', 'CMS']
            ],
            [
                'title' => 'Advanced Theme Customization',
                'slug' => 'theme-customization',
                'excerpt' => 'Deep dive into the theming system.',
                'category' => 'design',
                'tags' => ['Design', 'Vue.js']
            ]
        ];

        foreach($posts as $p) {
            $cat = $categories[$p['category']] ?? null;
            $content = Content::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'type' => 'post',
                    'status' => 'published',
                    'author_id' => $user->id,
                    'excerpt' => $p['excerpt'],
                    'body' => '<p>This is a sample blog post content generated by the seeder.</p>',
                    'category_id' => $cat?->id,
                    'published_at' => now()
                ]
            );
            // Tags...
        }
    }
}
