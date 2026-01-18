<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    message: {
        type: String,
        default: 'Upgrade to Premium for unlimited access'
    },
    compact: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div 
        v-if="!$page.props.auth.user.isPremium"
        :class="[
            'bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg',
            compact ? 'p-3' : 'p-4'
        ]"
    >
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⭐</span>
                <p :class="['text-white', compact ? 'text-sm' : 'text-base']">
                    {{ message }}
                </p>
            </div>
            <Link
                :href="route('subscription.pricing')"
                :class="[
                    'whitespace-nowrap bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors',
                    compact ? 'px-3 py-1.5 text-sm' : 'px-4 py-2'
                ]"
            >
                Upgrade
            </Link>
        </div>
    </div>
</template>