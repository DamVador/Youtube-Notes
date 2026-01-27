<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="shortcut icon" href="/favicon.ico">
        <link rel="manifest" href="/site.webmanifest">

        <!-- SEO Meta Tags -->
        <meta name="description" content="VidNotes - Take notes while watching YouTube videos. Timestamp your notes, organize with tags, and export to PDF.">
        <meta name="keywords" content="youtube notes, video notes, timestamp notes, study tool, youtube study, video learning">
        <meta name="author" content="VidNotes">
        
        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Robots (noindex pour preprod) -->
        @if(app()->environment('preprod', 'local', 'staging'))
        <meta name="robots" content="noindex, nofollow">
        @endif

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="VidNotes - Smart Video Note-Taking">
        <meta property="og:description" content="Take notes while watching YouTube videos. Timestamp your notes, organize with tags, and export to PDF.">
        <meta property="og:image" content="https://vid-notes.com/og-image.png">
        <meta property="og:site_name" content="VidNotes">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="VidNotes - Smart Video Note-Taking">
        <meta name="twitter:description" content="Take notes while watching YouTube videos. Timestamp your notes, organize with tags, and export to PDF.">
        <meta name="twitter:image" content="https://vid-notes.com/og-image.png">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title inertia>{{ config('app.name', 'VidNotes') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- Google Analytics (production only) -->
        @if(app()->environment('production') && config('services.google.analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
        @endif
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>