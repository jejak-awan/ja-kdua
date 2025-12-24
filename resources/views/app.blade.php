<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'JA CMS') }}</title>

    {{-- Critical CSS - Complete variables for sidebar dark mode --}}
    <style>
        /* CSS Custom Properties - Must match app.css exactly */
        :root {
            --background: 240 5% 96%;
            --foreground: 240 10% 3.9%;
            --card: 0 0% 100%;
            --card-foreground: 240 10% 3.9%;
            --border: 240 5.9% 90%;
            --muted: 240 4.8% 95.9%;
            --muted-foreground: 240 3.8% 46.1%;
            --accent: 240 4.8% 95.9%;
            --accent-foreground: 240 5.9% 10%;
        }
        .dark {
            --background: 240 10% 6%;
            --foreground: 0 0% 98%;
            --card: 240 6% 10%;
            --card-foreground: 0 0% 98%;
            --border: 240 3.7% 15.9%;
            --muted: 240 3.7% 15.9%;
            --muted-foreground: 240 5% 64.9%;
            --accent: 240 3.7% 15.9%;
            --accent-foreground: 0 0% 98%;
        }
    </style>

    {{-- Blocking script - Apply dark mode class immediately --}}
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

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div id="app"></div>
</body>
</html>
