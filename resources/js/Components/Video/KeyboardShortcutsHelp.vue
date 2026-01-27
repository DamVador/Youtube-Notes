<script setup>
import { ref } from 'vue';

const show = ref(false);

const shortcuts = [
    { keys: 'Ctrl + T', action: 'Insert timestamp in editor' },
    { keys: 'Ctrl + N', action: 'Quick note + timestamp' },
    { keys: 'Ctrl + P', action: 'Presentation mode' },
    { keys: 'Ctrl + E', action: 'Export PDF' },
    { keys: 'Escape', action: 'Close panels' },
];
</script>

<template>
    <!-- Trigger Button -->
    <button
        @click="show = true"
        class="hidden lg:flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
        title="Keyboard shortcuts"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>

    <!-- Modal -->
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    @click="show = false"
                    class="absolute inset-0 bg-black/50"
                ></div>

                <!-- Content -->
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 w-full max-w-sm mx-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Keyboard Shortcuts
                        </h3>
                        <button
                            @click="show = false"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="shortcut in shortcuts"
                            :key="shortcut.keys"
                            class="flex items-center justify-between"
                        >
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                {{ shortcut.action }}
                            </span>
                            <kbd class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600">
                                {{ shortcut.keys }}
                            </kbd>
                        </div>
                    </div>

                    <p class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500">
                        Desktop only
                    </p>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>