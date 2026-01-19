<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <span class="text-red-500">🛡️</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Admin Dashboard
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Utilisateurs</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <p class="text-3xl font-bold text-green-600">{{ stats.premium_users }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Premium</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <p class="text-3xl font-bold text-blue-600">{{ stats.total_videos }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Vidéos</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <p class="text-3xl font-bold text-purple-600">{{ stats.total_notes }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                        <p class="text-3xl font-bold text-orange-600">{{ stats.total_tags }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tags</p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="flex gap-4 mb-8">
                    <Link
                        :href="route('admin.users')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Gérer les utilisateurs
                    </Link>
                </div>

                <!-- Recent Users -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Dernières inscriptions
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="user in stats.recent_users"
                            :key="user.id"
                            class="px-6 py-4 flex items-center justify-between"
                        >
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>