<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    videos: Object,
});

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const searchError = ref(null);

const searchYouTube = async () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    searchError.value = null;

    try {
        const response = await fetch(route('videos.search') + '?q=' + encodeURIComponent(searchQuery.value));
        
        if (!response.ok) {
            throw new Error('Search failed');
        }

        searchResults.value = await response.json();
    } catch (error) {
        console.error('Search error:', error);
        searchError.value = 'Failed to search YouTube. Please try again.';
    } finally {
        isSearching.value = false;
    }
};

const saveAndWatch = async (video) => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
            || document.head.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(route('videos.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(video),
        });

        if (!response.ok) {
            throw new Error('Failed to save video');
        }

        const savedVideo = await response.json();
        router.visit(route('videos.show', savedVideo.id));
    } catch (error) {
        console.error('Error saving video:', error);
        alert('Failed to save video. Please try again.');
    }
};

const deleteVideo = (videoId) => {
    if (!confirm('Delete this video and all its notes?')) return;
    router.delete(route('videos.destroy', videoId));
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
};
</script>

<template>
    <Head title="Videos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Videos
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search Bar -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            @keyup.enter="searchYouTube"
                            type="text"
                            placeholder="Search YouTube videos..."
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <button
                            @click="searchYouTube"
                            :disabled="isSearching"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition-colors"
                        >
                            {{ isSearching ? 'Searching...' : 'Search' }}
                        </button>
                        <button
                            v-if="searchResults.length > 0"
                            @click="clearSearch"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors"
                        >
                            Clear
                        </button>
                    </div>

                    <p v-if="searchError" class="text-red-500 text-sm mt-2">{{ searchError }}</p>
                </div>

                <!-- Search Results -->
                <div v-if="searchResults.length > 0" class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Search Results</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <button
                            v-for="video in searchResults"
                            :key="video.youtube_id"
                            @click="saveAndWatch(video)"
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:border-blue-500 dark:hover:border-blue-500 transition-colors text-left group"
                        >
                            <div class="relative">
                                <img
                                    :src="video.thumbnail"
                                    :alt="video.title"
                                    class="w-full aspect-video object-cover"
                                />
                                <div class="absolute inset-0 bg-blue-600/0 group-hover:bg-blue-600/20 transition-colors flex items-center justify-center">
                                    <span class="opacity-0 group-hover:opacity-100 text-white bg-blue-600 px-3 py-1 rounded-full text-sm font-medium transition-opacity">
                                        Watch & Take Notes
                                    </span>
                                </div>
                            </div>
                            <div class="p-3">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    {{ video.title }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ video.channel_name }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Saved Videos -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Saved Videos
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ videos.total }})</span>
                    </h3>

                    <div v-if="videos.data.length === 0" class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">No saved videos yet</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Search for a video above to get started</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="video in videos.data"
                            :key="video.id"
                            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden group"
                        >
                            <Link :href="route('videos.show', video.id)" class="block">
                                <div class="relative">
                                    <img
                                        :src="video.thumbnail"
                                        :alt="video.title"
                                        class="w-full aspect-video object-cover"
                                    />
                                    <div class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                        {{ video.total_notes_count || 0 }} note{{ (video.total_notes_count || 0) !== 1 ? 's' : '' }}
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                        {{ video.title }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ video.channel_name }}
                                    </p>
                                </div>
                            </Link>
                            <div class="px-3 pb-3 flex justify-end">
                                <button
                                    @click="deleteVideo(video.id)"
                                    class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="videos.last_page > 1" class="flex justify-center gap-2 mt-6">
                        <Link
                            v-for="page in videos.last_page"
                            :key="page"
                            :href="route('videos.index', { page })"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                page === videos.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>