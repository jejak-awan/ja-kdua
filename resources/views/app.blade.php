<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'JA CMS') }}</title>
    @php
        $themeService = app(\App\Services\Core\ThemeService::class);
        $activeTheme = $themeService->getActiveTheme('frontend');
        $favicon = $activeTheme ? $themeService->getThemeSetting($activeTheme, 'brand_favicon') : null;
        
        // Use default if no theme favicon is set
        $faviconUrl = $favicon ?: '/favicon.svg';
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">

    {{-- Blocking script - Apply dark mode class immediately BEFORE any CSS --}}
    <script>
        (function() {
            const savedMode = localStorage.getItem('admin-dark-mode');
            const isDark = savedMode === 'dark' || 
                (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    
    <script>
        window.siteConfig = {
            lazyLoading: {{ config('view.lazy_loading', true) ? 'true' : 'false' }}
        };
        window.jaCmsData = @json($jaCmsData ?? []);
    </script>

    {{-- Critical CSS - Prevent Flash --}}
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222 47% 11%;
            --card: 0 0% 100%;
            --border: 220 13% 91%;
            --sidebar: 0 0% 100%;
        }
        
        .dark {
            --background: 240 10% 2%;
            --foreground: 210 40% 98%;
            --card: 240 10% 4%;
            --border: 240 10% 18%;
            --sidebar: 240 10% 2%;
        }

        html, body {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        .bg-background { background-color: hsl(var(--background)); }
        .bg-card { background-color: hsl(var(--card)); }
        .bg-sidebar { background-color: hsl(var(--sidebar)); }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased min-h-full bg-background text-foreground">
    <div id="app"></div>
</body>
</html>

