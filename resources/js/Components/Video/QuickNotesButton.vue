<script setup>
defineProps({
    notesCount: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['click']);
</script>

<template>
    <button
        @click="$emit('click')"
        class="mt-4 w-full px-4 py-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-between hover:border-blue-300 dark:hover:border-blue-600 transition-colors"
    >
        <span class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg>
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                Quick Notes
            </span>
        </span>
        <span class="flex items-center gap-2">
            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">
                {{ notesCount }}
            </span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </span>
    </button>
</template>