<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    video: Object,
});

const notes = ref(props.video.notes || []);
const newNoteContent = ref('');
const currentTimestamp = ref(null);
const isSaving = ref(false);
const editingNoteId = ref(null);
const editingContent = ref('');
const player = ref(null);
const playerReady = ref(false);

// Tags
const availableTags = ref([]);
const selectedTags = ref([]);
const showTagInput = ref(false);
const newTagName = ref('');
const newTagColor = ref('#3B82F6');

// Load tags
const loadTags = async () => {
    try {
        const response = await fetch(route('tags.index'));
        availableTags.value = await response.json();
    } catch (error) {
        console.error('Error loading tags:', error);
    }
};

// Initialize YouTube Player
onMounted(() => {
    loadTags();

    // Load YouTube IFrame API
    if (!window.YT) {
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    window.onYouTubeIframeAPIReady = initPlayer;

    if (window.YT && window.YT.Player) {
        initPlayer();
    }
});

const initPlayer = () => {
    player.value = new window.YT.Player('youtube-player', {
        videoId: props.video.youtube_id,
        playerVars: {
            autoplay: 0,
            modestbranding: 1,
            rel: 0,
        },
        events: {
            onReady: () => {
                playerReady.value = true;
            },
        },
    });
};

const getCurrentTime = () => {
    if (player.value && playerReady.value) {
        return Math.floor(player.value.getCurrentTime());
    }
    return null;
};

const seekTo = (seconds) => {
    if (player.value && playerReady.value) {
        player.value.seekTo(seconds, true);
    }
};

const formatTimestamp = (seconds) => {
    if (seconds === null) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const addTimestamp = () => {
    currentTimestamp.value = getCurrentTime();
};

const clearTimestamp = () => {
    currentTimestamp.value = null;
};

// Save new note
const saveNote = async () => {
    if (!newNoteContent.value.trim()) return;

    isSaving.value = true;

    try {
        const response = await fetch(route('notes.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                video_id: props.video.id,
                content: newNoteContent.value,
                timestamp: currentTimestamp.value,
                tags: selectedTags.value,
            }),
        });

        const note = await response.json();
        notes.value.unshift(note);
        newNoteContent.value = '';
        currentTimestamp.value = null;
        selectedTags.value = [];
    } catch (error) {
        console.error('Error saving note:', error);
    } finally {
        isSaving.value = false;
    }
};

// Edit note
const startEditing = (note) => {
    editingNoteId.value = note.id;
    editingContent.value = note.content;
};

const cancelEditing = () => {
    editingNoteId.value = null;
    editingContent.value = '';
};

const updateNote = async (note) => {
    try {
        const response = await fetch(route('notes.update', note.id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                content: editingContent.value,
                timestamp: note.timestamp,
                tags: note.tags.map(t => t.id),
            }),
        });

        const updatedNote = await response.json();
        const index = notes.value.findIndex(n => n.id === note.id);
        if (index !== -1) {
            notes.value[index] = updatedNote;
        }
        editingNoteId.value = null;
        editingContent.value = '';
    } catch (error) {
        console.error('Error updating note:', error);
    }
};

// Delete note
const deleteNote = async (noteId) => {
    if (!confirm('Are you sure you want to delete this note?')) return;

    try {
        await fetch(route('notes.destroy', noteId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        notes.value = notes.value.filter(n => n.id !== noteId);
    } catch (error) {
        console.error('Error deleting note:', error);
    }
};

// Create new tag
const createTag = async () => {
    if (!newTagName.value.trim()) return;

    try {
        const response = await fetch(route('tags.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                name: newTagName.value,
                color: newTagColor.value,
            }),
        });

        const tag = await response.json();
        availableTags.value.push(tag);
        selectedTags.value.push(tag.id);
        newTagName.value = '';
        showTagInput.value = false;
    } catch (error) {
        console.error('Error creating tag:', error);
    }
};

const toggleTag = (tagId) => {
    const index = selectedTags.value.indexOf(tagId);
    if (index === -1) {
        selectedTags.value.push(tagId);
    } else {
        selectedTags.value.splice(index, 1);
    }
};

// Sort notes by timestamp
const sortedNotes = computed(() => {
    return [...notes.value].sort((a, b) => {
        if (a.timestamp === null && b.timestamp === null) return 0;
        if (a.timestamp === null) return 1;
        if (b.timestamp === null) return -1;
        return a.timestamp - b.timestamp;
    });
});
</script>

