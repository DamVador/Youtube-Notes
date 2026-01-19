<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const togglePremium = () => {
    if (confirm('Confirmer le changement de statut premium ?')) {
        router.post(route('admin.users.toggle-premium', props.user.id));
    }
};

const toggleAdmin = () => {
    if (confirm('Confirmer le changement de statut admin ?')) {
        router.post(route('admin.users.toggle-admin', props.user.id));
    }
};

const deleteUser = () => {
    if (confirm('ATTENTION: Supprimer cet utilisateur et toutes ses données ? Cette action est irréversible.')) {
        router.delete(route('admin.users.delete', props.user.id));
    }
};

const isPremium = props.user.subscriptions?.some(s => s.stripe_status === 'active');
</script>

<template>
    <Head :title="`Admin - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.dashboard')" class="text-gray-400 hover:text-gray-600">
                    <span>🛡️</span>
                </Link>
                <span class="text-gray-400">/</span>
                <Link :href="route('admin.users')" class="text-gray-400 hover:text-gray-600">
                    Utilisateurs
                </Link>
                <span class="text-gray-400">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ user.name }}
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg">
                    {{ $page.props.flash.success }}
                </div>

                <!-- User Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center overflow-hidden">
                                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                                <span v-else class="text-2xl text-gray-500">{{ user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ user.name }}
                                    <span v-if="user.is_admin" class="ml-1 text-red-500" title="Admin">🛡️</span>
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span
                                        :class="isPremium 
                                            ? 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                                        class="px-2 py-1 text-xs font-medium rounded-full"
                                    >
                                        {{ isPremium ? 'Premium' : 'Free' }}
                                    </span>
                                    <span v-if="user.google_id" class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full">
                                        Google
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.videos_count }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Vidéos</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.notes_count }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.tags_count }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tags</p>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Inscrit le {{ new Date(user.created_at).toLocaleDateString('fr-FR', { dateStyle: 'long' }) }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h4>
                    
                    <div class="space-y-3">
                        <button
                            @click="togglePremium"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ isPremium ? '❌ Retirer Premium' : '⭐ Activer Premium' }}
                            </span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ isPremium ? 'Révoquer l\'accès premium manuellement' : 'Offrir l\'accès premium gratuitement' }}
                            </p>
                        </button>

                        <button
                            @click="toggleAdmin"
                            class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ user.is_admin ? '🛡️ Retirer Admin' : '🛡️ Rendre Admin' }}
                            </span>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ user.is_admin ? 'Retirer les droits d\'administration' : 'Donner les droits d\'administration' }}
                            </p>
                        </button>

                        <button
                            @click="deleteUser"
                            class="w-full text-left px-4 py-3 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                        >
                            <span class="font-medium text-red-700 dark:text-red-400">
                                🗑️ Supprimer l'utilisateur
                            </span>
                            <p class="text-sm text-red-500 dark:text-red-400">
                                Supprime définitivement l'utilisateur et toutes ses données
                            </p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>