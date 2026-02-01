<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
import CookieConsent from '@/Components/CookieConsent.vue';
import { computed } from 'vue';

const props = defineProps({
    post: Object,
    relatedPosts: Array,
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

const metaTitle = computed(() => props.post.meta_title || props.post.title);
const metaDescription = computed(() => props.post.meta_description || props.post.excerpt || '');
</script>

<template>
    <Head>
        <title>{{ metaTitle }} - VidNotes Blog</title>
        <meta name="description" :content="metaDescription">
        <meta property="og:title" :content="metaTitle">
        <meta property="og:description" :content="metaDescription">
        <meta property="og:type" content="article">
        <meta property="og:image" :content="post.cover_image || 'https://vid-notes.com/og-image.png'">
        <meta property="article:published_time" :content="post.published_at">
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

        <!-- Article -->
        <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Back link -->
            <Link href="/blog" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-8 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Blog
            </Link>

            <!-- Header -->
            <header class="mb-8">
                <time class="text-blue-400 text-sm font-medium">{{ formatDate(post.published_at) }}</time>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mt-3 leading-tight">
                    {{ post.title }}
                </h1>
                <p v-if="post.excerpt" class="text-xl text-slate-400 mt-4">
                    {{ post.excerpt }}
                </p>
                <div class="flex items-center gap-4 mt-6 text-sm text-slate-500">
                    <span>By {{ post.author?.name || 'VidNotes Team' }}</span>
                    <span>•</span>
                    <span>{{ Math.ceil(post.content.split(' ').length / 200) }} min read</span>
                </div>
            </header>

            <!-- Cover Image -->
            <div v-if="post.cover_image" class="mb-10 rounded-xl overflow-hidden">
                <img
                    :src="post.cover_image"
                    :alt="post.title"
                    class="w-full"
                />
            </div>

            <!-- Content -->
            <div 
                class="prose prose-lg prose-invert prose-blue max-w-none
                    prose-headings:text-white prose-headings:font-bold
                    prose-p:text-slate-300 prose-p:leading-relaxed
                    prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline
                    prose-strong:text-white
                    prose-code:text-blue-300 prose-code:bg-slate-800 prose-code:px-1 prose-code:rounded
                    prose-pre:bg-slate-800 prose-pre:border prose-pre:border-slate-700
                    prose-blockquote:border-blue-500 prose-blockquote:text-slate-400
                    prose-li:text-slate-300
                    prose-img:rounded-xl"
                v-html="post.content"
            ></div>

            <!-- Share -->
            <div class="mt-12 pt-8 border-t border-slate-700/50">
                <p class="text-slate-400 text-sm mb-4">Share this article</p>
                <div class="flex items-center gap-4">
                    <a
                        :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(post.title)}&url=${encodeURIComponent('https://vid-notes.com/blog/' + post.slug)}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="p-3 bg-slate-800 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a
                        :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent('https://vid-notes.com/blog/' + post.slug)}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="p-3 bg-slate-800 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>
        </article>

        <!-- Related Posts -->
        <div v-if="relatedPosts.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-slate-700/50">
            <h2 class="text-2xl font-bold text-white mb-8">Related Articles</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <Link
                    v-for="related in relatedPosts"
                    :key="related.id"
                    :href="`/blog/${related.slug}`"
                    class="group bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden hover:border-blue-500/50 transition-all"
                >
                    <div v-if="related.cover_image" class="aspect-video overflow-hidden">
                        <img :src="related.cover_image" :alt="related.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                    </div>
                    <div class="p-5">
                        <h3 class="font-semibold text-white group-hover:text-blue-400 transition-colors">
                            {{ related.title }}
                        </h3>
                        <time class="text-sm text-slate-500 mt-2 block">{{ formatDate(related.published_at) }}</time>
                    </div>
                </Link>
            </div>
        </div>

        <!-- CTA -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="bg-gradient-to-r from-blue-600/20 to-indigo-600/20 border border-blue-500/30 rounded-2xl p-8 sm:p-12 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                    Ready to take better notes?
                </h2>
                <p class="text-slate-300 mb-8 max-w-xl mx-auto">
                    Start taking timestamped notes on YouTube videos today.
                </p>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-block px-10 py-4 text-lg font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-900/30 transform transition hover:-translate-y-1"
                >
                    Get started for free
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
                        <span>© {{ new Date().getFullYear() }} VidNotes</span>
                    </div>
                </div>
            </div>
        </footer>

        <CookieConsent />
    </div>
</template>