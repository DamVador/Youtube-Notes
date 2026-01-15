<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

const props = defineProps({
    video: Object,
});

// Video width (persisted in localStorage)
const videoWidth = ref(500);
const minWidth = 300;
const maxWidth = 800;
const isResizing = ref(false);

// Threshold for collapsed mode
const collapsedThreshold = 500;

// Mobile/Collapsed Quick Notes panel
const showQuickNotesPanel = ref(false);

// Computed: should Quick Notes be collapsed into a button?
const isQuickNotesCollapsed = computed(() => {
    return videoWidth.value >= collapsedThreshold;
});

// Load saved width from localStorage
onMounted(() => {
    const savedWidth = localStorage.getItem('videoWidth');
    if (savedWidth) {
        videoWidth.value = Math.min(Math.max(parseInt(savedWidth), minWidth), maxWidth);
    }
});

// Save width to localStorage when changed
watch(videoWidth, (newWidth) => {
    localStorage.setItem('videoWidth', newWidth.toString());
});

const startResize = (e) => {
    isResizing.value = true;
    document.addEventListener('mousemove', onResize);
    document.addEventListener('mouseup', stopResize);
    e.preventDefault();
};

const onResize = (e) => {
    if (!isResizing.value) return;
    const container = document.querySelector('.video-container');
    if (container) {
        const rect = container.getBoundingClientRect();
        const newWidth = e.clientX - rect.left;
        videoWidth.value = Math.min(Math.max(newWidth, minWidth), maxWidth);
    }
};

const stopResize = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', onResize);
    document.removeEventListener('mouseup', stopResize);
};

// Document (éditeur riche)
const documentContent = ref('');
const isSavingDocument = ref(false);
const lastSaved = ref(null);
const editorRef = ref(null);

// Quick Notes
const notes = ref(props.video.notes || []);
const newNoteContent = ref('');
const currentTimestamp = ref(null);
const isSavingNote = ref(false);
const showQuickNotes = ref(true);

// Edit Quick Note
const editingNoteId = ref(null);
const editingNoteContent = ref('');
const editingNoteTimestamp = ref(null);

// Tags
const availableTags = ref([]);
const selectedDocTags = ref([]);
const showTagInput = ref(false);
const newTagName = ref('');
const newTagColor = ref('#3B82F6');

// YouTube Player
const player = ref(null);
const playerReady = ref(false);

onMounted(async () => {
    await loadTags();
    await loadDocument();
    initYouTubePlayer();
});

const loadTags = async () => {
    try {
        const response = await fetch(route('tags.index'));
        availableTags.value = await response.json();
    } catch (error) {
        console.error('Error loading tags:', error);
    }
};

const loadDocument = async () => {
    try {
        const response = await fetch(route('documents.show', props.video.id));
        if (response.ok) {
            const doc = await response.json();
            if (doc) {
                documentContent.value = doc.content || '';
                selectedDocTags.value = doc.tags?.map(t => t.id) || [];
            }
        }
    } catch (error) {
        console.error('Error loading document:', error);
    }
};

const initYouTubePlayer = () => {
    if (!window.YT) {
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    window.onYouTubeIframeAPIReady = createPlayer;

    if (window.YT && window.YT.Player) {
        createPlayer();
    }
};

const createPlayer = () => {
    const playerId = window.innerWidth >= 1024 ? 'youtube-player-desktop' : 'youtube-player';
    player.value = new window.YT.Player(playerId, {
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
    return 0;
};

const seekTo = (seconds) => {
    if (player.value && playerReady.value) {
        player.value.seekTo(seconds, true);
    }
};

const formatTimestamp = (seconds) => {
    if (seconds === null || seconds === undefined) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

// Document functions
let saveTimeout = null;
const autoSaveDocument = () => {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        saveDocument();
    }, 2000);
};

const saveDocument = async () => {
    isSavingDocument.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(route('documents.store', props.video.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                content: documentContent.value,
                content_json: editorRef.value?.getJSON(),
                tags: selectedDocTags.value,
            }),
        });

        if (response.ok) {
            lastSaved.value = new Date();
        }
    } catch (error) {
        console.error('Error saving document:', error);
    } finally {
        isSavingDocument.value = false;
    }
};

const insertTimestampInEditor = () => {
    const time = getCurrentTime();
    editorRef.value?.insertTimestamp(time);
    autoSaveDocument();
};

