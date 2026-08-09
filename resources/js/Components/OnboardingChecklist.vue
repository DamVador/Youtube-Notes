<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    show: { type: Boolean, default: true },
    stats: { type: Object, required: true },
});

const emit = defineEmits(['dismiss']);

const page = usePage();
const isPremium = computed(() => !!page.props.auth.user.isPremium);

const steps = computed(() => [
    { key: 'video', label: 'Add a video', done: (props.stats?.videos_count ?? 0) > 0 },
    {
        key: 'note',
        label: 'Take a note',
        done: (props.stats?.notes_count ?? 0) > 0 || (props.stats?.documents_count ?? 0) > 0,
    },
    { key: 'tag', label: 'Create a tag', done: (props.stats?.tags_count ?? 0) > 0 },
]);

const doneCount = computed(() => steps.value.filter((s) => s.done).length);
const totalCount = computed(() => steps.value.length);
const nextStep = computed(() => steps.value.find((s) => !s.done));

// Once everything is done and the user is premium, there's nothing left to guide.
const allComplete = computed(() => doneCount.value === totalCount.value && isPremium.value);
</script>

<template>
    <div
        v-if="show && !allComplete"
        class="mb-6 flex items-center gap-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm px-4 py-2.5"
    >
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200 whitespace-nowrap">
            Getting started
        </span>

        <!-- Segmented progress -->
        <div class="flex items-center gap-1.5">
            <span
                v-for="step in steps"
                :key="step.key"
                class="h-1.5 w-6 rounded-full transition-colors"
                :class="step.done ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600'"
                :title="step.label"
            ></span>
        </div>

        <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap">
            {{ doneCount }}/{{ totalCount }}
        </span>

        <!-- Next action hint -->
        <span v-if="nextStep" class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:inline">
            Next: {{ nextStep.label }}
        </span>

        <button
            type="button"
            @click="emit('dismiss')"
            aria-label="Dismiss"
            class="ml-auto flex-shrink-0 -mr-1 p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>
