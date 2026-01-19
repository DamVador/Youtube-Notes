<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users'), { search: value }, { preserveState: true });
    }, 300);
});
</script>

<template>
    <Head title="Admin - Utilisateurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-gray-600">
                    <span>🛡️</span>
                </Link>
                <span class="text-gray-400">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Utilisateurs
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search -->
                <div class="mb-6">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher par nom ou email..."
                        class="w-full md:w-96 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                    />
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Users Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Utilisateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stats</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Inscrit le</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ user.name }}
                                            <span v-if="user.is_admin" class="ml-1 text-red-500">🛡️</span>
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="user.subscriptions?.length > 0"
                                        class="px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-full"
                                    >
                                        Premium
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full"
                                    >
                                        Free
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.videos_count }} vidéos · {{ user.notes_count }} notes
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('admin.users.show', user.id)"
                                        class="text-blue-600 dark:text-blue-400 hover:underline text-sm"
                                    >
                                        Détails
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="flex justify-center gap-2 mt-6">
                    <Link
                        v-for="page in users.last_page"
                        :key="page"
                        :href="route('admin.users', { page, search })"
                        :class="[
                            'px-3 py-1 rounded text-sm',
                            page === users.current_page
                                ? 'bg-blue-600 text-white'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border hover:bg-gray-50'
                        ]"
                    >
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>