<template>
    <Head :title="video.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('videos.index')"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight truncate">
                    {{ video.title }}
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Video Player -->
                    <div class="lg:w-2/3">
                        <div class="bg-black rounded-lg overflow-hidden aspect-video">
                            <div id="youtube-player" class="w-full h-full"></div>
                        </div>
                        <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ video.title }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ video.channel_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="lg:w-1/3">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm h-full flex flex-col">
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Notes
                                </h3>
                            </div>

                            <!-- New Note Form -->
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                <textarea
                                    v-model="newNoteContent"
                                    placeholder="Write a note..."
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 text-sm"
                                ></textarea>

                                <!-- Timestamp -->
                                <div class="flex items-center gap-2 mt-2">
                                    <button
                                        @click="addTimestamp"
                                        class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded text-gray-700 dark:text-gray-300 transition-colors"
                                    >
                                        + Add timestamp
                                    </button>
                                    <span
                                        v-if="currentTimestamp !== null"
                                        class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-1 rounded flex items-center gap-1"
                                    >
                                        {{ formatTimestamp(currentTimestamp) }}
                                        <button @click="clearTimestamp" class="hover:text-blue-900 dark:hover:text-blue-100">×</button>
                                    </span>
                                </div>

                                <!-- Tags Selection -->
                                <div class="mt-3">
                                    <div class="flex flex-wrap gap-1">
                                        <button
                                            v-for="tag in availableTags"
                                            :key="tag.id"
                                            @click="toggleTag(tag.id)"
                                            :style="{
                                                backgroundColor: selectedTags.includes(tag.id) ? tag.color : tag.color + '20',
                                                color: selectedTags.includes(tag.id) ? 'white' : tag.color
                                            }"
                                            class="px-2 py-0.5 text-xs rounded-full transition-colors"
                                        >
                                            {{ tag.name }}
                                        </button>
                                        <button
                                            @click="showTagInput = !showTagInput"
                                            class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600"
                                        >
                                            + New tag
                                        </button>
                                    </div>

                                    <!-- New Tag Input -->
                                    <div v-if="showTagInput" class="mt-2 flex gap-2">
                                        <input
                                            v-model="newTagName"
                                            type="text"
                                            placeholder="Tag name"
                                            class="flex-1 text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                        />
                                        <input
                                            v-model="newTagColor"
                                            type="color"
                                            class="w-8 h-8 rounded cursor-pointer"
                                        />
                                        <button
                                            @click="createTag"
                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>

                                <!-- Save Button -->
                                <button
                                    @click="saveNote"
                                    :disabled="isSaving || !newNoteContent.trim()"
                                    class="mt-3 w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition-colors text-sm"
                                >
                                    {{ isSaving ? 'Saving...' : 'Save Note' }}
                                </button>
                            </div>

                            <!-- Notes List -->
                            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                                <div v-if="sortedNotes.length === 0" class="text-gray-500 dark:text-gray-400 text-sm text-center py-8">
                                    No notes yet. Start taking notes!
                                </div>
                                <div
                                    v-for="note in sortedNotes"
                                    :key="note.id"
                                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                                >
                                    <!-- Timestamp -->
                                    <button
                                        v-if="note.timestamp !== null"
                                        @click="seekTo(note.timestamp)"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-mono mb-1"
                                    >
                                        {{ formatTimestamp(note.timestamp) }}
                                    </button>

                                    <!-- Note Content -->
                                    <div v-if="editingNoteId === note.id">
                                        <textarea
                                            v-model="editingContent"
                                            rows="3"
                                            class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-100"
                                        ></textarea>
                                        <div class="flex gap-2 mt-2">
                                            <button
                                                @click="updateNote(note)"
                                                class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                            >
                                                Save
                                            </button>
                                            <button
                                                @click="cancelEditing"
                                                class="text-xs px-2 py-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-400 dark:hover:bg-gray-500"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                        {{ note.content }}
                                    </p>

                                    <!-- Tags -->
                                    <div v-if="note.tags && note.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                        <span
                                            v-for="tag in note.tags"
                                            :key="tag.id"
                                            :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                            class="px-2 py-0.5 text-xs rounded-full"
                                        >
                                            {{ tag.name }}
                                        </span>
                                    </div>

                                    <!-- Actions -->
                                    <div v-if="editingNoteId !== note.id" class="flex gap-2 mt-2">
                                        <button
                                            @click="startEditing(note)"
                                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteNote(note.id)"
                                            class="text-xs text-red-500 hover:text-red-700"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>