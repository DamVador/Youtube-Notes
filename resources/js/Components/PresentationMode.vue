<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import html2pdf from 'html2pdf.js';

const props = defineProps({
    content: {
        type: String,
        required: true,
    },
    videoTitle: {
        type: String,
        default: 'Presentation',
    },
});

const emit = defineEmits(['close']);

const currentSlideIndex = ref(0);
const isAnimating = ref(false);
const showHelp = ref(false);

// Parse HTML content into slides
const slides = computed(() => {
    if (!props.content) return [];
    
    const parser = new DOMParser();
    const doc = parser.parseFromString(props.content, 'text/html');
    const result = [];
    let currentSlide = null;

    const processNode = (node) => {
        const tagName = node.tagName?.toLowerCase();
        
        if (['h1', 'h2'].includes(tagName)) {
            if (currentSlide) {
                result.push(currentSlide);
            }
            currentSlide = {
                title: node.textContent,
                content: [],
                level: tagName === 'h1' ? 1 : 2,
            };
        } else if (tagName === 'h3' && currentSlide) {
            currentSlide.content.push({
                type: 'subtitle',
                html: node.textContent,
            });
        } else if (currentSlide) {
            const html = node.outerHTML || node.textContent;
            if (html?.trim()) {
                currentSlide.content.push({
                    type: 'content',
                    html: html,
                });
            }
        } else if (!currentSlide && node.textContent?.trim()) {
            // Content before the first title
            currentSlide = {
                title: props.videoTitle,
                content: [{
                    type: 'content',
                    html: node.outerHTML || node.textContent,
                }],
                level: 1,
            };
        }
    };

    doc.body.childNodes.forEach(processNode);

    if (currentSlide) {
        result.push(currentSlide);
    }

    // If no slides, create a default one
    if (result.length === 0 && props.content.trim()) {
        result.push({
            title: props.videoTitle,
            content: [{ type: 'content', html: props.content }],
            level: 1,
        });
    }

    return result;
});

const currentSlide = computed(() => slides.value[currentSlideIndex.value]);
const totalSlides = computed(() => slides.value.length);
const progress = computed(() => ((currentSlideIndex.value + 1) / totalSlides.value) * 100);

const nextSlide = () => {
    if (currentSlideIndex.value < totalSlides.value - 1 && !isAnimating.value) {
        isAnimating.value = true;
        currentSlideIndex.value++;
        setTimeout(() => isAnimating.value = false, 300);
    }
};

const prevSlide = () => {
    if (currentSlideIndex.value > 0 && !isAnimating.value) {
        isAnimating.value = true;
        currentSlideIndex.value--;
        setTimeout(() => isAnimating.value = false, 300);
    }
};

const goToSlide = (index) => {
    if (index >= 0 && index < totalSlides.value && !isAnimating.value) {
        isAnimating.value = true;
        currentSlideIndex.value = index;
        setTimeout(() => isAnimating.value = false, 300);
    }
};

const handleKeydown = (e) => {
    switch (e.key) {
        case 'ArrowRight':
        case 'Space':
        case 'Enter':
            e.preventDefault();
            nextSlide();
            break;
        case 'ArrowLeft':
        case 'Backspace':
            e.preventDefault();
            prevSlide();
            break;
        case 'Escape':
            emit('close');
            break;
        case 'Home':
            e.preventDefault();
            goToSlide(0);
            break;
        case 'End':
            e.preventDefault();
            goToSlide(totalSlides.value - 1);
            break;
    }
};