// Quick Notes functions
const addTimestamp = () => {
    currentTimestamp.value = getCurrentTime();
};

const clearTimestamp = () => {
    currentTimestamp.value = null;
};

const saveQuickNote = async () => {
    if (!newNoteContent.value.trim()) return;

    isSavingNote.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(route('notes.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                video_id: props.video.id,
                content: newNoteContent.value,
                timestamp: currentTimestamp.value,
            }),
        });

        const note = await response.json();
        notes.value.unshift(note);
        newNoteContent.value = '';
        currentTimestamp.value = null;
    } catch (error) {
        console.error('Error saving note:', error);
    } finally {
        isSavingNote.value = false;
    }
};

const startEditNote = (note) => {
    editingNoteId.value = note.id;
    editingNoteContent.value = note.content;
    editingNoteTimestamp.value = note.timestamp;
};

const cancelEditNote = () => {
    editingNoteId.value = null;
    editingNoteContent.value = '';
    editingNoteTimestamp.value = null;
};

const updateEditTimestamp = () => {
    editingNoteTimestamp.value = getCurrentTime();
};

const saveEditNote = async () => {
    if (!editingNoteContent.value.trim()) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(route('notes.update', editingNoteId.value), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                content: editingNoteContent.value,
                timestamp: editingNoteTimestamp.value,
            }),
        });

        const updatedNote = await response.json();
        const index = notes.value.findIndex(n => n.id === editingNoteId.value);
        if (index !== -1) {
            notes.value[index] = updatedNote;
        }
        cancelEditNote();
    } catch (error) {
        console.error('Error updating note:', error);
    }
};

const deleteNote = async (noteId) => {
    if (!confirm('Delete this quick note?')) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        await fetch(route('notes.destroy', noteId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        notes.value = notes.value.filter(n => n.id !== noteId);
    } catch (error) {
        console.error('Error deleting note:', error);
    }
};

// Tag functions
const toggleDocTag = (tagId) => {
    const index = selectedDocTags.value.indexOf(tagId);
    if (index === -1) {
        selectedDocTags.value.push(tagId);
    } else {
        selectedDocTags.value.splice(index, 1);
    }
    saveDocument();
};

const createTag = async () => {
    if (!newTagName.value.trim()) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(route('tags.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                name: newTagName.value,
                color: newTagColor.value,
            }),
        });

        const tag = await response.json();
        availableTags.value.push(tag);
        selectedDocTags.value.push(tag.id);
        newTagName.value = '';
        showTagInput.value = false;
        saveDocument();
    } catch (error) {
        console.error('Error creating tag:', error);
    }
};

const sortedNotes = computed(() => {
    return [...notes.value].sort((a, b) => {
        if (a.timestamp === null && b.timestamp === null) return 0;
        if (a.timestamp === null) return 1;
        if (b.timestamp === null) return -1;
        return a.timestamp - b.timestamp;
    });
});

const handleEditorClick = (event) => {
    const target = event.target;
    if (target.classList.contains('timestamp-link') || target.closest('.timestamp-link')) {
        const link = target.classList.contains('timestamp-link') ? target : target.closest('.timestamp-link');
        const timestamp = link.dataset.timestamp;
        if (timestamp) {
            seekTo(parseInt(timestamp));
        }
        event.preventDefault();
    }
};
</script>

