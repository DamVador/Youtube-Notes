<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TiptapEditor from '@/Components/TiptapEditor.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch, onBeforeUnmount } from 'vue';
import html2pdf from 'html2pdf.js';
import PresentationMode from '@/Components/PresentationMode.vue';
import QuickNotesPanel from '@/Components/Video/QuickNotesPanel.vue';
import TagSelector from '@/Components/Video/TagSelector.vue';
import LimitError from '@/Components/Video/LimitError.vue';
import QuickNotesInline from '@/Components/Video/QuickNotesInline.vue';
import QuickNotesButton from '@/Components/Video/QuickNotesButton.vue';

const props = defineProps({
    video: Object,
});

// Video width (persisted in localStorage)
const videoWidth = ref(500);
const minWidth = 300;
const maxWidth = 800;
const isResizing = ref(false);

const limitError = ref(null);

// Threshold for collapsed mode
const collapsedThreshold = 500;

// Mobile/Collapsed Quick Notes panel
const showQuickNotesPanel = ref(false);

const lastPositionSave = ref(0);

const showPresentation = ref(false);

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
    window.addEventListener('beforeunload', savePosition);
});

onBeforeUnmount(() => {
    savePosition();
    window.removeEventListener('beforeunload', savePosition);
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

// Edit Quick Note
const editingNoteId = ref(null);
const editingNoteContent = ref('');
const editingNoteTimestamp = ref(null);

// Tags
const availableTags = ref([]);
const selectedDocTags = ref([]);

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
            start: props.video.last_position || 0,
        },
        events: {
            onReady: () => {
                playerReady.value = true;
            },
            onStateChange: onPlayerStateChange,
        },
    });
};

const savePosition = async () => {
    if (!player.value || !playerReady.value) return;
    
    const currentTime = Math.floor(player.value.getCurrentTime());
    
    if (Math.abs(currentTime - lastPositionSave.value) < 5) return;
    
    lastPositionSave.value = currentTime;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        await fetch(route('videos.updatePosition', props.video.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ position: currentTime }),
        });
    } catch (error) {
        console.error('Error saving position:', error);
    }
};

const onPlayerStateChange = (event) => {
    if (event.data === 2 || event.data === 0) {
        savePosition();
    }
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
    limitError.value = null;

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

        const data = await response.json();

        if (!response.ok) {
            if (data.error === 'limit') {
                limitError.value = data.message;
                return;
            }
            throw new Error('Failed to save note');
        }

        notes.value.unshift(data);
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

const createTag = async (tagData) => {
    limitError.value = null;

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
            body: JSON.stringify(tagData),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.error === 'limit') {
                limitError.value = data.message;
                return;
            }
            throw new Error('Failed to create tag');
        }

        availableTags.value.push(data);
        selectedDocTags.value.push(data.id);
        saveDocument();
    } catch (error) {
        console.error('Error creating tag:', error);
    }
};

