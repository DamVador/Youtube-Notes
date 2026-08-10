<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import Logo from '@/Components/Logo.vue';
import CookieConsent from '@/Components/CookieConsent.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const mobileMenuOpen = ref(false);

// Reveal elements as they scroll into view (no library, respects reduced motion).
onMounted(() => {
    const els = document.querySelectorAll('.reveal');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduce || !('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    els.forEach((el) => observer.observe(el));
});
</script>

<template>
    <Head>
        <title>VidNotes: Timestamped notes for YouTube videos</title>
        <meta name="description" content="VidNotes, Take timestamped notes while you watch YouTube videos. Click any timestamp to jump back to the moment, organize with tags, and export to PDF. Free to start.">
    </Head>

    <div class="min-h-screen overflow-x-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <!-- Navbar -->
        <nav class="border-b border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <Link href="/">
                            <Logo size="md" />
                        </Link>
                    </div>

                    <!-- Desktop nav -->
                    <div class="hidden sm:flex items-center gap-6">
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

                        <template v-if="canLogin">
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
                                    class="text-sm font-medium text-slate-300 hover:text-white transition-colors"
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
                        </template>
                    </div>

                    <!-- Mobile hamburger -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        type="button"
                        class="sm:hidden inline-flex items-center justify-center p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="mobile-menu"
                        aria-label="Toggle navigation menu"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu panel -->
            <div v-show="mobileMenuOpen" id="mobile-menu" class="sm:hidden border-t border-slate-700/50 bg-slate-900/95">
                <div class="px-4 py-4 space-y-1">
                    <Link
                        href="/features"
                        class="block px-3 py-2 rounded-lg text-base font-medium text-blue-400 hover:bg-slate-800 transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        Features
                    </Link>
                    <Link
                        :href="route('subscription.pricing')"
                        class="block px-3 py-2 rounded-lg text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        Pricing
                    </Link>

                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="block px-3 py-2 rounded-lg text-base font-medium text-white hover:bg-slate-800 transition-colors"
                            @click="mobileMenuOpen = false"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="block px-3 py-2 rounded-lg text-base font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
                                @click="mobileMenuOpen = false"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="block mt-2 px-3 py-2 rounded-lg text-base font-medium text-center bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                                @click="mobileMenuOpen = false"
                            >
                                Sign up
                            </Link>
                        </template>
                    </template>
                </div>
            </div>
        </nav>

      <main>
        <!-- Hero Section -->
        <section class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Copy -->
                <div class="text-center lg:text-left">
                    <div class="reveal inline-flex items-center gap-2 px-3 py-1 mb-5 bg-blue-600/15 text-blue-300 rounded-full text-sm font-medium">
                        <svg aria-hidden="true" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        For anyone who learns on YouTube
                    </div>
                    <h1 class="reveal text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.1]">
                        Turn any YouTube video into
                        <span class="shimmer-text text-transparent bg-clip-text bg-gradient-to-r from-blue-300 via-blue-500 to-indigo-500">timestamped notes</span>
                        you can actually find
                    </h1>
                    <p class="reveal mt-6 text-lg sm:text-xl text-slate-400 max-w-xl mx-auto lg:mx-0 leading-relaxed" style="--reveal-delay: 120ms">
                        Take notes while you watch, and every note links back to the exact second.
                        Click a timestamp to jump straight to the moment. Built for students,
                        developers, and lifelong learners.
                    </p>

                    <div v-if="canRegister" class="reveal mt-10 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4" style="--reveal-delay: 240ms">
                        <Link
                            :href="route('register')"
                            class="w-full sm:w-auto px-8 py-3.5 text-base font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-900/30 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-blue-600/40 active:scale-95"
                        >
                            Get started for free
                        </Link>
                        <Link
                            :href="route('subscription.pricing')"
                            class="w-full sm:w-auto px-8 py-3.5 text-base font-semibold text-slate-200 border border-slate-600 hover:border-slate-400 hover:text-white rounded-xl transition-colors"
                        >
                            See pricing
                        </Link>
                    </div>
                    <p class="reveal mt-4 text-sm text-slate-500" style="--reveal-delay: 340ms">Free to start · No credit card required</p>
                </div>

                <!-- Product preview -->
                <div class="reveal" style="--reveal-delay: 200ms">
                    <div class="card-lift bg-slate-800/60 border border-slate-700/60 rounded-2xl shadow-2xl shadow-blue-950/40 p-3 sm:p-4">
                        <!-- Window chrome -->
                        <div aria-hidden="true" class="flex items-center gap-1.5 px-2 pb-3">
                            <span class="w-3 h-3 rounded-full bg-red-400/70"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-400/70"></span>
                            <span class="w-3 h-3 rounded-full bg-green-400/70"></span>
                        </div>

                        <!-- Video player -->
                        <div class="relative rounded-lg overflow-hidden aspect-video bg-gradient-to-br from-slate-700 to-slate-900">
                            <div class="absolute top-3 left-3 right-3 text-xs text-slate-200/90 font-medium truncate">
                                Photography Basics: Full Course
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div aria-hidden="true" class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-slate-900 ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div class="absolute bottom-2.5 right-3 text-[11px] text-slate-200/80 font-mono">5:12 / 14:20</div>
                            <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-white/15">
                                <div class="h-full bg-blue-500" style="width: 38%"></div>
                            </div>
                        </div>

                        <!-- Notes panel -->
                        <div class="mt-3 rounded-lg bg-slate-900/70 border border-slate-700/50 p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-600/25 text-blue-300">Basics</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-600/25 text-purple-300">Tips</span>
                            </div>
                            <p class="mb-2.5 text-sm">
                                <span class="ts-chip inline-flex items-center px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded text-xs font-mono">2:34</span>
                                <span class="text-slate-300 ml-2">Rule of thirds</span>
                            </p>
                            <p class="mb-2.5 text-sm">
                                <span class="ts-chip inline-flex items-center px-2 py-0.5 bg-blue-600/40 text-blue-300 rounded text-xs font-mono ring-1 ring-blue-400/50" style="animation-delay: 1s">5:12</span>
                                <span class="text-slate-200 ml-2">Golden hour lighting</span>
                            </p>
                            <p class="text-sm text-slate-400">Switching to manual mode…</p>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs text-slate-500">Click any timestamp to jump the video to that second.</p>
                </div>
            </div>

            <div aria-hidden="true" class="absolute inset-0 -z-10 opacity-20 overflow-hidden">
                <div class="hero-glow absolute -top-24 left-1/2 w-[600px] h-[300px] max-w-full bg-blue-600 rounded-full blur-[120px]"></div>
            </div>
        </section>

        <!-- How it works -->
        <section class="bg-slate-900/60 border-y border-slate-700/50 py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="reveal text-3xl font-bold text-white text-center mb-3">From video to notes in seconds</h2>
            <p class="reveal text-slate-400 text-center max-w-2xl mx-auto mb-12" style="--reveal-delay: 100ms">
                No setup and no extensions. Three steps and you are taking notes.
            </p>
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="reveal text-center">
                    <div aria-hidden="true" class="mx-auto w-12 h-12 rounded-full bg-blue-600/20 text-blue-400 flex items-center justify-center text-lg font-bold mb-4">1</div>
                    <h3 class="text-lg font-semibold text-white mb-2">Paste a YouTube link</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Drop any public video URL and it opens right inside VidNotes, ready to watch.
                    </p>
                </div>
                <div class="reveal text-center" style="--reveal-delay: 120ms">
                    <div aria-hidden="true" class="mx-auto w-12 h-12 rounded-full bg-blue-600/20 text-blue-400 flex items-center justify-center text-lg font-bold mb-4">2</div>
                    <h3 class="text-lg font-semibold text-white mb-2">Take timestamped notes</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        One button stamps the exact second, then you write your note right next to it.
                    </p>
                </div>
                <div class="reveal text-center" style="--reveal-delay: 240ms">
                    <div aria-hidden="true" class="mx-auto w-12 h-12 rounded-full bg-blue-600/20 text-blue-400 flex items-center justify-center text-lg font-bold mb-4">3</div>
                    <h3 class="text-lg font-semibold text-white mb-2">Jump back anytime</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Click any timestamp to replay that moment, or export everything to PDF.
                    </p>
                </div>
            </div>
            </div>
        </section>

        <!-- Core Features -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <h2 class="reveal text-3xl font-bold text-white text-center mb-3">Capture, organize, and revisit</h2>
            <p class="reveal text-slate-400 text-center max-w-2xl mx-auto mb-12" style="--reveal-delay: 100ms">
                Three things VidNotes does so your notes stay tied to the video they came from.
            </p>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Capture with timestamps -->
                <article class="reveal card-lift bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-blue-500/50">
                    <div aria-hidden="true" class="w-12 h-12 bg-blue-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Capture with timestamps</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Insert the current moment with one click. Every timestamp becomes a link
                        that jumps the video right back to that point.
                    </p>
                </article>

                <!-- Write the way you think -->
                <article class="reveal card-lift bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-green-500/50" style="--reveal-delay: 120ms">
                    <div aria-hidden="true" class="w-12 h-12 bg-green-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Write the way you think</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        A rich text editor for structured notes, plus quick notes for fast capture,
                        without breaking your focus on the video.
                    </p>
                </article>

                <!-- Organize and reuse -->
                <article class="reveal card-lift bg-slate-800/50 border border-slate-700/50 rounded-xl p-6 hover:border-purple-500/50" style="--reveal-delay: 240ms">
                    <div aria-hidden="true" class="w-12 h-12 bg-purple-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Organize and reuse</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Tag notes by topic, export them to PDF, or switch to presentation mode to
                        review and teach from what you wrote.
                    </p>
                </article>
            </div>
        </section>

        <!-- Detailed Feature: Timestamps -->
        <section class="bg-slate-900/60 border-y border-slate-700/50 py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-600/20 text-blue-400 rounded-full text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="reveal card-lift bg-slate-800/50 border border-slate-700/50 rounded-xl p-6" style="--reveal-delay: 140ms">
                    <div class="bg-slate-900 rounded-lg p-4 font-mono text-sm">
                        <p class="text-slate-300 mb-3">Introduction to the topic</p>
                        <p class="mb-3">
                            <span class="ts-chip inline-flex items-center px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded text-xs font-mono cursor-pointer hover:bg-blue-600/50 transition-colors">
                                2:34
                            </span>
                            <span class="text-slate-300 ml-2">Key concept explained here</span>
                        </p>
                        <p class="mb-3">
                            <span class="ts-chip inline-flex items-center px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded text-xs font-mono cursor-pointer hover:bg-blue-600/50 transition-colors" style="animation-delay: 1.4s">
                                5:12
                            </span>
                            <span class="text-slate-300 ml-2">Important example</span>
                        </p>
                        <p class="text-slate-300">Summary and takeaways...</p>
                    </div>
                </div>
            </div>
            </div>
        </section>

        <!-- Everything else -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <h2 class="reveal text-xl font-semibold text-white text-center mb-8">Plus the details that make it stick</h2>
            <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-4 max-w-4xl mx-auto">
                <li class="reveal flex items-start gap-3 text-slate-300">
                    <svg aria-hidden="true" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span><strong class="font-medium text-white">Continue watching:</strong> resume each video where you left off.</span>
                </li>
                <li class="reveal flex items-start gap-3 text-slate-300" style="--reveal-delay: 100ms">
                    <svg aria-hidden="true" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span><strong class="font-medium text-white">Search &amp; filter:</strong> find any note across your videos by keyword or tag.</span>
                </li>
                <li class="reveal flex items-start gap-3 text-slate-300" style="--reveal-delay: 200ms">
                    <svg aria-hidden="true" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span><strong class="font-medium text-white">Dark mode:</strong> comfortable for long, late study sessions.</span>
                </li>
            </ul>
        </section>

        <!-- Lifetime offer -->
        <section class="bg-slate-900/60 border-y border-slate-700/50 py-16 lg:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="reveal card-lift bg-slate-800/50 border border-blue-500/30 rounded-2xl p-8 sm:p-10 grid md:grid-cols-[1fr_auto] gap-8 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-600/20 text-blue-300 rounded-full text-sm font-medium mb-4">
                        <span aria-hidden="true">⚡</span> Best value
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">Pay once. Yours forever.</h2>
                    <p class="text-slate-400 max-w-xl">
                        A single 29€ payment unlocks every premium feature for life, with no renewals.
                        Prefer to spread the cost? There is a monthly plan too. And a free plan that
                        needs no card if you just want to try.
                    </p>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-4xl font-extrabold text-white">29€ <span class="text-base font-medium text-slate-400">one time</span></div>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="mt-4 inline-block px-8 py-3.5 text-base font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-900/30 transition-all duration-300 hover:-translate-y-0.5 active:scale-95"
                    >
                        Get started for free
                    </Link>
                    <div class="mt-2">
                        <Link :href="route('subscription.pricing')" class="text-sm text-slate-400 hover:text-white transition-colors">See all plans</Link>
                    </div>
                </div>
            </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <h2 class="reveal text-3xl font-bold text-white text-center mb-10">Questions, answered</h2>
            <div class="space-y-4">
                <div class="reveal bg-slate-800/50 border border-slate-700/50 rounded-xl p-5">
                    <h3 class="font-semibold text-white mb-1.5">Is it free?</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Yes. The free plan lets you save videos and take notes with no credit card.
                        Upgrade only when you want unlimited videos, notes, and tags.
                    </p>
                </div>
                <div class="reveal bg-slate-800/50 border border-slate-700/50 rounded-xl p-5" style="--reveal-delay: 80ms">
                    <h3 class="font-semibold text-white mb-1.5">Does it work with any YouTube video?</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Any public YouTube link works. Paste the URL and the video opens right away,
                        ready for timestamped notes.
                    </p>
                </div>
                <div class="reveal bg-slate-800/50 border border-slate-700/50 rounded-xl p-5" style="--reveal-delay: 160ms">
                    <h3 class="font-semibold text-white mb-1.5">Do I need to install anything?</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        No. VidNotes runs in your browser. There is nothing to download and no extension to add.
                    </p>
                </div>
                <div class="reveal bg-slate-800/50 border border-slate-700/50 rounded-xl p-5" style="--reveal-delay: 240ms">
                    <h3 class="font-semibold text-white mb-1.5">What is the lifetime plan?</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        A single 29€ payment that unlocks every premium feature for life, with no
                        subscription and no renewals.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="reveal cta-glow bg-gradient-to-r from-blue-600/20 to-indigo-600/20 border border-blue-500/30 rounded-2xl p-8 sm:p-12 text-center">
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">
                    Start taking better notes on your videos
                </h2>
                <p class="text-slate-300 mb-8 max-w-xl mx-auto">
                    Add a YouTube video, take your first timestamped note, and keep everything in one place.
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
        </section>
      </main>

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

<style scoped>
/* Scroll-reveal: hidden until .is-visible is added by the IntersectionObserver */
.reveal {
    opacity: 0;
    transform: translateY(26px);
    transition:
        opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
        transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    transition-delay: var(--reveal-delay, 0ms);
    will-change: opacity, transform;
}
.reveal.is-visible {
    opacity: 1;
    transform: none;
}

/* Cards gently lift and glow on hover */
.card-lift {
    transition:
        transform 0.3s ease,
        border-color 0.3s ease,
        box-shadow 0.3s ease;
}
.card-lift:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px -18px rgba(37, 99, 235, 0.45);
}

