<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Logo from '@/Components/Logo.vue';

const props = defineProps({
    isSubscribed: Boolean,
    currentPlan: String,
    prices: Object,
});

const form = useForm({
    price_id: '',
});

const subscribe = async (priceId) => {
    form.price_id = priceId;
    form.processing = true;
    
    try {
        const response = await fetch(route('subscription.checkout'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ price_id: priceId }),
        });
        
        const data = await response.json();
        
        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        }
    } catch (error) {
        console.error('Checkout error:', error);
    } finally {
        form.processing = false;
    }
};
</script>

<template>
    <Head title="Pricing" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <!-- Navbar -->
        <nav class="border-b border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <Link :href="route('welcome')">
                        <Logo size="md" />
                    </Link>
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth?.user"
                            :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors"
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
        <div class="py-16 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4">
                Simple, transparent pricing
            </h1>
            <p class="text-xl text-slate-400 max-w-2xl mx-auto">
                Start for free, upgrade when you need more
            </p>
        </div>

        <!-- Pricing Cards -->
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <div class="grid md:grid-cols-3 gap-8">
                
                <!-- Free Plan -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-2xl p-8">
                    <h3 class="text-lg font-semibold text-white mb-2">Free</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-white">0€</span>
                        <span class="text-slate-400">/forever</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            5 videos max
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            10 quick notes per video
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            3 tags max
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Rich text editor
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Timestamps
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-slate-500">PDF export</span>
                        </li>
                    </ul>
                    <Link
                        v-if="!$page.props.auth?.user"
                        :href="route('register')"
                        class="block w-full py-3 text-center text-sm font-semibold border border-slate-600 text-slate-300 hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        Get started
                    </Link>
                    <div v-else-if="!isSubscribed" class="py-3 text-center text-sm text-slate-500">
                        Current plan
                    </div>
                </div>

                <!-- Monthly Plan -->
                <div class="bg-slate-800/50 border-2 border-blue-500 rounded-2xl p-8 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
                        Popular
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Premium Monthly</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-white">{{ prices.monthly.amount }}€</span>
                        <span class="text-slate-400">/month</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>Unlimited</strong> videos
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>Unlimited</strong> quick notes
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>Unlimited</strong> tags
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Rich text editor
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Timestamps
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>PDF export</strong>
                        </li>
                    </ul>
                    <button
                        v-if="!$page.props.auth?.user"
                        @click="$inertia.visit(route('register'))"
                        class="block w-full py-3 text-center text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                        Get started
                    </button>
                    <button
                        v-else-if="!isSubscribed"
                        @click="subscribe(prices.monthly.id)"
                        :disabled="form.processing"
                        class="block w-full py-3 text-center text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Loading...' : 'Subscribe' }}
                    </button>
                    <div v-else-if="currentPlan === 'monthly'" class="py-3 text-center text-sm text-green-500 font-semibold">
                        ✓ Current plan
                    </div>
                    <Link
                        v-else
                        :href="route('subscription.billing')"
                        class="block w-full py-3 text-center text-sm font-semibold border border-slate-600 text-slate-300 hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        Switch plan
                    </Link>
                </div>

                <!-- Yearly Plan -->
                <div class="bg-slate-800/50 border border-slate-700/50 rounded-2xl p-8 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">
                        Save 30%
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Premium Yearly</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-white">{{ prices.yearly.amount }}€</span>
                        <span class="text-slate-400">/year</span>
                    </div>
                    <ul class="space-y-3 mb-8 text-sm text-slate-300">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>Unlimited</strong> videos
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>Unlimited</strong> quick notes
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>Unlimited</strong> tags
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Rich text editor
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Timestamps
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <strong>PDF export</strong>
                        </li>
                    </ul>
                    <button
                        v-if="!$page.props.auth?.user"
                        @click="$inertia.visit(route('register'))"
                        class="block w-full py-3 text-center text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                    >
                        Get started
                    </button>
                    <button
                        v-else-if="!isSubscribed"
                        @click="subscribe(prices.yearly.id)"
                        :disabled="form.processing"
                        class="block w-full py-3 text-center text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Loading...' : 'Subscribe' }}
                    </button>
                    <div v-else-if="currentPlan === 'yearly'" class="py-3 text-center text-sm text-green-500 font-semibold">
                        ✓ Current plan
                    </div>
                    <Link
                        v-else
                        :href="route('subscription.billing')"
                        class="block w-full py-3 text-center text-sm font-semibold border border-slate-600 text-slate-300 hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        Switch plan
                    </Link>
                </div>
            </div>

            <!-- FAQ or additional info -->
            <div class="mt-16 text-center">
                <p class="text-slate-400">
                    Questions? <a href="mailto:support@vid-notes.com" class="text-blue-400 hover:underline">Contact us</a>
                </p>
            </div>
        </div>
    </div>
</template>