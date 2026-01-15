<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import Placeholder from '@tiptap/extension-placeholder';
import Highlight from '@tiptap/extension-highlight';
import { Color } from '@tiptap/extension-color';
import { TextStyle } from '@tiptap/extension-text-style';
import { ref, watch, onBeforeUnmount, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Start writing your notes...',
    },
});

const emit = defineEmits(['update:modelValue', 'save']);

const showAdvancedMenu = ref(false);
const showHighlightColors = ref(false);

const highlightColors = [
    { name: 'Yellow', color: '#fef08a' },
    { name: 'Green', color: '#bbf7d0' },
    { name: 'Blue', color: '#bfdbfe' },
    { name: 'Purple', color: '#e9d5ff' },
    { name: 'Pink', color: '#fbcfe8' },
    { name: 'Orange', color: '#fed7aa' },
];

const textColors = [
    { name: 'Default', color: null },
    { name: 'Gray', color: '#6b7280' },
    { name: 'Red', color: '#dc2626' },
    { name: 'Orange', color: '#ea580c' },
    { name: 'Yellow', color: '#ca8a04' },
    { name: 'Green', color: '#16a34a' },
    { name: 'Blue', color: '#2563eb' },
    { name: 'Purple', color: '#9333ea' },
    { name: 'Pink', color: '#db2777' },
];

const showTextColors = ref(false);

const setTextColor = (color) => {
    if (color === null) {
        editor.value.chain().focus().unsetColor().run();
    } else {
        editor.value.chain().focus().setColor(color).run();
    }
    showTextColors.value = false;
};

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-blue-600 underline cursor-pointer',
            },
        }),
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        Highlight.configure({
            multicolor: true,
        }),
        TextStyle,
        Color,
    ],
    editorProps: {
        attributes: {
            class: 'focus:outline-none min-h-[400px] lg:min-h-[500px] px-4 py-4 text-gray-900 dark:text-gray-100',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (newValue) => {
    if (editor.value && newValue !== editor.value.getHTML()) {
        editor.value.commands.setContent(newValue, false);
    }
});

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
        editor.value = null;
    }
});

onUnmounted(() => {
    if (editor.value) {
        editor.value.destroy();
        editor.value = null;
    }
});

const setLink = () => {
    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);

    if (url === null) return;

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const setHighlight = (color) => {
    if (color === null) {
        editor.value.chain().focus().unsetHighlight().run();
    } else {
        editor.value.chain().focus().toggleHighlight({ color }).run();
    }
    showHighlightColors.value = false;
};

const insertTimestamp = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    const formatted = `${mins}:${secs.toString().padStart(2, '0')}`;
    
    editor.value
        .chain()
        .focus()
        .insertContent(`<a href="#timestamp-${seconds}" class="timestamp-link" data-timestamp="${seconds}">[${formatted}]</a> `)
        .run();
};

defineExpose({
    insertTimestamp,
    getHTML: () => editor.value?.getHTML(),
    getJSON: () => editor.value?.getJSON(),
});
</script>

