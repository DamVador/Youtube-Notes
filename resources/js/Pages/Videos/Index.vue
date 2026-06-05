<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import UpgradeBanner from '@/Components/UpgradeBanner.vue';
import LimitWarning from '@/Components/LimitWarning.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    videos: Object,
    continueWatching: Object,
    stats: Object,
});

const formatTime = (seconds) => {
    if (!seconds) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const inputValue = ref('');
const isAdding = ref(false);
const addError = ref(null);
const savedVideosFilter = ref('');

// Detect an 11-char YouTube id from any common URL form, or a bare id.
const extractYouTubeId = (input) => {
    if (!input) return null;
    const value = input.trim();
    const patterns = [
        /youtube\.com\/watch\?(?:.*&)?v=([a-zA-Z0-9_-]{11})/,
        /youtu\.be\/([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/live\/([a-zA-Z0-9_-]{11})/,
        /^([a-zA-Z0-9_-]{11})$/,
    ];

    for (const pattern of patterns) {
        const match = value.match(pattern);
        if (match) return match[1];
    }
    return null;
};

const detectedId = computed(() => extractYouTubeId(inputValue.value));

// Free text (not a URL) acts as a live filter over the user's saved videos.
watch(inputValue, (newVal) => {
    savedVideosFilter.value = extractYouTubeId(newVal) ? '' : newVal.trim();
});

const handleSubmit = async () => {
    if (detectedId.value) {
        await addVideoById(detectedId.value);
    }
};

const addVideoById = async (videoId) => {
    isAdding.value = true;
    addError.value = null;

    try {
        const { data } = await window.axios.get(route('videos.lookup'), {
            params: { url: videoId },
        });
        await saveAndWatch(data);
    } catch (error) {
        console.error('Lookup failed:', error);
        // oEmbed/network issue — fall back to minimal metadata so the user is never blocked.
        await saveAndWatch({
            youtube_id: videoId,
            title: 'YouTube Video',
            thumbnail: `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`,
            channel_name: 'Unknown',
        });
    } finally {
        isAdding.value = false;
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

        // window.axios (configured in bootstrap.js) sends the fresh XSRF-TOKEN
        // cookie automatically, so there's no stale CSRF token to manage.
        const { data } = await window.axios.post(route('videos.store'), video);

        router.visit(route('videos.show', data.id));
    } catch (error) {
        if (error.response?.status === 403 && error.response.data?.error === 'limit') {
            limitError.value = error.response.data.message;
            return;
        }
        console.error('Error saving video:', error);
        addError.value = 'Failed to save video. Please try again.';
    }
};

const deleteVideo = (videoId) => {
    if (!confirm('Delete this video and all its notes?')) return;
    router.delete(route('videos.destroy', videoId));
};

const clearSearch = () => {
    inputValue.value = '';
    savedVideosFilter.value = '';
};

// Support deep-links such as /videos?url=https://youtube.com/watch?v=ID
onMounted(() => {
    const url = new URLSearchParams(window.location.search).get('url');
    const videoId = extractYouTubeId(url);
    if (videoId) {
        addVideoById(videoId);
    }
});
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

                <!-- Continue Watching -->
                <Link
                    v-if="continueWatching"
                    :href="route('videos.show', continueWatching.id)"
                    class="flex items-center gap-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 mb-6 hover:border-blue-500 dark:hover:border-blue-500 transition-colors group"
                >
                    <div class="relative w-32 sm:w-40 flex-shrink-0 rounded-md overflow-hidden">
                        <img :src="continueWatching.thumbnail" :alt="continueWatching.title" class="w-full aspect-video object-cover" />
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-0.5">Continue watching</p>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ continueWatching.title }}
                        </h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">{{ continueWatching.channel_name }}</p>
                        <p v-if="continueWatching.last_position" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Resume at {{ formatTime(continueWatching.last_position) }}
                        </p>
                    </div>
                </Link>

                <!-- Add a video -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex gap-2">
                        <input
                            v-model="inputValue"
                            @keyup.enter="handleSubmit"
                            type="text"
                            placeholder="Paste a YouTube URL to add a video, or type to filter your videos..."
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-blue-500"
                        />
                        <button
                            @click="handleSubmit"
                            :disabled="!detectedId || isAdding"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed text-white rounded-lg transition-colors whitespace-nowrap"
                        >
                            {{ isAdding ? 'Adding...' : 'Add video' }}
                        </button>
                        <button
                            v-if="inputValue || savedVideosFilter"
                            @click="clearSearch"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors"
                        >
                            Clear
                        </button>
                    </div>

                    <p v-if="addError" class="text-red-500 text-sm mt-2">{{ addError }}</p>
                    <p v-if="detectedId" class="text-green-600 dark:text-green-400 text-sm mt-2">
                        YouTube link detected — press Enter or click “Add video”.
                    </p>
                    <p v-else class="text-gray-400 dark:text-gray-500 text-xs mt-2">
                        Works with watch, youtu.be, Shorts and embed links.
                    </p>
                </div>

                <!-- Saved Videos -->
                <div>
                    <div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            <span v-if="savedVideosFilter">
                                Results for "{{ savedVideosFilter }}"
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ filteredVideos.length }})</span>
                            </span>
                            <span v-else>
                                Saved Videos
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ videos.total }})</span>
                            </span>
                        </h3>

                        <div v-if="stats" class="flex items-center gap-2 text-sm">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">
                                🎞 {{ stats.videos_count }} videos
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                                📝 {{ stats.notes_count }} notes
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                                🏷 {{ stats.tags_count }} tags
                            </span>
                        </div>
                    </div>

                    <div v-if="videos.data.length === 0" class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">No saved videos yet</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Paste a YouTube URL above to get started</p>
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