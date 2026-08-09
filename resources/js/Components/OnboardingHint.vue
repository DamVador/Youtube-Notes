<script setup>
defineProps({
    active: { type: Boolean, default: false },
    text: { type: String, default: '' },
});
</script>

<template>
    <div>
        <p
            v-if="active && text"
            class="flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 mb-2"
        >
            <span class="text-base">👇</span>
            {{ text }}
        </p>
        <div
            :class="active ? 'rounded-lg ring-2 ring-blue-500/60 ring-offset-2 ring-offset-white dark:ring-offset-gray-900 transition-all' : ''"
        >
            <slot />
        </div>
    </div>
</template>
