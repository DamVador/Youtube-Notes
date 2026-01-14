<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    videos: Array,
    recentNotes: Array,
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Quick Actions -->
                <div class="mb-8 flex flex-wrap gap-4">
                    <Link
                        :href="route('videos.index')"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search Videos
                    </Link>
                    <Link
                        :href="route('notes.index')"
                        class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        My Notes
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Videos -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Recent Videos
                            </h3>
                            <div v-if="videos.length === 0" class="text-gray-500 dark:text-gray-400">
                                No videos yet. Start by searching for a video!
                            </div>
                            <div v-else class="space-y-4">
                                <Link
                                    v-for="video in videos"
                                    :key="video.id"
                                    :href="route('videos.show', video.id)"
                                    class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <img
                                        :src="video.thumbnail"
                                        :alt="video.title"
                                        class="w-24 h-14 object-cover rounded"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ video.title }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ video.notes_count }} note(s)
                                        </p>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Notes -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Recent Notes
                            </h3>
                            <div v-if="recentNotes.length === 0" class="text-gray-500 dark:text-gray-400">
                                No notes yet. Watch a video and start taking notes!
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="note in recentNotes"
                                    :key="note.id"
                                    class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700"
                                >
                                    <Link
                                        :href="route('videos.show', note.video.id)"
                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        {{ note.video.title }}
                                    </Link>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 line-clamp-2">
                                        {{ note.content }}
                                    </p>
                                    <div v-if="note.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                        <span
                                            v-for="tag in note.tags"
                                            :key="tag.id"
                                            :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                                            class="px-2 py-0.5 text-xs rounded-full"
                                        >
                                            {{ tag.name }}
                                        </span>
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