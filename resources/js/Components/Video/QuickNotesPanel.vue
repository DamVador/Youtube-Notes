<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    notes: {
        type: Array,
        default: () => [],
    },
    newNoteContent: {
        type: String,
        default: '',
    },
    currentTimestamp: {
        type: Number,
        default: null,
    },
    isSavingNote: {
        type: Boolean,
        default: false,
    },
    editingNoteId: {
        type: Number,
        default: null,
    },
    editingNoteContent: {
        type: String,
        default: '',
    },
    editingNoteTimestamp: {
        type: Number,
        default: null,
    },
    highlightedNoteId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits([
    'close',
    'update:newNoteContent',
    'update:editingNoteContent',
    'addTimestamp',
    'clearTimestamp',
    'saveNote',
    'seekTo',
    'startEdit',
    'cancelEdit',
    'updateEditTimestamp',
    'saveEdit',
    'deleteNote',
]);

const sortedNotes = computed(() => {
    return [...props.notes].sort((a, b) => {
        if (a.timestamp === null && b.timestamp === null) return 0;
        if (a.timestamp === null) return 1;
        if (b.timestamp === null) return -1;
        return a.timestamp - b.timestamp;
    });
});

const formatTimestamp = (seconds) => {
    if (seconds === null || seconds === undefined) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};
</script>

<template>
    <Teleport to="body">
        <Transition name="sheet">
            <div
                v-if="show"
                class="fixed inset-0 z-50"
            >
                <!-- Backdrop -->
                <div
                    @click="$emit('close')"
                    class="absolute inset-0 bg-black/50"
                ></div>

                <!-- Sheet - Right panel on desktop, bottom sheet on mobile -->
                <div 
                    class="absolute bg-white dark:bg-gray-800 flex flex-col
                        lg:right-0 lg:top-0 lg:bottom-0 lg:w-[400px] lg:rounded-l-2xl
                        max-lg:bottom-0 max-lg:left-0 max-lg:right-0 max-lg:max-h-[70vh] max-lg:rounded-t-2xl"
                >
                    <!-- Handle (mobile only) -->
                    <div class="lg:hidden flex justify-center py-3 flex-shrink-0">
                        <div class="w-10 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                    </div>

                    <!-- Header -->
                    <div class="px-4 py-3 lg:py-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Quick Notes ({{ notes.length }})
                        </h3>
                        <button
                            @click="$emit('close')"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Add Quick Note -->
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
                        <textarea
                            :value="newNoteContent"
                            @input="$emit('update:newNoteContent', $event.target.value)"
                            placeholder="Add a quick note..."
                            rows="2"
                            class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500 resize-none"
                        ></textarea>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center gap-2">
                                <button
                                    @click="$emit('addTimestamp')"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                                >
                                    + Timestamp
                                </button>
                                <span
                                    v-if="currentTimestamp !== null"
                                    class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300"
                                >
                                    {{ formatTimestamp(currentTimestamp) }}
                                    <button @click="$emit('clearTimestamp')" class="hover:text-blue-900 dark:hover:text-blue-100">×</button>
                                </span>
                            </div>
                            <button
                                @click="$emit('saveNote')"
                                :disabled="isSavingNote || !newNoteContent.trim()"
                                class="text-xs px-3 py-1.5 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:bg-gray-300 disabled:text-gray-500 dark:disabled:bg-gray-600 dark:disabled:text-gray-400 transition-colors"
                            >
                                {{ isSavingNote ? '...' : 'Add' }}
                            </button>
                        </div>
                    </div>

                    <!-- Notes List -->
                    <div class="flex-1 overflow-y-auto">
                        <div v-if="sortedNotes.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No quick notes yet
                        </div>
                        <div
                            v-for="note in sortedNotes"
                            :key="note.id"
                            :data-note-id="note.id"
                            class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors duration-300"
                            :class="{ 'bg-yellow-100 dark:bg-yellow-900/30': highlightedNoteId == note.id }"
                        >
                            <!-- Edit Mode -->
                            <div v-if="editingNoteId === note.id" class="space-y-2">
                                <textarea
                                    :value="editingNoteContent"
                                    @input="$emit('update:editingNoteContent', $event.target.value)"
                                    rows="2"
                                    class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 resize-none"
                                ></textarea>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="$emit('updateEditTimestamp')"
                                            class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300"
                                        >
                                            Update time
                                        </button>
                                        <span
                                            v-if="editingNoteTimestamp !== null"
                                            class="text-xs font-mono text-blue-600 dark:text-blue-400"
                                        >
                                            {{ formatTimestamp(editingNoteTimestamp) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="$emit('cancelEdit')"
                                            class="text-xs px-2 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            @click="$emit('saveEdit')"
                                            class="text-xs px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
                                        >
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- View Mode -->
                            <div v-else>
                                <button
                                    v-if="note.timestamp !== null"
                                    @click="$emit('seekTo', note.timestamp)"
                                    class="text-xs font-mono text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                >
                                    {{ formatTimestamp(note.timestamp) }}
                                </button>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">
                                    {{ note.content }}
                                </p>
                                <div class="flex items-center gap-3 mt-1">
                                    <button
                                        @click="$emit('startEdit', note)"
                                        class="text-xs text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="$emit('deleteNote', note.id)"
                                        class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Panel Transitions */
.sheet-enter-active,
.sheet-leave-active {
    transition: all 0.3s ease;
}

.sheet-enter-active > div:last-child,
.sheet-leave-active > div:last-child {
    transition: transform 0.3s ease;
}

.sheet-enter-from,
.sheet-leave-to {
    opacity: 0;
}

/* Mobile: slide from bottom */
@media (max-width: 1023px) {
    .sheet-enter-from > div:last-child,
    .sheet-leave-to > div:last-child {
        transform: translateY(100%);
    }
}

/* Desktop: slide from right */
@media (min-width: 1024px) {
    .sheet-enter-from > div:last-child,
    .sheet-leave-to > div:last-child {
        transform: translateX(100%);
    }
}
</style>