<template>
    <div class="tiptap-editor">
        <!-- Toolbar -->
        <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 sticky top-0 z-10">
            <div class="flex flex-wrap items-center gap-0.5">
                <!-- Bold -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBold().run()"
                    :class="editor?.isActive('bold') ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="p-2 rounded transition-colors"
                    title="Bold"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                        <path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                    </svg>
                </button>

                <!-- Italic -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleItalic().run()"
                    :class="editor?.isActive('italic') ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="p-2 rounded transition-colors"
                    title="Italic"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 4h4m-2 0v16m-4 0h8"></path>
                    </svg>
                </button>

                <!-- Underline -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleUnderline().run()"
                    :class="editor?.isActive('underline') ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="p-2 rounded transition-colors"
                    title="Underline"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M7 4v7a5 5 0 0010 0V4M5 21h14"></path>
                    </svg>
                </button>

                <!-- Text Color -->
                <div class="relative">
                    <button
                        type="button"
                        @click="showTextColors = !showTextColors"
                        class="p-2 rounded text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-1"
                        title="Text Color"
                    >
                        <span class="font-bold text-sm">A</span>
                        <span 
                            class="w-3 h-1 rounded-sm" 
                            :style="{ backgroundColor: editor?.getAttributes('textStyle').color || '#000000' }"
                        ></span>
                    </button>

                    <!-- Text Color Palette -->
                    <div
                        v-if="showTextColors"
                        class="absolute left-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 p-2"
                    >
                        <div class="flex gap-1 flex-wrap w-[140px]">
                            <button
                                v-for="colorOption in textColors"
                                :key="colorOption.name"
                                @click="setTextColor(colorOption.color)"
                                :title="colorOption.name"
                                class="w-6 h-6 rounded border border-gray-200 dark:border-gray-600 hover:scale-110 transition-transform flex items-center justify-center"
                                :style="{ backgroundColor: colorOption.color || '#f3f4f6' }"
                            >
                                <span v-if="colorOption.color === null" class="text-gray-400 text-xs">✕</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Highlight with colors -->
                <div class="relative">
                    <button
                        type="button"
                        @click="showHighlightColors = !showHighlightColors"
                        :class="editor?.isActive('highlight') ? 'bg-yellow-200 dark:bg-yellow-700 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="p-2 rounded transition-colors"
                        title="Highlight"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                    </button>

                    <!-- Highlight Color Palette -->
                    <div
                        v-if="showHighlightColors"
                        class="absolute left-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 p-2"
                    >
                        <div class="flex gap-1">
                            <button
                                @click="setHighlight(null)"
                                title="Remove highlight"
                                class="w-6 h-6 rounded border border-gray-300 dark:border-gray-600 hover:scale-110 transition-transform flex items-center justify-center bg-white dark:bg-gray-700"
                            >
                                <span class="text-gray-400 text-xs">✕</span>
                            </button>
                            <button
                                v-for="colorOption in highlightColors"
                                :key="colorOption.name"
                                @click="setHighlight(colorOption.color)"
                                :title="colorOption.name"
                                class="w-6 h-6 rounded border border-gray-200 dark:border-gray-600 hover:scale-110 transition-transform"
                                :style="{ backgroundColor: colorOption.color }"
                            ></button>
                        </div>
                    </div>
                </div>

                <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Headings -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                    :class="editor?.isActive('heading', { level: 1 }) ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-2 py-1 rounded text-xs font-bold transition-colors"
                    title="Heading 1"
                >
                    H1
                </button>

                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                    :class="editor?.isActive('heading', { level: 2 }) ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-2 py-1 rounded text-xs font-bold transition-colors"
                    title="Heading 2"
                >
                    H2
                </button>

                <button
                    type="button"
                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                    :class="editor?.isActive('heading', { level: 3 }) ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="px-2 py-1 rounded text-xs font-bold transition-colors"
                    title="Heading 3"
                >
                    H3
                </button>

                <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Bullet List -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleBulletList().run()"
                    :class="editor?.isActive('bulletList') ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="p-2 rounded transition-colors"
                    title="Bullet List"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"></path>
                    </svg>
                </button>

                <!-- Ordered List -->
                <button
                    type="button"
                    @click="editor?.chain().focus().toggleOrderedList().run()"
                    :class="editor?.isActive('orderedList') ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="p-2 rounded transition-colors"
                    title="Numbered List"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M7 20h14M7 12h14M7 4h14M3 20h.01M3 12h.01M3 4h.01"></path>
                    </svg>
                </button>

                <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                <!-- Link -->
                <button
                    type="button"
                    @click="setLink"
                    :class="editor?.isActive('link') ? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                    class="p-2 rounded transition-colors"
                    title="Add Link"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </button>

                <!-- More Options -->
                <div class="relative ml-auto">
                    <button
                        type="button"
                        @click="showAdvancedMenu = !showAdvancedMenu"
                        class="p-2 rounded text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        title="More options"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                    </button>

                    <div
                        v-if="showAdvancedMenu"
                        class="absolute right-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 py-1 min-w-[150px]"
                    >
                        <button
                            type="button"
                            @click="editor?.chain().focus().toggleBlockquote().run(); showAdvancedMenu = false"
                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Quote
                        </button>
                        <button
                            type="button"
                            @click="editor?.chain().focus().toggleCodeBlock().run(); showAdvancedMenu = false"
                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Code Block
                        </button>
                        <button
                            type="button"
                            @click="editor?.chain().focus().setHorizontalRule().run(); showAdvancedMenu = false"
                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Divider
                        </button>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <button
                            type="button"
                            @click="editor?.chain().focus().setTextAlign('left').run(); showAdvancedMenu = false"
                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Align Left
                        </button>
                        <button
                            type="button"
                            @click="editor?.chain().focus().setTextAlign('center').run(); showAdvancedMenu = false"
                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Align Center
                        </button>
                        <button
                            type="button"
                            @click="editor?.chain().focus().setTextAlign('right').run(); showAdvancedMenu = false"
                            class="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            Align Right
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Editor Content -->
        <div class="bg-white dark:bg-gray-900 flex-1 flex flex-col">
            <EditorContent :editor="editor" class="flex-1" />
        </div>
    </div>
</template>

<style>
.tiptap-editor {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.tiptap-editor .ProseMirror {
    flex: 1;
    min-height: 300px;
}

.tiptap-editor .ProseMirror p.is-editor-empty:first-child::before {
    color: #9ca3af;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

.tiptap-editor .ProseMirror:focus {
    outline: none;
}

.tiptap-editor .ProseMirror h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
}

.tiptap-editor .ProseMirror h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 1rem 0 0.5rem;
}

.tiptap-editor .ProseMirror h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 1rem 0 0.5rem;
}

.tiptap-editor .ProseMirror p {
    margin: 0.5rem 0;
}

.tiptap-editor .ProseMirror ul,
.tiptap-editor .ProseMirror ol {
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}

.tiptap-editor .ProseMirror li {
    margin: 0.25rem 0;
}

.tiptap-editor .ProseMirror blockquote {
    border-left: 3px solid #d1d5db;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #6b7280;
}

.dark .tiptap-editor .ProseMirror blockquote {
    border-left-color: #4b5563;
    color: #9ca3af;
}

.tiptap-editor .ProseMirror pre {
    background-color: #1f2937;
    color: #e5e7eb;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
    overflow-x: auto;
}

.tiptap-editor .ProseMirror code {
    font-family: monospace;
    font-size: 0.9em;
}

.tiptap-editor .ProseMirror hr {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 1.5rem 0;
}

.dark .tiptap-editor .ProseMirror hr {
    border-top-color: #374151;
}

.tiptap-editor .ProseMirror mark {
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
}

.timestamp-link {
    display: inline-block;
    background-color: #dbeafe;
    color: #1d4ed8;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-family: monospace;
    font-size: 0.875rem;
    cursor: pointer;
    text-decoration: none;
    margin: 0 0.125rem;
}

.dark .timestamp-link {
    background-color: #1e3a8a;
    color: #93c5fd;
}

.timestamp-link:hover {
    background-color: #bfdbfe;
}

.dark .timestamp-link:hover {
    background-color: #1e40af;
}
</style>
