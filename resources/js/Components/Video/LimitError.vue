<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    message: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['dismiss']);
</script>

<template>
    <div class="mt-3 p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-800 rounded-lg">
        <div class="flex items-start gap-2">
            <span class="text-red-500 flex-shrink-0">⚠️</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-red-700 dark:text-red-300">{{ message }}</p>
                <Link 
                    :href="route('subscription.pricing')" 
                    class="inline-block mt-2 px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700"
                >
                    Upgrade to Premium
                </Link>
            </div>
            <button @click="$emit('dismiss')" class="text-red-400 hover:text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>