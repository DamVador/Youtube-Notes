<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import CookieConsent from '@/Components/CookieConsent.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head>
        <title>Features - VidNotes</title>
        <meta name="description" content="Discover VidNotes features: timestamped notes, rich text editor, tags, PDF export, presentation mode, and more. The ultimate YouTube study companion.">
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <!-- Navbar -->
        <nav class="border-b border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <Link href="/">
                            <Logo size="md" />
                        </Link>
                    </div>

                    <div class="flex items-center gap-6">
                        <Link
                            href="/features"
                            class="text-sm font-medium text-blue-400"
                        >
                            Features
                        </Link>
                        <Link
                            :href="route('subscription.pricing')"
                            class="text-sm font-medium text-slate-300 hover:text-white transition-colors"
                        >
                            Pricing
                        </Link>
                    </div>

                    <div v-if="canLogin" class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium text-white hover:text-blue-400 transition-colors"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                            >
                                Sign up
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Everything you need to
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-blue-500 to-indigo-500">
                        learn from videos
                    </span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                    VidNotes combines powerful note-taking with seamless YouTube integration. 
                    Never lose an insight again.
                </p>
            </div>

            <div class="absolute top-0 -z-10 h-full w-full opacity-20 overflow-hidden">
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-blue-600 rounded-full blur-[120px]"></div>
            </div>
        </div>

        <!-- Main Features Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Timestamps -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-blue-500/50 transition-colors">
                    <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Clickable Timestamps</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Insert timestamps with one click. Click any timestamp to jump directly to that moment in the video.
                    </p>
                </div>

                <!-- Quick Notes -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-green-500/50 transition-colors">
                    <div class="w-12 h-12 bg-green-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Quick Notes</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Capture quick thoughts without interrupting your flow. Perfect for jotting down key points fast.
                    </p>
                </div>

                <!-- Rich Editor -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-purple-500/50 transition-colors">
                    <div class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Rich Text Editor</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Format your notes with headings, lists, bold, italic, and more. Your notes, your style.
                    </p>
                </div>

                <!-- Tags -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-yellow-500/50 transition-colors">
                    <div class="w-12 h-12 bg-yellow-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Tags & Organization</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Create custom colored tags to organize your notes by topic, course, or project.
                    </p>
                </div>

                <!-- PDF Export -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-red-500/50 transition-colors">
                    <div class="w-12 h-12 bg-red-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">PDF Export</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Export your notes to PDF for offline reading, printing, or sharing with others.
                    </p>
                </div>

                <!-- Presentation Mode -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-indigo-500/50 transition-colors">
                    <div class="w-12 h-12 bg-indigo-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Presentation Mode</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Turn your notes into slides instantly. Perfect for reviewing or teaching others.
                    </p>
                </div>
            </div>

            <!-- Second Row -->
            <div class="grid md:grid-cols-3 gap-8 mt-8">
                <!-- Continue Watching -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-teal-500/50 transition-colors">
                    <div class="w-12 h-12 bg-teal-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Continue Watching</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Pick up right where you left off. Your video position is saved automatically.
                    </p>
                </div>

                <!-- Search -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-orange-500/50 transition-colors">
                    <div class="w-12 h-12 bg-orange-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Search & Filter</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Find any note instantly. Search across all your videos and filter by tags.
                    </p>
                </div>

                <!-- Dark Mode -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-slate-500/50 transition-colors">
                    <div class="w-12 h-12 bg-slate-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Dark Mode</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Easy on the eyes during late-night study sessions. Switches automatically with your system.
                    </p>
                </div>
            </div>
        </div>

        <!-- Detailed Feature: Timestamps -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-700/50">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-600/20 text-blue-400 rounded-full text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Timestamps
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-4">
                        Never lose track of important moments
                    </h2>
                    <p class="text-lg text-slate-400 mb-6">
                        With one click, insert the current video timestamp into your notes. 
                        Later, click any timestamp to jump directly to that exact moment.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-slate-300">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            One-click timestamp insertion
                        </li>
                        <li class="flex items-center gap-3 text-slate-300">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Clickable links in your notes
                        </li>
                        <li class="flex items-center gap-3 text-slate-300">
                            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Works in both editor and quick notes
                        </li>
                    </ul>
                </div>
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
                    <div class="bg-slate-900 rounded-lg p-4 font-mono text-sm">
                        <p class="text-slate-300 mb-3">Introduction to the topic</p>
                        <p class="mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded text-xs font-mono cursor-pointer hover:bg-blue-600/50 transition-colors">
                                2:34
                            </span>
                            <span class="text-slate-300 ml-2">Key concept explained here</span>
                        </p>
                        <p class="mb-3">
                            <span class="inline-flex items-center px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded text-xs font-mono cursor-pointer hover:bg-blue-600/50 transition-colors">
                                5:12
                            </span>
                            <span class="text-slate-300 ml-2">Important example</span>
                        </p>
                        <p class="text-slate-300">Summary and takeaways...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="bg-gradient-to-r from-blue-600/20 to-indigo-600/20 border border-blue-500/30 rounded-2xl p-8 sm:p-12 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                    Ready to transform how you learn from videos?
                </h2>
                <p class="text-slate-300 mb-8 max-w-xl mx-auto">
                    Join thousands of students and professionals who use VidNotes to learn more effectively.
                </p>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-block px-10 py-4 text-lg font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-900/30 transform transition hover:-translate-y-1 active:scale-95"
                >
                    Get started for free
                </Link>
                <p class="text-slate-500 text-sm mt-4">No credit card required</p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <Logo size="sm" />
                    <div class="flex items-center gap-6 text-sm text-slate-500">
                        <Link href="/features" class="hover:text-slate-300 transition-colors">
                            Features
                        </Link>
                        <Link :href="route('subscription.pricing')" class="hover:text-slate-300 transition-colors">
                            Pricing
                        </Link>
                        <Link :href="route('legal.terms')" class="hover:text-slate-300 transition-colors">
                            Terms
                        </Link>
                        <Link :href="route('legal.privacy')" class="hover:text-slate-300 transition-colors">
                            Privacy
                        </Link>
                        <span>© {{ new Date().getFullYear() }} VidNotes</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Cookie Consent -->
        <CookieConsent />
    </div>
</template>