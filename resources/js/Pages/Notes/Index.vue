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
const selectedTag = ref(props.filters.tag || '');
const selectedVideo = ref(props.filters.video || '');

// Debounce search
let searchTimeout = null;
const performSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('notes.index'), {
            search: search.value || undefined,
            tag: selectedTag.value || undefined,
            video: selectedVideo.value || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

watch([search, selectedTag, selectedVideo], performSearch);

const clearFilters = () => {
    search.value = '';
    selectedTag.value = '';
    selectedVideo.value = '';
};

const formatTimestamp = (seconds) => {
    if (seconds === null) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const highlightText = (text, query) => {
    if (!query || !text) return text;
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 px-0.5 rounded">$1</mark>');
};

const deleteNote = async (noteId) => {
    if (!confirm('Are you sure you want to delete this note?')) return;

    try {
        await fetch(route('notes.destroy', noteId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        router.reload();
    } catch (error) {
        console.error('Error deleting note:', error);
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-8">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Search Input -->
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Search in notes
                                </label>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by keyword..."
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Filter by Tag -->
                            <div class="md:w-48">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Filter by tag
                                </label>
                                <select
                                    v-model="selectedTag"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">All tags</option>
                                    <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                                        {{ tag.name }} ({{ tag.notes_count }})
                                    </option>
                                </select>
                            </div>

                            <!-- Filter by Video -->
                            <div class="md:w-64">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Filter by video
                                </label>
                                <select
                                    v-model="selectedVideo"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">All videos</option>
                                    <option v-for="video in videos" :key="video.id" :value="video.id">
                                        {{ video.title.substring(0, 40) }}{{ video.title.length > 40 ? '...' : '' }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Active Filters -->
                        <div v-if="search || selectedTag || selectedVideo" class="mt-4 flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
                            <button
                                @click="clearFilters"
                                class="text-sm text-red-500 hover:text-red-700 underline"
                            >
                                Clear all
                            </button>
                        </div>

                        <!-- Tags Quick Filter -->
                        <div v-if="tags.length > 0" class="mt-4">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="tag in tags"
                                    :key="tag.id"
                                    @click="selectedTag = selectedTag == tag.id ? '' : tag.id"
                                    :style="{
                                        backgroundColor: selectedTag == tag.id ? tag.color : tag.color + '20',
                                        color: selectedTag == tag.id ? 'white' : tag.color
                                    }"
                                    class="px-3 py-1 text-sm rounded-full transition-colors"
                                >
                                    {{ tag.name }}
                                    <span class="ml-1 opacity-70">({{ tag.notes_count }})</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ notes.total }} note(s) found
                </div>

                <!-- Notes List -->
                <div v-if="notes.data.length === 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">
                        No notes found matching your criteria.
                    </p>
                    <Link
                        :href="route('videos.index')"
                        class="inline-block mt-4 text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        Start watching videos and taking notes →
                    </Link>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="note in notes.data"
                        :key="note.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
                    >
                        <div class="p-4">
                            <!-- Video Info -->
                            <div class="flex items-start gap-4 mb-3">
                                <Link
                                    :href="route('videos.show', note.video.id)"
                                    class="flex-shrink-0"
                                >
                                    <img
                                        :src="note.video.thumbnail"
                                        :alt="note.video.title"
                                        class="w-24 h-14 object-cover rounded"
                                    />
                                </Link>
                                <div class="flex-1 min-w-0">
                                    <Link
                                        :href="route('videos.show', note.video.id)"
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400 line-clamp-1"
                                    >
                                        {{ note.video.title }}
                                    </Link>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Link
                                            v-if="note.timestamp !== null"
                                            :href="route('videos.show', note.video.id) + '?t=' + note.timestamp"
                                            class="text-xs font-mono text-blue-600 dark:text-blue-400 hover:underline"
                                        >
                                            {{ formatTimestamp(note.timestamp) }}
                                        </Link>
                                        <span class="text-xs text-gray-400">
                                            {{ new Date(note.updated_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Note Content -->
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap"
                                v-html="highlightText(note.content, search)"
                            ></div>

                            <!-- Tags & Actions -->
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in note.tags"
                                        :key="tag.id"
                                        :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                        class="px-2 py-0.5 text-xs rounded-full cursor-pointer hover:opacity-80"
                                        @click="selectedTag = tag.id"
                                    >
                                        {{ tag.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="route('videos.show', note.video.id)"
                                        class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                    >
                                        View video
                                    </Link>
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

                <!-- Pagination -->
                <div v-if="notes.last_page > 1" class="mt-8 flex justify-center gap-2">
                    <Link
                        v-for="page in notes.last_page"
                        :key="page"
                        :href="route('notes.index', { ...filters, page })"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            page === notes.current_page
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                        ]"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>