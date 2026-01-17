<script setup>
defineProps({
    customClass: {
        type: String,
        default: ''
    },
    showText: {
        type: Boolean,
        default: true
    },
    size: {
        type: String,
        default: 'md'
    }
});

const sizes = {
    sm: { box: 'w-8 h-8', icon: 'w-5 h-5', text: 'text-base' },
    md: { box: 'w-10 h-10', icon: 'w-6 h-6', text: 'text-lg' },
    lg: { box: 'w-14 h-14', icon: 'w-8 h-8', text: 'text-2xl' }
};
</script>

<template>
    <div :class="['flex items-center gap-2', customClass]">
        <div :class="[sizes[size].box, 'bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md']">
            <svg :class="sizes[size].icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Notepad body -->
                <rect x="4" y="5" width="16" height="16" rx="2" fill="white"/>
                <!-- Rings -->
                <rect x="7" y="2" width="2.5" height="5" rx="1.25" fill="#1e3a5f"/>
                <rect x="14.5" y="2" width="2.5" height="5" rx="1.25" fill="#1e3a5f"/>
                <!-- Play button -->
                <path d="M9 9L9 17L16 13L9 9Z" fill="#3b82f6"/>
            </svg>
        </div>
        <span 
            v-if="showText" 
            :class="[sizes[size].text, 'font-semibold text-slate-900 dark:text-white']"
        >
            VidNotes
        </span>
    </div>
</template>