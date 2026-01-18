<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    current: {
        type: Number,
        required: true
    },
    max: {
        type: Number,
        required: true
    },
    type: {
        type: String,
        default: 'items'
    }
});

const percentage = computed(() => {
    return Math.min(100, (props.current / props.max) * 100);
});

const isNearLimit = computed(() => {
    return percentage.value >= 80;
});

const isAtLimit = computed(() => {
    return props.current >= props.max;
});
</script>

<template>
    <div v-if="!$page.props.auth.user.isPremium" class="space-y-2">
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-600 dark:text-slate-400">
                {{ current }} / {{ max }} {{ type }}
            </span>
            <Link 
                v-if="isNearLimit"
                :href="route('subscription.pricing')"
                class="text-blue-600 dark:text-blue-400 hover:underline text-xs"
            >
                Upgrade
            </Link>
        </div>
        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
            <div 
                class="h-2 rounded-full transition-all duration-300"
                :class="{
                    'bg-green-500': percentage < 60,
                    'bg-yellow-500': percentage >= 60 && percentage < 80,
                    'bg-orange-500': percentage >= 80 && percentage < 100,
                    'bg-red-500': percentage >= 100
                }"
                :style="{ width: percentage + '%' }"
            ></div>
        </div>
        <p v-if="isAtLimit" class="text-xs text-red-500">
            Limit reached. <Link :href="route('subscription.pricing')" class="underline">Upgrade to Premium</Link> for unlimited {{ type }}.
        </p>
    </div>
</template>