<template>
    <Head :title="video.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('videos.index')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ video.title }}
                </h2>
            </div>
        </template>

        <div class="py-4 lg:py-6 min-h-screen">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6">
                
                <!-- DESKTOP LAYOUT -->
                <div class="hidden lg:flex gap-6 h-[calc(100vh-140px)]">
                    
                    <!-- LEFT COLUMN: Video + Quick Notes (resizable) -->
                    <div 
                        class="video-container flex-shrink-0 relative flex flex-col"
                        :style="{ width: `${videoWidth}px` }"
                    >
                        <!-- Video Player -->
                        <div class="bg-black rounded-lg overflow-hidden aspect-video shadow-lg flex-shrink-0">
                            <div id="youtube-player-desktop" class="w-full h-full"></div>
                        </div>

                        <!-- Video Info -->
                        <div class="mt-3 px-1 flex-shrink-0">
                            <h1 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">
                                {{ video.title }}
                            </h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ video.channel_name }}
                            </p>
                        </div>

                        <!-- Width Slider -->
                        <div class="mt-3 px-1 flex-shrink-0">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                </svg>
                                <input
                                    type="range"
                                    :min="minWidth"
                                    :max="maxWidth"
                                    v-model="videoWidth"
                                    class="flex-1 h-1 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
                                />
                                <span class="text-xs text-gray-500 dark:text-gray-400 w-12 text-right">{{ videoWidth }}px</span>
                            </div>
                        </div>

                        <!-- Quick Notes: Inline (when not collapsed) -->
                        <div 
                            v-if="!isQuickNotesCollapsed"
                            class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex-1 flex flex-col min-h-0 overflow-hidden"
                        >
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
                                    v-model="newNoteContent"
                                    placeholder="Add a quick note..."
                                    rows="2"
                                    class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500 resize-none"
                                ></textarea>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="addTimestamp"
                                            class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                                        >
                                            + Timestamp
                                        </button>
                                        <span
                                            v-if="currentTimestamp !== null"
                                            class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300"
                                        >
                                            {{ formatTimestamp(currentTimestamp) }}
                                            <button @click="clearTimestamp" class="hover:text-blue-900 dark:hover:text-blue-100">×</button>
                                        </span>
                                    </div>
                                    <button
                                        @click="saveQuickNote"
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
                                            v-model="editingNoteContent"
                                            rows="2"
                                            class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 resize-none"
                                        ></textarea>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    @click="updateEditTimestamp"
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
                                                    @click="cancelEditNote"
                                                    class="text-xs px-2 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                >
                                                    Cancel
                                                </button>
                                                <button
                                                    @click="saveEditNote"
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
                                            @click="seekTo(note.timestamp)"
                                            class="text-xs font-mono text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                        >
                                            {{ formatTimestamp(note.timestamp) }}
                                        </button>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">
                                            {{ note.content }}
                                        </p>
                                        <div class="flex items-center gap-3 mt-1">
                                            <button
                                                @click="startEditNote(note)"
                                                class="text-xs text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteNote(note.id)"
                                                class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Notes: Collapsed Button (when video is wide) -->
                        <button
                            v-if="isQuickNotesCollapsed"
                            @click="showQuickNotesPanel = true"
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
                                    {{ notes.length }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </button>

                        <!-- Resize Handle -->
                        <div
                            @mousedown="startResize"
                            class="absolute top-0 right-0 w-1 h-full cursor-ew-resize hover:bg-blue-500 transition-colors group"
                            :class="{ 'bg-blue-500': isResizing }"
                        >
                            <div class="absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2 w-4 h-8 bg-gray-300 dark:bg-gray-600 rounded opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <svg class="w-2 h-4 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 6 16">
                                    <path d="M0 0h2v16H0zM4 0h2v16H4z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Editor (fixed height) -->
                    <div class="flex-1 min-w-0 flex flex-col h-full">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col h-full">
                            <!-- Editor Header -->
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800 flex-shrink-0">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    Notes
                                </h3>
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="insertTimestampInEditor"
                                        class="text-xs px-3 py-1.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors"
                                    >
                                        + Insert Timestamp
                                    </button>
                                    <span v-if="isSavingDocument" class="text-xs text-gray-500 dark:text-gray-400">
                                        Saving...
                                    </span>
                                    <span v-else-if="lastSaved" class="text-xs text-green-600 dark:text-green-400">
                                        ✓ Saved
                                    </span>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex-shrink-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Tags:</span>
                                    <button
                                        v-for="tag in availableTags"
                                        :key="tag.id"
                                        @click="toggleDocTag(tag.id)"
                                        :style="{
                                            backgroundColor: selectedDocTags.includes(tag.id) ? tag.color : 'transparent',
                                            color: selectedDocTags.includes(tag.id) ? 'white' : tag.color,
                                            borderColor: tag.color
                                        }"
                                        class="px-2 py-0.5 text-xs rounded-full border transition-colors"
                                    >
                                        {{ tag.name }}
                                    </button>
                                    <button
                                        @click="showTagInput = !showTagInput"
                                        class="px-2 py-0.5 text-xs rounded-full border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                                    >
                                        + New
                                    </button>

                                    <div v-if="showTagInput" class="flex items-center gap-2 ml-2">
                                        <input
                                            v-model="newTagName"
                                            type="text"
                                            placeholder="Tag name"
                                            class="text-xs rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-2 py-1 w-24"
                                            @keyup.enter="createTag"
                                        />
                                        <input
                                            v-model="newTagColor"
                                            type="color"
                                            class="w-6 h-6 rounded cursor-pointer border-0"
                                        />
                                        <button
                                            @click="createTag"
                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Editor (scrollable) -->
                            <div @click="handleEditorClick" class="flex-1 overflow-y-auto">
                                <TiptapEditor
                                    ref="editorRef"
                                    v-model="documentContent"
                                    @update:modelValue="autoSaveDocument"
                                    placeholder="Start writing your notes..."
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MOBILE/TABLET LAYOUT -->
                <div class="lg:hidden">
                    <!-- Video Player (full width) -->
                    <div class="bg-black rounded-lg overflow-hidden aspect-video shadow-lg">
                        <div id="youtube-player" class="w-full h-full"></div>
                    </div>

                    <!-- Video Info -->
                    <div class="mt-3 px-1">
                        <h1 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">
                            {{ video.title }}
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ video.channel_name }}
                        </p>
                    </div>

                    <!-- Editor -->
                    <div class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <!-- Editor Header -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Notes
                            </h3>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="insertTimestampInEditor"
                                    class="text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300"
                                >
                                    + Timestamp
                                </button>
                                <span v-if="lastSaved" class="text-xs text-green-600 dark:text-green-400">✓</span>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    v-for="tag in availableTags"
                                    :key="tag.id"
                                    @click="toggleDocTag(tag.id)"
                                    :style="{
                                        backgroundColor: selectedDocTags.includes(tag.id) ? tag.color : 'transparent',
                                        color: selectedDocTags.includes(tag.id) ? 'white' : tag.color,
                                        borderColor: tag.color
                                    }"
                                    class="px-2 py-0.5 text-xs rounded-full border transition-colors"
                                >
                                    {{ tag.name }}
                                </button>
                                <button
                                    @click="showTagInput = !showTagInput"
                                    class="px-2 py-0.5 text-xs rounded-full border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Editor -->
                        <div @click="handleEditorClick" class="min-h-[400px]">
                            <TiptapEditor
                                ref="editorRef"
                                v-model="documentContent"
                                @update:modelValue="autoSaveDocument"
                                placeholder="Start writing your notes..."
                            />
                        </div>
                    </div>

                    <!-- Mobile Quick Notes FAB Button -->
                    <button
                        @click="showQuickNotesPanel = true"
                        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                        <span v-if="notes.length > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                            {{ notes.length }}
                        </span>
                    </button>
                </div>

                <!-- QUICK NOTES PANEL (shared between collapsed desktop and mobile) -->
                <Teleport to="body">
                    <Transition name="sheet">
                        <div
                            v-if="showQuickNotesPanel"
                            class="fixed inset-0 z-50"
                        >
                            <!-- Backdrop -->
                            <div
                                @click="showQuickNotesPanel = false"
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
                                        @click="showQuickNotesPanel = false"
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
                                        v-model="newNoteContent"
                                        placeholder="Add a quick note..."
                                        rows="2"
                                        class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500 resize-none"
                                    ></textarea>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-2">
                                            <button
                                                @click="addTimestamp"
                                                class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                                            >
                                                + Timestamp
                                            </button>
                                            <span
                                                v-if="currentTimestamp !== null"
                                                class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300"
                                            >
                                                {{ formatTimestamp(currentTimestamp) }}
                                                <button @click="clearTimestamp" class="hover:text-blue-900 dark:hover:text-blue-100">×</button>
                                            </span>
                                        </div>
                                        <button
                                            @click="saveQuickNote"
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
                                        class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                    >
                                        <!-- Edit Mode -->
                                        <div v-if="editingNoteId === note.id" class="space-y-2">
                                            <textarea
                                                v-model="editingNoteContent"
                                                rows="2"
                                                class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 resize-none"
                                            ></textarea>
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <button
                                                        @click="updateEditTimestamp"
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
                                                        @click="cancelEditNote"
                                                        class="text-xs px-2 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button
                                                        @click="saveEditNote"
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
                                                @click="seekTo(note.timestamp)"
                                                class="text-xs font-mono text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                            >
                                                {{ formatTimestamp(note.timestamp) }}
                                            </button>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">
                                                {{ note.content }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-1">
                                                <button
                                                    @click="startEditNote(note)"
                                                    class="text-xs text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    @click="deleteNote(note.id)"
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.slider::-webkit-slider-thumb {
    appearance: none;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
}

.slider::-moz-range-thumb {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #3b82f6;
    cursor: pointer;
    border: none;
}

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