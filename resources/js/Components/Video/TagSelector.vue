<script setup>
import { ref } from 'vue';

const props = defineProps({
    availableTags: {
        type: Array,
        default: () => [],
    },
    selectedTags: {
        type: Array,
        default: () => [],
    },
    showLabel: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['toggle', 'create', 'delete']);

const showTagInput = ref(false);
const newTagName = ref('');
const newTagColor = ref('#3B82F6');

const createTag = () => {
    if (!newTagName.value.trim()) return;
    
    emit('create', {
        name: newTagName.value,
        color: newTagColor.value,
    });
    
    newTagName.value = '';
    showTagInput.value = false;
};
</script>

<template>
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex-shrink-0">
        <div class="flex flex-wrap items-center gap-2">
            <span v-if="showLabel" class="text-xs text-gray-500 dark:text-gray-400">Tags:</span>
            <div
                v-for="tag in availableTags"
                :key="tag.id"
                class="group relative inline-flex items-center"
            >
                <button
                    @click="$emit('toggle', tag.id)"
                    :style="{
                        backgroundColor: selectedTags.includes(tag.id) ? tag.color : 'transparent',
                        color: selectedTags.includes(tag.id) ? 'white' : tag.color,
                        borderColor: tag.color
                    }"
                    class="px-2 py-0.5 text-xs rounded-full border transition-colors pr-5"
                >
                    {{ tag.name }}
                </button>
                <!-- Delete button -->
                <button
                    @click.stop="$emit('delete', tag)"
                    class="absolute right-1 w-3 h-3 flex items-center justify-center rounded-full opacity-0 group-hover:opacity-70 hover:!opacity-100 transition-opacity"
                    :style="{ color: selectedTags.includes(tag.id) ? 'white' : tag.color }"
                    title="Delete tag"
                >
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button
                @click="showTagInput = !showTagInput"
                class="px-2 py-0.5 text-xs rounded-full border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
            >
                {{ showLabel ? '+ New' : '+' }}
            </button>
        </div>
        
        <!-- New tag input -->
        <div v-if="showTagInput" class="flex items-center gap-2 mt-2">
            <input
                v-model="newTagName"
                type="text"
                placeholder="Tag name"
                class="text-xs rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-2 py-1 w-24"
                @keyup.enter="createTag"
            />
            <input
                v-model="newTagColor"
                type="color"
                class="w-6 h-6 rounded cursor-pointer border-0"
            />
            <button
                @click="createTag"
                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
            >
                Add
            </button>
        </div>
    </div>
</template>