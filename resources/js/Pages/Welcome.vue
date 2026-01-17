<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <!-- Navbar -->
        <nav class="border-b border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <!-- <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center"> -->
                            <Logo size="md" />
                        <!-- </div> -->
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6">
                    Don't just watch, learn ! Take notes while watching
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-blue-600">
                        YouTube videos
                    </span>
                </h1>
                <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto mb-10">
                    Search videos, take precise notes with automatic timestamps, and build your personal knowledge library.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-8 py-3 text-base font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                        Get started for free
                    </Link>
                    <Link
                        v-if="canLogin && !$page.props.auth.user"
                        :href="route('login')"
                        class="px-8 py-3 text-base font-medium border border-slate-600 hover:border-slate-500 text-slate-300 hover:text-white rounded-lg transition-colors"
                    >
                        Sign in to your account
                    </Link>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Search YouTube</h3>
                    <p class="text-slate-400 text-sm">
                        Find any video directly from the app and start taking notes immediately.
                    </p>
                </div>

                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Rich Text Editor</h3>
                    <p class="text-slate-400 text-sm">
                        Format your notes with headings, lists, highlights, and more.
                    </p>
                </div>

                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-6">
                    <div class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Timestamps</h3>
                    <p class="text-slate-400 text-sm">
                        Add clickable timestamps to jump to any moment in the video.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <Logo size="sm" />
                    <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400">
                        <Link :href="route('legal.terms')" class="hover:text-slate-700 dark:hover:text-slate-200">
                            Terms
                        </Link>
                        <Link :href="route('legal.privacy')" class="hover:text-slate-700 dark:hover:text-slate-200">
                            Privacy
                        </Link>
                        <span>© {{ new Date().getFullYear() }} VidNotes</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
