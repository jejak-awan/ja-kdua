<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'JA CMS') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Blocking script - Apply dark mode class immediately BEFORE any CSS --}}
    <script nonce="{{ $cspNonce ?? '' }}">
        (function() {
            const savedMode = localStorage.getItem('admin-dark-mode');
            const isDark = savedMode === 'dark' || 
                (!savedMode && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    
    <script nonce="{{ $cspNonce ?? '' }}">
        window.siteConfig = {
            lazyLoading: {{ config('view.lazy_loading', true) ? 'true' : 'false' }}
        };
    </script>

    {{-- Critical CSS - Must match app.css exactly --}}
    <style nonce="{{ $cspNonce ?? '' }}">
        /* CSS Custom Properties - Synced with app.css */
        :root {
            /* Light mode - Shadcn Zinc matches app.css */
            --background: 240 4.8% 95.9%;
            --foreground: 240 10% 3.9%;
            --card: 0 0% 100%;
            --card-foreground: 240 10% 3.9%;
            --border: 240 5.9% 90%;
            --muted: 240 4.8% 95.9%;
            --muted-foreground: 240 3.8% 46.1%;
            --accent: 240 4.8% 95.9%;
            --accent-foreground: 240 5.9% 10%;
            /* Sidebar - Light mode */
            --sidebar: 0 0% 100%;
            --sidebar-foreground: 240 5.9% 10%;
            --sidebar-accent: 240 4.8% 95.9%;
        }
        
        .dark {
            /* Dark mode - Shadcn Zinc matches app.css */
            --background: 240 10% 3.9%;
            --foreground: 0 0% 98%;
            --card: 240 10% 3.9%;
            --card-foreground: 0 0% 98%;
            --border: 240 3.7% 15.9%;
            --muted: 240 3.7% 15.9%;
            --muted-foreground: 240 5% 64.9%;
            --accent: 240 3.7% 15.9%;
            --accent-foreground: 0 0% 98%;
            /* Sidebar - Dark mode matches bg */
            --sidebar: 240 10% 3.9%;
            --sidebar-foreground: 240 4.8% 95.9%;
            --sidebar-accent: 240 3.7% 15.9%;
        }

        /* Prevent white flash - apply bg immediately */
        html, border {
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        /* Critical element colors */
        .bg-background { background-color: hsl(var(--background)); }
        .bg-card { background-color: hsl(var(--card)); }
        .bg-sidebar { background-color: hsl(var(--sidebar)); }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased min-h-full bg-background text-foreground">
    <div id="app"></div>
</body>
</html>

