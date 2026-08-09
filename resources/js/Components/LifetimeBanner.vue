<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isPremium = computed(() => !!page.props.auth.user?.isPremium);
</script>

<template>
    <div
        v-if="!isPremium"
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 p-4"
    >
        <div class="flex items-center gap-3">
            <span class="text-2xl">⚡</span>
            <div>
                <p class="text-sm font-semibold text-white">
                    Unlock everything, forever
                </p>
                <p class="text-xs text-blue-100">
                    One payment of 29€, no subscription, no renewals.
                </p>
            </div>
        </div>
        <Link
            :href="route('subscription.pricing')"
            class="flex-shrink-0 text-center whitespace-nowrap px-4 py-2 text-sm font-semibold bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
        >
            Get lifetime access
        </Link>
    </div>
</template>
