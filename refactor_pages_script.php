
$slugs = ['home', 'contact', 'about'];
$pages = \App\Models\Content::whereIn('slug', $slugs)->get();

foreach ($pages as $page) {
    echo "Processing {$page->slug}...\n";
    
    $originalBody = $page->body;
    if (empty($originalBody)) {
        $originalBody = "<p>This is the {$page->title} page. content is ready to be edited.</p>";
    }

    $blocks = [
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
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => $originalBody
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $page->editor_type = 'builder';
    $page->blocks = $blocks;
    $page->save();
    echo "Updated {$page->slug} to builder format.\n";
}
