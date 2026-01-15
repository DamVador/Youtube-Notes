<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    recentVideos: Array,
    recentNotes: Array,
});

const formatTimestamp = (seconds) => {
    if (seconds === null || seconds === undefined) return '';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const stripHtml = (html) => {
    if (!html) return '';
    return html.replace(/<[^>]*>/g, '').substring(0, 150);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Quick Actions -->
                <div class="flex gap-4 mb-8">
                    <Link
                        :href="route('videos.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search Videos
                    </Link>
                    <Link
                        :href="route('notes.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        My Notes
                    </Link>
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Recent Videos -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Videos</h3>
                        <div v-if="recentVideos.length === 0" class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                            <p class="text-gray-500 dark:text-gray-400">No videos yet</p>
                            <Link :href="route('videos.index')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mt-2 inline-block">
                                Search for videos →
                            </Link>
                        </div>
                        <div v-else class="space-y-3">
                            <Link
                                v-for="video in recentVideos"
                                :key="video.id"
                                :href="route('videos.show', video.id)"
                                class="flex gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors group"
                            >
                                <img
                                    :src="video.thumbnail"
                                    :alt="video.title"
                                    class="w-32 h-20 object-cover rounded flex-shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                        {{ video.title }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ video.channel_name }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ video.total_notes_count || 0 }} note{{ (video.total_notes_count || 0) !== 1 ? 's' : '' }}
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- Recent Notes -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Notes</h3>
                        <div v-if="recentNotes.length === 0" class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center border border-gray-200 dark:border-gray-700">
                            <p class="text-gray-500 dark:text-gray-400">No notes yet</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Start by watching a video and taking notes</p>
                        </div>
                        <div v-else class="space-y-3">
                            <Link
                                v-for="note in recentNotes"
                                :key="`${note.type}-${note.id}`"
                                :href="route('videos.show', note.video?.id || note.video_id)"
                                class="block p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors"
                            >
                                <div class="flex items-start gap-3">
                                    <img
                                        v-if="note.video?.thumbnail"
                                        :src="note.video.thumbnail"
                                        :alt="note.video.title"
                                        class="w-16 h-10 object-cover rounded flex-shrink-0"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <!-- Type badge -->
                                            <span
                                                v-if="note.type === 'document'"
                                                class="text-xs px-1.5 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 rounded"
                                            >
                                                Doc
                                            </span>
                                            <span
                                                v-else-if="note.timestamp !== null && note.timestamp !== undefined"
                                                class="text-xs font-mono text-blue-600 dark:text-blue-400"
                                            >
                                                {{ formatTimestamp(note.timestamp) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                            {{ note.type === 'document' ? note.content_preview : note.content }}
                                        </p>
                                        <div v-if="note.tags && note.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                            <span
                                                v-for="tag in note.tags"
                                                :key="tag.id"
                                                :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                                class="text-xs px-1.5 py-0.5 rounded"
                                            >
                                                {{ tag.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>