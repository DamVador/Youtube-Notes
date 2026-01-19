<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    notes: Object,
    tags: Array,
    videos: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedTag = ref(props.filters.tag ? props.filters.tag.toString() : '');
const selectedVideo = ref(props.filters.video || '');

let searchTimeout = null;

const applyFilters = () => {
    router.get(route('notes.index'), {
        search: search.value || undefined,
        tag: selectedTag.value || undefined,
        video: selectedVideo.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch([selectedTag, selectedVideo], applyFilters);

const clearFilters = () => {
    search.value = '';
    selectedTag.value = '';
    selectedVideo.value = '';
    applyFilters();
};

const formatTimestamp = (seconds) => {
    if (seconds === null || seconds === undefined) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const hasActiveFilters = () => {
    return search.value || selectedTag.value || selectedVideo.value;
};

const highlightSearch = (text, query) => {
    if (!query || !text) return text;
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 px-0.5 rounded">$1</mark>');
};

const deleteNote = async (note) => {
    if (note.type === 'document') {
        alert('Documents can only be edited from the video page');
        return;
    }
    
    if (!confirm('Delete this quick note?')) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        await fetch(route('notes.destroy', note.id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        router.reload({ only: ['notes'] });
    } catch (error) {
        console.error('Error deleting note:', error);
    }
};

const deleteTag = async (tag) => {
    const noteCount = (tag.notes_count || 0) + (tag.documents_count || 0);
    const message = noteCount > 0 
        ? `Delete tag "${tag.name}"? It will be removed from ${noteCount} note(s).`
        : `Delete tag "${tag.name}"?`;
    
    if (!confirm(message)) return;

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

        // Si le tag supprimé était sélectionné, on le désélectionne
        if (selectedTag.value === tag.id.toString()) {
            selectedTag.value = '';
        }

        router.reload({ only: ['tags', 'notes'] });
    } catch (error) {
        console.error('Error deleting tag:', error);
    }
};
</script>

<template>
    <Head title="My Notes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                My Notes
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search notes..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Tag Filter -->
                        <select
                            v-model="selectedTag"
                            class="rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All Tags</option>
                            <option v-for="tag in tags" :key="tag.id" :value="tag.id.toString()">
                                {{ tag.name }} ({{ (tag.notes_count || 0) + (tag.documents_count || 0) }})
                            </option>
                        </select>

                        <!-- Video Filter -->
                        <select
                            v-model="selectedVideo"
                            class="rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">All Videos</option>
                            <option v-for="video in videos" :key="video.id" :value="video.id">
                                {{ video.title }}
                            </option>
                        </select>
                    </div>

                    <!-- Active Filters -->
                    <div v-if="hasActiveFilters()" class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
                        <button
                            @click="clearFilters"
                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                        >
                            Clear all
                        </button>
                    </div>

                    <!-- Quick Tag Filters -->
                    <div v-if="tags.length > 0" class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div
                            v-for="tag in tags"
                            :key="tag.id"
                            class="group relative flex items-center"
                        >
                            <button
                                @click="selectedTag = selectedTag === tag.id.toString() ? '' : tag.id.toString()"
                                :style="{
                                    backgroundColor: selectedTag === tag.id.toString() ? tag.color : 'transparent',
                                    color: selectedTag === tag.id.toString() ? 'white' : tag.color,
                                    borderColor: tag.color
                                }"
                                class="px-3 py-1 text-sm rounded-full border transition-colors pr-7"
                            >
                                {{ tag.name }}
                            </button>
                            <!-- Delete button -->
                            <button
                                @click.stop="deleteTag(tag)"
                                class="absolute right-1.5 w-4 h-4 flex items-center justify-center rounded-full opacity-50 hover:opacity-100 transition-opacity"
                                :style="{ color: selectedTag === tag.id.toString() ? 'white' : tag.color }"
                                title="Delete tag"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results Count -->
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ notes.total }} result{{ notes.total !== 1 ? 's' : '' }}
                </p>

                <!-- Notes List -->
                <div v-if="notes.data.length === 0" class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">No notes found</p>
                    <p v-if="hasActiveFilters()" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                        Try adjusting your filters
                    </p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="note in notes.data"
                        :key="`${note.type}-${note.id}`"
                        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <div class="flex">
                            <!-- Video Thumbnail -->
                            <Link
                                v-if="note.video"
                                :href="route('videos.show', note.video.id)"
                                class="flex-shrink-0"
                            >
                                <img
                                    :src="note.video.thumbnail"
                                    :alt="note.video.title"
                                    class="w-40 h-24 object-cover"
                                />
                            </Link>

                            <!-- Note Content -->
                            <div class="flex-1 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <!-- Type & Timestamp -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <span
                                                v-if="note.type === 'document'"
                                                class="text-xs px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded font-medium"
                                            >
                                                Document
                                            </span>
                                            <span
                                                v-else
                                                class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded font-medium"
                                            >
                                                Quick Note
                                            </span>
                                            <Link
                                                v-if="note.type === 'quick_note' && note.timestamp !== null && note.video"
                                                :href="route('videos.show', note.video.id)"
                                                class="text-xs font-mono text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                            >
                                                {{ formatTimestamp(note.timestamp) }}
                                            </Link>
                                        </div>

                                        <!-- Video Title -->
                                        <Link
                                            v-if="note.video"
                                            :href="route('videos.show', note.video.id)"
                                            class="text-xs text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 block mb-1"
                                        >
                                            {{ note.video.title }}
                                        </Link>

                                        <!-- Note Content -->
                                        <p
                                            class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3"
                                            v-html="highlightSearch(note.type === 'document' ? note.content_preview : (note.content?.substring(0, 200) + (note.content?.length > 200 ? '...' : '')), search)"
                                        ></p>

                                        <!-- Tags -->
                                        <div v-if="note.tags && note.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                            <button
                                                v-for="tag in note.tags"
                                                :key="tag.id"
                                                @click.prevent="selectedTag = tag.id.toString()"
                                                :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                                class="text-xs px-2 py-0.5 rounded hover:opacity-80 transition-opacity"
                                            >
                                                {{ tag.name }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="note.video"
                                            :href="route('videos.show', note.video.id)"
                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            View
                                        </Link>
                                        <button
                                            v-if="note.type === 'quick_note'"
                                            @click="deleteNote(note)"
                                            class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="notes.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <Link
                        v-for="page in notes.last_page"
                        :key="page"
                        :href="route('notes.index', { ...filters, page })"
                        :class="[
                            'px-3 py-1 rounded text-sm',
                            page === notes.current_page
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                        ]"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>