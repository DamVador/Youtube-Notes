<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import UpgradeBanner from '@/Components/UpgradeBanner.vue';
import LimitWarning from '@/Components/LimitWarning.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    videos: Object,
});

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const searchError = ref(null);
const activeTab = ref('youtube');
const savedVideosFilter = ref('');
const urlDetected = ref(false);

// Detect YouTube URL patterns
const extractYouTubeId = (input) => {
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /^([a-zA-Z0-9_-]{11})$/
    ];
    
    for (const pattern of patterns) {
        const match = input.match(pattern);
        if (match) return match[1];
    }
    return null;
};

// Watch for URL in search query
watch(searchQuery, (newVal) => {
    urlDetected.value = !!extractYouTubeId(newVal);
});

const handleSearch = async () => {
    const query = searchQuery.value.trim();
    if (!query) return;

    // Check if it's a YouTube URL
    const videoId = extractYouTubeId(query);
    
    if (videoId) {
        // Direct URL - fetch video info and save
        await saveVideoFromUrl(videoId);
        return;
    }

    if (activeTab.value === 'youtube') {
        // YouTube search
        await searchYouTube();
    } else {
        // Filter saved videos
        savedVideosFilter.value = query;
    }
};

const saveVideoFromUrl = async (videoId) => {
    isSearching.value = true;
    searchError.value = null;

    try {
        // First, get video info from YouTube API
        const response = await fetch(route('videos.search') + '?q=' + encodeURIComponent(videoId));
        
        if (!response.ok) throw new Error('Failed to fetch video info');
        
        const results = await response.json();
        const video = results.find(v => v.youtube_id === videoId);
        
        if (video) {
            await saveAndWatch(video);
        } else {
            // If not found via search, create with minimal info
            await saveAndWatch({
                youtube_id: videoId,
                title: 'YouTube Video',
                thumbnail: `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`,
                channel_name: 'Unknown'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        searchError.value = 'Failed to add video. Please try again.';
    } finally {
        isSearching.value = false;
    }
};

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
        activeTab.value = 'youtube';
    } catch (error) {
        console.error('Search error:', error);
        searchError.value = 'Failed to search YouTube. Please try again.';
    } finally {
        isSearching.value = false;
    }
};

const filteredVideos = computed(() => {
    if (!savedVideosFilter.value) return props.videos.data;
    
    const filter = savedVideosFilter.value.toLowerCase();
    return props.videos.data.filter(video => 
        video.title.toLowerCase().includes(filter) ||
        video.channel_name?.toLowerCase().includes(filter)
    );
});

const limitError = ref(null);

const saveAndWatch = async (video) => {
    try {
        limitError.value = null;
        
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

        const data = await response.json();

        if (!response.ok) {
            if (data.error === 'limit') {
                limitError.value = data.message;
                return;
            }
            throw new Error('Failed to save video');
        }

        router.visit(route('videos.show', data.id));
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
    savedVideosFilter.value = '';
    urlDetected.value = false;
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
                <!-- Limit Error Message -->
                <div 
                    v-if="limitError" 
                    class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-800 rounded-lg"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-red-500 text-xl">⚠️</span>
                        <p class="text-red-700 dark:text-red-300 flex-1">{{ limitError }}</p>
                        <Link 
                            :href="route('subscription.pricing')" 
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                        >
                            Upgrade to Premium
                        </Link>
                    </div>
                </div>

                <!-- Upgrade Banner -->
                <UpgradeBanner 
                    v-if="!$page.props.auth.user.isPremium && $page.props.auth.user.limits.remainingVideos <= 3"
                    message="You're running low on video slots. Upgrade for unlimited videos!"
                    class="mb-6"
                />

                <!-- Limit Warning -->
                <LimitWarning
                    :current="$page.props.auth.user.limits.videosCount"
                    :max="$page.props.auth.user.limits.maxVideos"
                    type="videos"
                    class="mb-4"
                />

                <!-- Search Bar -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            @keyup.enter="handleSearch"
                            type="text"
                            placeholder="Search YouTube, paste a URL, or filter your videos..."
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <button
                            @click="handleSearch"
                            :disabled="isSearching"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition-colors"
                        >
                            {{ isSearching ? '...' : 'Search' }}
                        </button>
                        <button
                            v-if="searchResults.length > 0 || savedVideosFilter"
                            @click="clearSearch"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors"
                        >
                            Clear
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="flex gap-4 mt-3 border-t border-gray-200 dark:border-gray-700 pt-3">
                        <button
                            @click="activeTab = 'youtube'"
                            :class="activeTab === 'youtube' 
                                ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' 
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="pb-1 text-sm font-medium transition-colors"
                        >
                            YouTube Search
                        </button>
                        <button
                            @click="activeTab = 'saved'"
                            :class="activeTab === 'saved' 
                                ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' 
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="pb-1 text-sm font-medium transition-colors"
                        >
                            My Videos ({{ videos.total }})
                        </button>
                    </div>

                    <p v-if="searchError" class="text-red-500 text-sm mt-2">{{ searchError }}</p>
                    <p v-if="urlDetected" class="text-green-600 dark:text-green-400 text-sm mt-2">
                        YouTube URL detected! Click Search to add this video.
                    </p>
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
                <div v-show="activeTab === 'saved' || searchResults.length === 0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        <span v-if="savedVideosFilter">
                            Results for "{{ savedVideosFilter }}"
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ filteredVideos.length }})</span>
                        </span>
                        <span v-else>
                            Saved Videos
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ videos.total }})</span>
                        </span>
                    </h3>

                    <div v-if="videos.data.length === 0" class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">No saved videos yet</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Search for a video above to get started</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="video in filteredVideos"
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