const exportToPdf = () => {
    const container = document.createElement('div');
    
    slides.value.forEach((slide, index) => {
        const slideDiv = document.createElement('div');
        slideDiv.style.cssText = `
            page-break-after: always;
            padding: 60px;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            font-family: Arial, sans-serif;
        `;
        
        slideDiv.innerHTML = `
            <h1 style="font-size: 36px; margin-bottom: 40px; color: #60a5fa;">${slide.title}</h1>
            <div style="font-size: 20px; line-height: 1.8; color: #e2e8f0;">
                ${slide.content.map(c => c.html).join('')}
            </div>
            <div style="position: absolute; bottom: 40px; right: 60px; color: #64748b; font-size: 14px;">
                ${index + 1} / ${slides.value.length}
            </div>
        `;
        
        container.appendChild(slideDiv);
    });

    const opt = {
        margin: 0,
        filename: `${props.videoTitle.substring(0, 50).replace(/[^a-z0-9]/gi, '_')}_presentation.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak: { mode: 'css' }
    };

    html2pdf().set(opt).from(container).save();
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
    document.body.style.overflow = 'hidden';
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <div class="presentation-overlay">
            <!-- Header -->
            <div class="presentation-header">
                <div class="header-left">
                    <button @click="$emit('close')" class="close-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Exit</span>
                    </button>
                </div>
                <div class="header-center">
                    <span class="slide-counter">{{ currentSlideIndex + 1 }} / {{ totalSlides }}</span>
                </div>
                <div class="header-right">
                    <button @click="showHelp = !showHelp" class="help-btn" title="How to structure your notes">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <button @click="exportToPdf" class="export-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </button>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="progress-bar">
                <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
            </div>

            <!-- Help panel -->
            <Transition name="fade">
                <div v-if="showHelp" class="help-panel">
                    <div class="help-content">
                        <div class="help-header">
                            <h3>How to structure your notes</h3>
                            <button @click="showHelp = false" class="help-close">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="help-body">
                            <p class="help-intro">Use headings to create slides automatically:</p>
                            <div class="help-example">
                                <div class="example-row">
                                    <span class="example-tag">H1</span>
                                    <span class="example-text">Title slide (main sections)</span>
                                </div>
                                <div class="example-row">
                                    <span class="example-tag">H2</span>
                                    <span class="example-text">New slide (sub-sections)</span>
                                </div>
                                <div class="example-row">
                                    <span class="example-tag">H3</span>
                                    <span class="example-text">Subtitle within a slide</span>
                                </div>
                                <div class="example-row">
                                    <span class="example-tag">Text</span>
                                    <span class="example-text">Content for current slide</span>
                                </div>
                            </div>
                            <p class="help-tip">💡 Tip: Keep slides focused — one idea per heading works best!</p>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Slide content -->
            <div class="slide-container" @click="nextSlide">
                <Transition name="slide" mode="out-in">
                    <div v-if="currentSlide" :key="currentSlideIndex" class="slide">
                        <div class="slide-content">
                            <h1 
                                class="slide-title"
                                :class="{ 'title-h1': currentSlide.level === 1, 'title-h2': currentSlide.level === 2 }"
                            >
                                {{ currentSlide.title }}
                            </h1>
                            <div class="slide-body">
                                <template v-for="(item, idx) in currentSlide.content" :key="idx">
                                    <h3 v-if="item.type === 'subtitle'" class="slide-subtitle">
                                        {{ item.html }}
                                    </h3>
                                    <div v-else class="slide-text" v-html="item.html"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div v-else class="slide empty-slide">
                        <p>No content to display</p>
                        <p class="empty-hint">Add headings (H1, H2) in your notes to create slides</p>
                    </div>
                </Transition>
            </div>

            <!-- Navigation -->
            <div class="navigation">
                <button 
                    @click.stop="prevSlide" 
                    :disabled="currentSlideIndex === 0"
                    class="nav-btn"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                
                <!-- Slide dots -->
                <div class="slide-dots">
                    <button
                        v-for="(slide, index) in slides"
                        :key="index"
                        @click.stop="goToSlide(index)"
                        :class="['dot', { active: index === currentSlideIndex }]"
                        :title="`Slide ${index + 1}: ${slide.title}`"
                    ></button>
                </div>
                
                <button 
                    @click.stop="nextSlide" 
                    :disabled="currentSlideIndex === totalSlides - 1"
                    class="nav-btn"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Keyboard hints -->
            <div class="keyboard-hints">
                <span>← → Navigate</span>
                <!-- <span>Space Next</span> -->
                <span>Esc Exit</span>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.presentation-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    display: flex;
    flex-direction: column;
}

/* Header */
.presentation-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px);
}

.header-left, .header-right {
    flex: 1;
}

.header-right {
    display: flex;
    justify-content: flex-end;
}

.header-center {
    flex: 1;
    text-align: center;
}

.close-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 8px;
    color: #94a3b8;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.slide-counter {
    color: #94a3b8;
    font-size: 14px;
    font-weight: 500;
}

.export-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #3b82f6;
    border: none;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.export-btn:hover {
    background: #2563eb;
}

.help-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 8px;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.2s;
    margin-right: 12px;
}

.help-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Help panel */
.help-panel {
    position: absolute;
    top: 70px;
    right: 24px;
    z-index: 100;
}

.help-content {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    width: 320px;
    overflow: hidden;
}

.help-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.help-header h3 {
    font-size: 14px;
    font-weight: 600;
    color: white;
    margin: 0;
}

.help-close {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
}

.help-close:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
}

.help-body {
    padding: 16px;
}

.help-intro {
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 16px;
}

.help-example {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 16px;
}

.example-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.example-tag {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    min-width: 40px;
    text-align: center;
}

.example-text {
    font-size: 13px;
    color: #e2e8f0;
}

.help-tip {
    font-size: 12px;
    color: #94a3b8;
    background: rgba(250, 204, 21, 0.1);
    padding: 10px 12px;
    border-radius: 8px;
    border-left: 3px solid #eab308;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Progress bar */
.progress-bar {
    height: 3px;
    background: rgba(255, 255, 255, 0.1);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    transition: width 0.3s ease;
}

/* Slide container */
.slide-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 80px;
    cursor: pointer;
    overflow: hidden;
}

.slide {
    width: 100%;
    max-width: 1200px;
    max-height: 100%;
    overflow-y: auto;
}

.slide-content {
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-title {
    margin-bottom: 48px;
    line-height: 1.2;
}

.title-h1 {
    font-size: 56px;
    font-weight: 800;
    background: linear-gradient(135deg, #ffffff 0%, #60a5fa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.title-h2 {
    font-size: 48px;
    font-weight: 700;
    color: #60a5fa;
}

.slide-subtitle {
    font-size: 28px;
    font-weight: 600;
    color: #94a3b8;
    margin: 24px 0 16px;
}

.slide-body {
    font-size: 24px;
    line-height: 1.8;
    color: #e2e8f0;
}

.slide-text {
    margin-bottom: 16px;
}

.slide-text :deep(ul),
.slide-text :deep(ol) {
    margin-left: 32px;
    margin-bottom: 16px;
}

.slide-text :deep(li) {
    margin-bottom: 12px;
}

.slide-text :deep(code) {
    background: rgba(59, 130, 246, 0.2);
    padding: 2px 8px;
    border-radius: 4px;
    font-family: monospace;
    color: #60a5fa;
}

.slide-text :deep(pre) {
    background: rgba(0, 0, 0, 0.4);
    padding: 20px;
    border-radius: 12px;
    overflow-x: auto;
    font-size: 18px;
}

.slide-text :deep(blockquote) {
    border-left: 4px solid #3b82f6;
    padding-left: 24px;
    margin: 24px 0;
    color: #94a3b8;
    font-style: italic;
}

.slide-text :deep(mark) {
    background: rgba(250, 204, 21, 0.3);
    padding: 2px 6px;
    border-radius: 4px;
}

.slide-text :deep(.timestamp-link) {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    padding: 4px 12px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 20px;
}

.empty-slide {
    text-align: center;
    color: #64748b;
}

.empty-slide p {
    font-size: 24px;
    margin-bottom: 16px;
}

.empty-hint {
    font-size: 16px !important;
    color: #475569;
}

/* Navigation */
.navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    padding: 24px;
}

.nav-btn {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.nav-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.slide-dots {
    display: flex;
    gap: 8px;
    max-width: 400px;
    flex-wrap: wrap;
    justify-content: center;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.dot:hover {
    background: rgba(255, 255, 255, 0.4);
}

.dot.active {
    background: #3b82f6;
    transform: scale(1.3);
}

/* Keyboard hints */
.keyboard-hints {
    display: flex;
    justify-content: center;
    gap: 32px;
    padding: 16px;
    color: #475569;
    font-size: 12px;
}

.keyboard-hints span {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Slide transition */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.slide-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

/* Responsive */
@media (max-width: 768px) {
    .slide-container {
        padding: 24px;
    }
    
    .title-h1 {
        font-size: 36px;
    }
    
    .title-h2 {
        font-size: 28px;
    }
    
    .slide-body {
        font-size: 18px;
    }
    
    .keyboard-hints {
        display: none;
    }
    
    .export-btn span {
        display: none;
    }
}
</style>