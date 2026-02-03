<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const videos = ref([]);
const currentInterest = ref([]);
const loading = ref(true);
const showSettings = ref(false);
const categories = ref([]);
const selectedCategories = ref([]);
const customKeywords = ref([]);
const newKeyword = ref('');
const hasInterests = ref(true);
const savingInterests = ref(false);
const interests = ref([]);

const loadSuggestions = async () => {
    loading.value = true;
    try {
        const response = await fetch(route('discover.suggestions'));
        const data = await response.json();
        
        if (data.message === 'no_interests') {
            hasInterests.value = false;
            videos.value = [];
        } else {
            hasInterests.value = true;
            videos.value = data.videos || [];
            interests.value = data.interests || [];
        }
    } catch (error) {
        console.error('Error loading suggestions:', error);
    } finally {
        loading.value = false;
    }
};

const loadCategories = async () => {
    try {
        const response = await fetch(route('discover.categories'));
        categories.value = await response.json();
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

const loadUserInterests = async () => {
    try {
        const response = await fetch(route('discover.interests'));
        const interests = await response.json();
        
        selectedCategories.value = interests
            .filter(i => i.interest_category_id)
            .map(i => i.interest_category_id);
        
        customKeywords.value = interests
            .filter(i => i.custom_keyword)
            .map(i => i.custom_keyword);
    } catch (error) {
        console.error('Error loading user interests:', error);
    }
};

const openSettings = async () => {
    await loadCategories();
    await loadUserInterests();
    showSettings.value = true;
};

const toggleCategory = (categoryId) => {
    const index = selectedCategories.value.indexOf(categoryId);
    if (index > -1) {
        selectedCategories.value.splice(index, 1);
    } else {
        selectedCategories.value.push(categoryId);
    }
};

const addKeyword = () => {
    const keyword = newKeyword.value.trim();
    if (keyword && !customKeywords.value.includes(keyword)) {
        customKeywords.value.push(keyword);
        newKeyword.value = '';
    }
};

const removeKeyword = (keyword) => {
    const index = customKeywords.value.indexOf(keyword);
    if (index > -1) {
        customKeywords.value.splice(index, 1);
    }
};

const saveInterests = async () => {
    savingInterests.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        await fetch(route('discover.interests.update'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                category_ids: selectedCategories.value,
                custom_keywords: customKeywords.value,
            }),
        });
        
        showSettings.value = false;
        await loadSuggestions();
    } catch (error) {
        console.error('Error saving interests:', error);
    } finally {
        savingInterests.value = false;
    }
};

const refresh = async () => {
    loading.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch(route('discover.refresh'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        const data = await response.json();
        videos.value = data.videos || [];
        currentInterest.value = data.interest || '';
    } catch (error) {
        console.error('Error refreshing:', error);
    } finally {
        loading.value = false;
    }
};

const startVideo = (video) => {
    window.location.href = route('videos.index') + '?url=https://youtube.com/watch?v=' + video.youtube_id;
};

onMounted(() => {
    loadSuggestions();
});
</script>

<template>
    <div class="mb-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    📚 Discover
                </h3>
                <span v-if="interests && interests.length && !loading" class="text-sm text-gray-500 dark:text-gray-400">
                    Based on: {{ interests.slice(0, 3).join(', ') }}<span v-if="interests.length > 3">...</span>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    v-if="hasInterests"
                    @click="refresh"
                    :disabled="loading"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50"
                    title="Refresh suggestions"
                >
                    <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <button
                    @click="openSettings"
                    class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Edit interests
                </button>
            </div>
        </div>

        <!-- No interests state -->
        <div v-if="!hasInterests && !loading" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
            <div class="text-4xl mb-4">🎯</div>
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Set up your learning interests
            </h4>
            <p class="text-gray-500 dark:text-gray-400 mb-4 max-w-md mx-auto">
                Tell us what you want to learn, and we'll suggest educational videos tailored to you.
            </p>
            <button
                @click="openSettings"
                class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
            >
                Choose your interests
            </button>
        </div>

        <!-- Loading state -->
        <div v-else-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="i in 4" :key="i" class="animate-pulse">
                <div class="bg-gray-200 dark:bg-gray-700 rounded-lg aspect-video mb-2"></div>
                <div class="bg-gray-200 dark:bg-gray-700 h-4 rounded w-3/4 mb-1"></div>
                <div class="bg-gray-200 dark:bg-gray-700 h-3 rounded w-1/2"></div>
            </div>
        </div>

        <!-- Videos grid -->
        <div v-else-if="videos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button
                v-for="video in videos"
                :key="video.youtube_id"
                @click="startVideo(video)"
                class="text-left group"
            >
                <div class="relative rounded-lg overflow-hidden mb-2">
                    <img
                        :src="video.thumbnail"
                        :alt="video.title"
                        class="w-full aspect-video object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="w-12 h-12 bg-white/90 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-900 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {{ video.title }}
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ video.channel_name }}
                </p>
            </button>
        </div>

        <!-- Settings Modal -->
        <Teleport to="body">
            <div v-if="showSettings" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div @click="showSettings = false" class="absolute inset-0 bg-black/50"></div>
                
                <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit your interests
                        </h3>
                        <button @click="showSettings = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-4 overflow-y-auto max-h-[60vh]">
                        <!-- Categories -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Select categories
                            </h4>
                            <div class="flex flex-wrap gap-2 text-white">
                                <button
                                    v-for="category in categories"
                                    :key="category.id"
                                    @click="toggleCategory(category.id)"
                                    :class="[
                                        'px-3 py-2 rounded-lg text-sm font-medium transition-all',
                                        selectedCategories.includes(category.id)
                                            ? 'ring-2 ring-offset-2 ring-blue-500 dark:ring-offset-gray-800'
                                            : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                                    ]"
                                    :style="{
                                        backgroundColor: selectedCategories.includes(category.id) ? category.color + '20' : '',
                                        color: selectedCategories.includes(category.id) ? category.color : ''
                                    }"
                                >
                                    {{ category.icon }} {{ category.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Custom Keywords -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Add custom keywords
                            </h4>
                            <div class="flex gap-2 mb-3">
                                <input
                                    v-model="newKeyword"
                                    @keyup.enter="addKeyword"
                                    type="text"
                                    placeholder="e.g., Vue.js, Piano, Spanish..."
                                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                <button
                                    @click="addKeyword"
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                >
                                    Add
                                </button>
                            </div>
                            <div v-if="customKeywords.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="keyword in customKeywords"
                                    :key="keyword"
                                    class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-sm"
                                >
                                    {{ keyword }}
                                    <button @click="removeKeyword(keyword)" class="hover:text-blue-900 dark:hover:text-blue-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button
                            @click="showSettings = false"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveInterests"
                            :disabled="savingInterests"
                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                        >
                            {{ savingInterests ? 'Saving...' : 'Save interests' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>