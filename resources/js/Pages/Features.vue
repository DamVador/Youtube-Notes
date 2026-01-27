<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Features - VidNotes</title>
        <meta name="description" content="Discover VidNotes features: timestamped notes, rich text editor, tags, PDF export, presentation mode, and more. The ultimate YouTube study companion.">
    </Head>

    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Navigation -->
        <nav class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link href="/" class="flex items-center gap-2">
                        <svg class="w-8 h-8 text-blue-600" viewBox="0 0 40 40" fill="currentColor">
                            <rect x="4" y="8" width="32" height="24" rx="4" fill="currentColor" opacity="0.2"/>
                            <polygon points="16,12 28,20 16,28" fill="currentColor"/>
                            <rect x="22" y="26" width="14" height="10" rx="2" fill="currentColor"/>
                            <line x1="25" y1="29" x2="33" y2="29" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                            <line x1="25" y1="32" x2="30" y2="32" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">VidNotes</span>
                    </Link>
                    <div class="flex items-center gap-4">
                        <Link href="/features" class="text-sm font-medium text-blue-600 dark:text-blue-400">
                            Features
                        </Link>
                        <Link href="/pricing" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Pricing
                        </Link>
                        <Link href="/login" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            Login
                        </Link>
                        <Link 
                            href="/register" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Start Free
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                    Everything you need to
                    <span class="text-blue-600">learn from videos</span>
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                    VidNotes combines powerful note-taking with seamless YouTube integration. 
                    Never lose an insight again.
                </p>
                <Link 
                    href="/register" 
                    class="inline-flex items-center gap-2 px-6 py-3 text-lg font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Start Free
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </Link>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-800/50">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12">
                    Powerful features for effective learning
                </h2>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Timestamps -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Clickable Timestamps
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Insert timestamps with one click. Click any timestamp to jump directly to that moment in the video.
                        </p>
                    </div>

                    <!-- Quick Notes -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Quick Notes
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Capture quick thoughts without interrupting your flow. Perfect for jotting down key points fast.
                        </p>
                    </div>

                    <!-- Rich Editor -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Rich Text Editor
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Format your notes with headings, lists, bold, italic, and more. Your notes, your style.
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Tags & Organization
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Create custom colored tags to organize your notes by topic, course, or project.
                        </p>
                    </div>

                    <!-- PDF Export -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            PDF Export
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Export your notes to PDF for offline reading, printing, or sharing with others.
                        </p>
                    </div>

                    <!-- Presentation Mode -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Presentation Mode
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Turn your notes into slides instantly. Perfect for reviewing or teaching others.
                        </p>
                    </div>

                    <!-- Continue Watching -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-teal-100 dark:bg-teal-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Continue Watching
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Pick up right where you left off. Your video position is saved automatically.
                        </p>
                    </div>

                    <!-- Search -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/50 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Search & Filter
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Find any note instantly. Search across all your videos and filter by tags.
                        </p>
                    </div>

                    <!-- Dark Mode -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Dark Mode
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300">
                            Easy on the eyes during late-night study sessions. Switches automatically with your system.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Detailed Feature: Timestamps -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-full text-sm font-medium mb-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Timestamps
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            Never lose track of important moments
                        </h2>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                            With one click, insert the current video timestamp into your notes. 
                            Later, click any timestamp to jump directly to that exact moment.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                One-click timestamp insertion
                            </li>
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Clickable links in your notes
                            </li>
                            <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Works in both editor and quick notes
                            </li>
                        </ul>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <div class="bg-white dark:bg-gray-900 rounded-lg p-4 font-mono text-sm">
                            <p class="text-gray-700 dark:text-gray-300 mb-2">Introduction to the topic</p>
                            <p class="mb-2">
                                <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded text-xs font-mono cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800">
                                    2:34
                                </span>
                                <span class="text-gray-700 dark:text-gray-300 ml-2">Key concept explained here</span>
                            </p>
                            <p class="mb-2">
                                <span class="inline-flex items-center px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded text-xs font-mono cursor-pointer hover:bg-blue-200 dark:hover:bg-blue-800">
                                    5:12
                                </span>
                                <span class="text-gray-700 dark:text-gray-300 ml-2">Important example</span>
                            </p>
                            <p class="text-gray-700 dark:text-gray-300">Summary and takeaways...</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-blue-600">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    Ready to transform how you learn from videos?
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Join thousands of students and professionals who use VidNotes to learn more effectively.
                </p>
                <Link 
                    href="/register" 
                    class="inline-flex items-center gap-2 px-8 py-4 text-lg font-medium text-blue-600 bg-white rounded-lg hover:bg-gray-100 transition-colors"
                >
                    Get Started Free
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </Link>
                <p class="text-blue-200 text-sm mt-4">No credit card required</p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="py-8 px-4 sm:px-6 lg:px-8 border-t border-gray-200 dark:border-gray-800">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    © {{ new Date().getFullYear() }} VidNotes. All rights reserved.
                </p>
                <div class="flex gap-6">
                    <Link href="/features" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        Features
                    </Link>
                    <Link href="/pricing" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        Pricing
                    </Link>
                    <a href="mailto:contact@vid-notes.com" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        Contact
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>