const deleteTag = async (tag) => {
    if (!confirm(`Delete tag "${tag.name}"?`)) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        await fetch(route('tags.destroy', tag.id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        availableTags.value = availableTags.value.filter(t => t.id !== tag.id);
        selectedDocTags.value = selectedDocTags.value.filter(id => id !== tag.id);
    } catch (error) {
        console.error('Error deleting tag:', error);
    }
};

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

const exportToPdf = () => {
    const content = documentContent.value;
    if (!content || content === '<p></p>') {
        alert('No content to export');
        return;
    }

    const container = document.createElement('div');
    container.innerHTML = `
        <div style="font-family: Arial, sans-serif; padding: 20px;">
            <h1 style="font-size: 18px; margin-bottom: 5px; color: #1f2937;">${props.video.title}</h1>
            <p style="font-size: 12px; color: #6b7280; margin-bottom: 20px;">${props.video.channel_name}</p>
            <hr style="border: none; border-top: 1px solid #e5e7eb; margin-bottom: 20px;">
            <div style="font-size: 14px; line-height: 1.6; color: #374151;">
                ${content}
            </div>
        </div>
    `;

    container.querySelectorAll('.timestamp-link').forEach(link => {
        link.style.backgroundColor = '#dbeafe';
        link.style.color = '#1d4ed8';
        link.style.padding = '2px 6px';
        link.style.borderRadius = '4px';
        link.style.fontFamily = 'monospace';
        link.style.fontSize = '12px';
        link.style.textDecoration = 'none';
    });

    const opt = {
        margin: 10,
        filename: `${props.video.title.substring(0, 50).replace(/[^a-z0-9]/gi, '_')}_notes.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(container).save();
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

                        <!-- Limit Error -->
                        <LimitError
                            v-if="limitError"
                            :message="limitError"
                            @dismiss="limitError = null"
                        />

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
                        <QuickNotesInline
                            v-if="!isQuickNotesCollapsed"
                            :notes="notes"
                            :new-note-content="newNoteContent"
                            :current-timestamp="currentTimestamp"
                            :is-saving-note="isSavingNote"
                            :editing-note-id="editingNoteId"
                            :editing-note-content="editingNoteContent"
                            :editing-note-timestamp="editingNoteTimestamp"
                            @update:new-note-content="newNoteContent = $event"
                            @update:editing-note-content="editingNoteContent = $event"
                            @add-timestamp="addTimestamp"
                            @clear-timestamp="clearTimestamp"
                            @save-note="saveQuickNote"
                            @seek-to="seekTo"
                            @start-edit="startEditNote"
                            @cancel-edit="cancelEditNote"
                            @update-edit-timestamp="updateEditTimestamp"
                            @save-edit="saveEditNote"
                            @delete-note="deleteNote"
                        />

                        <!-- Quick Notes: Collapsed Button (when video is wide) -->
                        <QuickNotesButton
                            v-if="isQuickNotesCollapsed"
                            :notes-count="notes.length"
                            @click="showQuickNotesPanel = true"
                        />

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
                                    <button
                                        @click="exportToPdf"
                                        class="text-xs px-3 py-1.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Export PDF
                                    </button>

                                    <button
                                        @click="showPresentation = true"
                                        class="text-xs px-3 py-1.5 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors flex items-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0h3a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h3m0 0V2m0 2h8" />
                                        </svg>
                                        Presentation
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
                            <TagSelector
                                :available-tags="availableTags"
                                :selected-tags="selectedDocTags"
                                @toggle="toggleDocTag"
                                @create="createTag"
                                @delete="deleteTag"
                            />

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
                                <button
                                    @click="exportToPdf"
                                    class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                                >
                                    PDF
                                </button>

                                <button
                                    @click="showPresentation = true"
                                    class="text-xs px-2 py-1 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300"
                                    title="Presentation Mode"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0h3a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h3m0 0V2m0 2h8" />
                                    </svg>
                                </button>
                                <span v-if="lastSaved" class="text-xs text-green-600 dark:text-green-400">✓</span>
                            </div>
                        </div>

                        <!-- Tags -->
                        <TagSelector
                            :available-tags="availableTags"
                            :selected-tags="selectedDocTags"
                            :show-label="false"
                            @toggle="toggleDocTag"
                            @create="createTag"
                            @delete="deleteTag"
                        />

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

                <!-- QUICK NOTES PANEL (extracted component) -->
                <QuickNotesPanel
                    :show="showQuickNotesPanel"
                    :notes="notes"
                    :new-note-content="newNoteContent"
                    :current-timestamp="currentTimestamp"
                    :is-saving-note="isSavingNote"
                    :editing-note-id="editingNoteId"
                    :editing-note-content="editingNoteContent"
                    :editing-note-timestamp="editingNoteTimestamp"
                    @close="showQuickNotesPanel = false"
                    @update:new-note-content="newNoteContent = $event"
                    @update:editing-note-content="editingNoteContent = $event"
                    @add-timestamp="addTimestamp"
                    @clear-timestamp="clearTimestamp"
                    @save-note="saveQuickNote"
                    @seek-to="seekTo"
                    @start-edit="startEditNote"
                    @cancel-edit="cancelEditNote"
                    @update-edit-timestamp="updateEditTimestamp"
                    @save-edit="saveEditNote"
                    @delete-note="deleteNote"
                />
            </div>
        </div>
    </AuthenticatedLayout>

    <PresentationMode
        v-if="showPresentation"
        :content="documentContent"
        :video-title="video.title"
        @close="showPresentation = false"
    />
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
</style>