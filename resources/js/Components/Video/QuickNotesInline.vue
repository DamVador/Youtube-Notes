<script setup>
import { computed } from 'vue';

const props = defineProps({
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
});

const emit = defineEmits([
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
    <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex-1 flex flex-col min-h-0 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 flex-shrink-0 border-b border-gray-200 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                Quick Notes
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">
                    ({{ notes.length }})
                </span>
            </span>
        </div>

        <!-- Add Quick Note -->
        <div class="p-3 border-b border-gray-100 dark:border-gray-700 flex-shrink-0">
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

        <!-- Quick Notes List (scrollable) -->
        <div class="flex-1 overflow-y-auto">
            <div v-if="sortedNotes.length === 0" class="p-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                No quick notes yet
            </div>
            <div
                v-for="note in sortedNotes"
                :key="note.id"
                class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 last:border-0"
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
</template>