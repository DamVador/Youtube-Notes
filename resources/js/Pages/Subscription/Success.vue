<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';
</script>

<template>
    <Head title="Subscription Successful" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center">
        <div class="text-center px-4">
            <div class="w-20 h-20 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-4">Welcome to Premium!</h1>
            <p class="text-slate-400 mb-8 max-w-md mx-auto">
                Your subscription is now active. Enjoy unlimited videos, notes, and all premium features.
            </p>
            <Link
                :href="route('dashboard')"
                class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
            >
                Go to Dashboard
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </Link>
        </div>
    </div>
</template>