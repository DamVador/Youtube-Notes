<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';


const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    cover_image: '',
    meta_title: '',
    meta_description: '',
    is_published: false,
});

const autoSlug = ref(true);

watch(() => form.title, (newTitle) => {
    if (autoSlug.value) {
        form.slug = newTitle
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});

const submit = () => {
    form.post(route('admin.posts.store'));
};
</script>

<template>
    <Head title="New Post - Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.posts.index')"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    New Post
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Title -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Your post title"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                    </div>

                    <!-- Slug -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Slug
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <input
                                    v-model="autoSlug"
                                    type="checkbox"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                />
                                Auto-generate
                            </label>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">/blog/</span>
                            <input
                                v-model="form.slug"
                                type="text"
                                :disabled="autoSlug"
                                class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50"
                                placeholder="post-slug"
                            />
                        </div>
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Excerpt -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="2"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="A short summary of the post..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ form.excerpt.length }}/500 characters</p>
                    </div>

                    <!-- Content -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Content *
                        </label>
                        <textarea
                            v-model="form.content"
                            rows="15"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                            placeholder="Write your post content in Markdown or HTML..."
                        ></textarea>
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>

                    <!-- Cover Image -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Cover Image URL
                        </label>
                        <input
                            v-model="form.cover_image"
                            type="text"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://example.com/image.jpg"
                        />
                        <div v-if="form.cover_image" class="mt-3">
                            <img :src="form.cover_image" alt="Cover preview" class="max-h-48 rounded-lg" />
                        </div>
                    </div>

                    <!-- SEO -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">SEO Settings</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Meta Title
                                </label>
                                <input
                                    v-model="form.meta_title"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                    :placeholder="form.title || 'Defaults to post title'"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Meta Description
                                </label>
                                <textarea
                                    v-model="form.meta_description"
                                    rows="2"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                                    :placeholder="form.excerpt || 'Defaults to excerpt'"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Publish -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 flex items-center justify-between">
                        <label class="flex items-center gap-3">
                            <input
                                v-model="form.is_published"
                                type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 w-5 h-5"
                            />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Publish immediately
                            </span>
                        </label>

                        <div class="flex items-center gap-3">
                            <Link
                                :href="route('admin.posts.index')"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                            >
                                {{ form.is_published ? 'Publish' : 'Save Draft' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>