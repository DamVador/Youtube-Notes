<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    notes: Object,
    tags: Array,
    videos: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedTag = ref(props.filters.tag || '');
const selectedVideo = ref(props.filters.video || '');
const selectedType = ref(props.filters.type || 'all');

const applyFilters = debounce(() => {
    router.get(route('notes.index'), {
        search: search.value || undefined,
        tag: selectedTag.value || undefined,
        video: selectedVideo.value || undefined,
        type: selectedType.value !== 'all' ? selectedType.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch([search, selectedTag, selectedVideo, selectedType], applyFilters);

const clearFilters = () => {
    search.value = '';
    selectedTag.value = '';
    selectedVideo.value = '';
    selectedType.value = 'all';
};

const hasActiveFilters = computed(() => {
    return search.value || selectedTag.value || selectedVideo.value || selectedType.value !== 'all';
});

const formatTimestamp = (seconds) => {
    if (seconds === null || seconds === undefined) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const highlightSearch = (text) => {
    if (!search.value || !text) return text;
    const regex = new RegExp(`(${search.value})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-800 px-0.5 rounded">$1</mark>');
};

const getTypeLabel = (type) => {
    return type === 'document' ? 'Document' : 'Quick Note';
};

const getTypeColor = (type) => {
    return type === 'document' 
        ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300'
        : 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300';
};
</script>

<template>
    <Head title="My Notes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                My Notes
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search in notes..."
                                    class="w-full pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                />
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <select
                            v-model="selectedType"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        >
                            <option value="all">All Types</option>
                            <option value="documents">Documents</option>
                            <option value="quick_notes">Quick Notes</option>
                        </select>

                        <!-- Tag Filter -->
                        <select
                            v-model="selectedTag"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        >
                            <option value="">All Tags</option>
                            <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                                {{ tag.name }}
                            </option>
                        </select>

                        <!-- Video Filter -->
                        <select
                            v-model="selectedVideo"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        >
                            <option value="">All Videos</option>
                            <option v-for="video in videos" :key="video.id" :value="video.id">
                                {{ video.title }}
                            </option>
                        </select>
                    </div>

                    <!-- Active Filters -->
                    <div v-if="hasActiveFilters" class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-sm text-slate-500 dark:text-slate-400">Active filters:</span>
                        
                        <span v-if="selectedType !== 'all'" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs rounded-full">
                            {{ selectedType === 'documents' ? 'Documents' : 'Quick Notes' }}
                        </span>
                        
                        <span v-if="selectedTag" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs rounded-full">
                            Tag: {{ tags.find(t => t.id == selectedTag)?.name }}
                        </span>
                        
                        <span v-if="selectedVideo" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs rounded-full">
                            Video: {{ videos.find(v => v.id == selectedVideo)?.title.substring(0, 20) }}...
                        </span>
                        
                        <button
                            @click="clearFilters"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline ml-2"
                        >
                            Clear all
                        </button>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="mb-4 text-sm text-slate-600 dark:text-slate-400">
                    {{ notes.total }} {{ notes.total === 1 ? 'result' : 'results' }}
                </div>

                <!-- Notes List -->
                <div v-if="notes.data.length > 0" class="space-y-4">
                    <div
                        v-for="note in notes.data"
                        :key="note.id"
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hover:border-slate-300 dark:hover:border-slate-600 transition-colors"
                    >
                        <div class="flex">
                            <!-- Video Thumbnail -->
                            <Link
                                v-if="note.video"
                                :href="route('videos.show', note.video.id)"
                                class="flex-shrink-0 w-40 h-24 bg-slate-100 dark:bg-slate-700 relative group"
                            >
                                <img
                                    :src="note.video.thumbnail"
                                    :alt="note.video.title"
                                    class="w-full h-full object-cover"
                                />
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </Link>

                            <!-- Content -->
                            <div class="flex-1 p-4 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <!-- Type Badge + Timestamp -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <span :class="getTypeColor(note.type)" class="text-xs font-medium px-2 py-0.5 rounded-full">
                                                {{ getTypeLabel(note.type) }}
                                            </span>
                                            
                                            <Link
                                                v-if="note.timestamp !== null"
                                                :href="route('videos.show', note.video.id)"
                                                class="text-xs font-mono text-blue-600 dark:text-blue-400 hover:underline"
                                            >
                                                {{ formatTimestamp(note.timestamp) }}
                                            </Link>
                                        </div>

                                        <!-- Video Title -->
                                        <Link
                                            v-if="note.video"
                                            :href="route('videos.show', note.video.id)"
                                            class="text-sm font-medium text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 line-clamp-1 block mb-1"
                                        >
                                            {{ note.video.title }}
                                        </Link>

                                        <!-- Content Preview -->
                                        <p
                                            class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2"
                                            v-html="highlightSearch(note.content_preview)"
                                        ></p>

                                        <!-- Tags -->
                                        <div v-if="note.tags && note.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                            <button
                                                v-for="tag in note.tags"
                                                :key="tag.id"
                                                @click="selectedTag = tag.id"
                                                class="px-2 py-0.5 text-xs rounded-full text-white"
                                                :style="{ backgroundColor: tag.color }"
                                            >
                                                {{ tag.name }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Date -->
                                    <div class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">
                                        {{ formatDate(note.updated_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center">
                    <svg class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">
                        No notes found
                    </h3>
                    <p class="text-slate-600 dark:text-slate-400 mb-6">
                        <span v-if="hasActiveFilters">Try adjusting your filters or search terms.</span>
                        <span v-else>Start watching videos and taking notes!</span>
                    </p>
                    <Link
                        :href="route('videos.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search Videos
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="notes.last_page > 1" class="mt-6 flex justify-center gap-2">
                    <Link
                        v-for="page in notes.last_page"
                        :key="page"
                        :href="route('notes.index', { ...filters, page })"
                        :class="[
                            'px-3 py-1 text-sm rounded-lg transition-colors',
                            page === notes.current_page
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700'
                        ]"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
