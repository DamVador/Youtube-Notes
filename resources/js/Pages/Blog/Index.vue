<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import CookieConsent from '@/Components/CookieConsent.vue';

defineProps({
    posts: Object,
    canLogin: Boolean,
    canRegister: Boolean,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head>
        <title>Blog - VidNotes</title>
        <meta name="description" content="Tips, tutorials, and insights about video note-taking, learning from YouTube, and productivity.">
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
                        <Link href="/features" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">
                            Features
                        </Link>
                        <Link :href="route('subscription.pricing')" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">
                            Pricing
                        </Link>
                        <Link href="/blog" class="text-sm font-medium text-blue-400">
                            Blog
                        </Link>
                    </div>

                    <div v-if="canLogin" class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth?.user"
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

        <!-- Header -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white mb-4">
                    Blog
                </h1>
                <p class="text-lg text-slate-400 max-w-2xl mx-auto">
                    Tips, tutorials, and insights about video note-taking, learning from YouTube, and productivity.
                </p>
            </div>

            <div class="absolute top-0 -z-10 h-full w-full opacity-20 overflow-hidden">
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-blue-600 rounded-full blur-[120px]"></div>
            </div>
        </div>

        <!-- Posts Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <div v-if="posts.data.length === 0" class="text-center py-12">
                <p class="text-slate-400">No posts yet. Check back soon!</p>
            </div>

            <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <Link
                    v-for="post in posts.data"
                    :key="post.id"
                    :href="`/blog/${post.slug}`"
                    class="group bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden hover:border-blue-500/50 transition-all hover:-translate-y-1"
                >
                    <div v-if="post.cover_image" class="aspect-video overflow-hidden">
                        <img
                            :src="post.cover_image"
                            :alt="post.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <div v-else class="aspect-video bg-gradient-to-br from-blue-600/20 to-purple-600/20 flex items-center justify-center">
                        <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <div class="p-6">
                        <time class="text-sm text-slate-500">{{ formatDate(post.published_at) }}</time>
                        <h2 class="text-xl font-semibold text-white mt-2 mb-3 group-hover:text-blue-400 transition-colors">
                            {{ post.title }}
                        </h2>
                        <p v-if="post.excerpt" class="text-slate-400 text-sm line-clamp-3">
                            {{ post.excerpt }}
                        </p>
                        <div class="mt-4 flex items-center gap-2 text-blue-400 text-sm font-medium">
                            Read more
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="posts.last_page > 1" class="mt-12 flex justify-center gap-2">
                <Link
                    v-for="page in posts.last_page"
                    :key="page"
                    :href="`/blog?page=${page}`"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                        page === posts.current_page
                            ? 'bg-blue-600 text-white'
                            : 'bg-slate-800 text-slate-300 hover:bg-slate-700'
                    ]"
                >
                    {{ page }}
                </Link>
            </div>
        </div>

        <!-- CTA -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-700/50">
            <div class="bg-gradient-to-r from-blue-600/20 to-indigo-600/20 border border-blue-500/30 rounded-2xl p-8 sm:p-12 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                    Ready to take better notes?
                </h2>
                <p class="text-slate-300 mb-8 max-w-xl mx-auto">
                    Join thousands of learners who use VidNotes to learn more effectively from YouTube.
                </p>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-block px-10 py-4 text-lg font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-900/30 transform transition hover:-translate-y-1"
                >
                    Start for free
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <Logo size="sm" />
                    <div class="flex items-center gap-6 text-sm text-slate-500">
                        <Link href="/features" class="hover:text-slate-300 transition-colors">Features</Link>
                        <Link :href="route('subscription.pricing')" class="hover:text-slate-300 transition-colors">Pricing</Link>
                        <Link href="/blog" class="hover:text-slate-300 transition-colors">Blog</Link>
                        <Link :href="route('legal.terms')" class="hover:text-slate-300 transition-colors">Terms</Link>
                        <Link :href="route('legal.privacy')" class="hover:text-slate-300 transition-colors">Privacy</Link>
                        <span>© {{ new Date().getFullYear() }} VidNotes</span>
                    </div>
                </div>
            </div>
        </footer>

        <CookieConsent />
    </div>
</template>