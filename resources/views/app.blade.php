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
        <meta name="keywords" content="youtube notes, video notes, timestamp notes, study tool">
        <meta name="author" content="VidNotes">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://vid-notes.com/">
        <meta property="og:title" content="VidNotes - Smart Video Note-Taking">
        <meta property="og:description" content="Take notes while watching YouTube videos. Timestamp your notes, organize with tags, and export to PDF.">
        <meta property="og:image" content="https://vid-notes.com/og-image.png">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="https://vid-notes.com/">
        <meta property="twitter:title" content="VidNotes - Smart Video Note-Taking">
        <meta property="twitter:description" content="Take notes while watching YouTube videos. Timestamp your notes, organize with tags, and export to PDF.">
        <meta property="twitter:image" content="https://vid-notes.com/og-image.png">

        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <!-- Google Analytics -->
        @production
            @if(config('services.google.analytics_id'))
            <meta name="analytics-id" content="{{ config('services.google.analytics_id') }}">
            @endif
        @endproduction
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
