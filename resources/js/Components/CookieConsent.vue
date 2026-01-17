<script setup>
import { ref, onMounted } from 'vue';

const showBanner = ref(false);
const analyticsId = ref('');

onMounted(() => {
    analyticsId.value = document.querySelector('meta[name="analytics-id"]')?.content || '';
    
    const consent = localStorage.getItem('cookie-consent');
    if (consent === null) {
        showBanner.value = true;
    } else if (consent === 'accepted' && analyticsId.value) {
        loadAnalytics();
    }
});

const acceptCookies = () => {
    localStorage.setItem('cookie-consent', 'accepted');
    showBanner.value = false;
    if (analyticsId.value) {
        loadAnalytics();
    }
};

const refuseCookies = () => {
    localStorage.setItem('cookie-consent', 'refused');
    showBanner.value = false;
};

const loadAnalytics = () => {
    if (!analyticsId.value || window.gtag) return;
    
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${analyticsId.value}`;
    document.head.appendChild(script);
    
    window.dataLayer = window.dataLayer || [];
    window.gtag = function(){ window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', analyticsId.value);
};
</script>

<template>
    <Transition name="slide">
        <div 
            v-if="showBanner"
            class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg z-50 p-4"
        >
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-600 dark:text-gray-300 text-center sm:text-left">
                    We use cookies to analyze our traffic and improve your experience. 
                    <a href="/privacy" class="text-blue-600 dark:text-blue-400 underline">Learn more</a>
                </p>
                <div class="flex items-center gap-3">
                    <button
                        @click="refuseCookies"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors"
                    >
                        Refuse
                    </button>
                    <button
                        @click="acceptCookies"
                        class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                        Accept
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
    transform: translateY(100%);
}
</style>