/* Slow sheen sweeping across the gradient headline */
.shimmer-text {
    background-size: 200% auto;
    animation: shimmer 6s linear infinite;
}
@keyframes shimmer {
    to {
        background-position: 200% center;
    }
}

/* Breathing glow behind the hero */
.hero-glow {
    transform: translateX(-50%);
    animation: hero-glow 9s ease-in-out infinite;
}
@keyframes hero-glow {
    0%,
    100% {
        opacity: 0.7;
        transform: translateX(-50%) scale(1);
    }
    50% {
        opacity: 1;
        transform: translateX(-50%) scale(1.12);
    }
}

/* Timestamp chips softly pulse to draw the eye */
.ts-chip {
    animation: ts-pulse 2.8s ease-in-out infinite;
}
@keyframes ts-pulse {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
    }
    50% {
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.18);
    }
}

/* Faint drifting aura on the final call-to-action */
.cta-glow {
    position: relative;
    overflow: hidden;
}
.cta-glow::before {
    content: '';
    position: absolute;
    inset: -40%;
    background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.25), transparent 60%);
    animation: cta-drift 12s ease-in-out infinite alternate;
    pointer-events: none;
}
@keyframes cta-drift {
    from {
        transform: translate(-6%, -4%);
    }
    to {
        transform: translate(8%, 6%);
    }
}

/* Respect users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
    .reveal,
    .card-lift,
    .shimmer-text,
    .hero-glow,
    .ts-chip,
    .cta-glow::before {
        animation: none !important;
        transition: none !important;
    }
    .reveal {
        opacity: 1;
        transform: none;
    }
}
</style>