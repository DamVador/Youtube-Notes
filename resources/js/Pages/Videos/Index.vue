<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

defineProps({
    videos: Object,
});

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showResults = ref(false);

const searchVideos = async () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    isSearching.value = true;
    showResults.value = true;

    try {
        const response = await fetch(route('videos.search') + '?q=' + encodeURIComponent(searchQuery.value));
        const data = await response.json();
        searchResults.value = data.items || [];
    } catch (error) {
        console.error('Search error:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const saveAndWatch = async (video) => {
    try {
        const response = await axios.post(route('videos.store'), video);
        router.visit(route('videos.show', response.data.id));
    } catch (error) {
        console.error('Save error:', error);
    }
};

const deleteVideo = (videoId) => {
    if (confirm('Are you sure you want to delete this video and all its notes?')) {
        router.delete(route('videos.destroy', videoId));
    }
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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-8">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Search YouTube
                        </h3>
                        <div class="flex gap-4">
                            <input
                                v-model="searchQuery"
                                @keyup.enter="searchVideos"
                                type="text"
                                placeholder="Search for videos..."
                                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <button
                                @click="searchVideos"
                                :disabled="isSearching"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition-colors"
                            >
                                <span v-if="isSearching">Searching...</span>
                                <span v-else>Search</span>
                            </button>
                        </div>

                        <!-- Search Results -->
                        <div v-if="showResults" class="mt-6">
                            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Search Results
                            </h4>
                            <div v-if="isSearching" class="text-gray-500 dark:text-gray-400">
                                Loading...
                            </div>
                            <div v-else-if="searchResults.length === 0" class="text-gray-500 dark:text-gray-400">
                                No videos found.
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div
                                    v-for="video in searchResults"
                                    :key="video.youtube_id"
                                    class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden cursor-pointer hover:ring-2 hover:ring-blue-500 transition-all"
                                    @click="saveAndWatch(video)"
                                >
                                    <img
                                        :src="video.thumbnail"
                                        :alt="video.title"
                                        class="w-full h-40 object-cover"
                                    />
                                    <div class="p-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2">
                                            {{ video.title }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ video.channel_name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saved Videos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            My Saved Videos
                        </h3>
                        <div v-if="videos.data.length === 0" class="text-gray-500 dark:text-gray-400">
                            No saved videos yet. Search and click on a video to save it!
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="video in videos.data"
                                :key="video.id"
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden"
                            >
                                <Link :href="route('videos.show', video.id)">
                                    <img
                                        :src="video.thumbnail"
                                        :alt="video.title"
                                        class="w-full h-40 object-cover hover:opacity-90 transition-opacity"
                                    />
                                </Link>
                                <div class="p-3">
                                    <Link :href="route('videos.show', video.id)">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ video.title }}
                                        </p>
                                    </Link>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ video.notes_count }} note(s)
                                        </span>
                                        <button
                                            @click="deleteVideo(video.id)"
                                            class="text-red-500 hover:text-red-700 text-xs"
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