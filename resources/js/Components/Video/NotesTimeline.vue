<script setup>
import { computed } from 'vue';

const props = defineProps({
    notes: {
        type: Array,
        default: () => [],
    },
    documentJson: {
        type: Object,
        default: null,
    },
    videoDuration: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['seek', 'highlight-note', 'highlight-timestamp']);

// Extract timestamps from document JSON
const extractEditorTimestamps = (json) => {
    const timestamps = [];
    
    const traverse = (node) => {
        if (!node) return;
        
        if (node.type === 'text' && node.marks) {
            const linkMark = node.marks.find(m => m.type === 'link');
            if (linkMark?.attrs?.href?.startsWith('#timestamp-')) {
                const seconds = parseInt(linkMark.attrs.href.replace('#timestamp-', ''));
                if (!isNaN(seconds)) {
                    timestamps.push({
                        timestamp: seconds,
                        type: 'editor',
                        text: node.text,
                    });
                }
            }
        }
        
        if (node.content && Array.isArray(node.content)) {
            node.content.forEach(traverse);
        }
    };
    
    traverse(json);
    return timestamps;
};

// Combine all markers
const markers = computed(() => {
    const allMarkers = [];
    
    props.notes.forEach(note => {
        if (note.timestamp !== null && note.timestamp !== undefined) {
            allMarkers.push({
                id: note.id,
                timestamp: note.timestamp,
                type: 'quicknote',
                text: note.content?.substring(0, 50) || '',
            });
        }
    });
    
    if (props.documentJson) {
        const editorTimestamps = extractEditorTimestamps(props.documentJson);
        editorTimestamps.forEach((ts, index) => {
            allMarkers.push({
                ...ts,
                id: `editor-${ts.timestamp}-${index}`,
            });
        });
    }
    
    return allMarkers.sort((a, b) => a.timestamp - b.timestamp);
});

const getMarkerPosition = (timestamp) => {
    if (!props.videoDuration || props.videoDuration === 0) return 0;
    return Math.min((timestamp / props.videoDuration) * 100, 100);
};

const formatTimestamp = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const handleMarkerClick = (marker) => {
    emit('seek', marker.timestamp);
    
    if (marker.type === 'quicknote') {
        emit('highlight-note', marker.id);
    } else {
        emit('highlight-timestamp', marker.timestamp);
    }
};
</script>

<template>
    <div 
        v-if="videoDuration > 0 && markers.length > 0"
        class="mt-2 px-1"
    >
        <!-- Timeline bar -->
        <div class="relative h-6 flex items-center group">
            <!-- Background track -->
            <div class="absolute inset-x-0 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
            
            <!-- Markers -->
            <button
                v-for="(marker, index) in markers"
                :key="`${marker.type}-${marker.timestamp}-${index}`"
                @click="handleMarkerClick(marker)"
                class="absolute w-3 h-3 rounded-full transform -translate-x-1/2 transition-transform hover:scale-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                :class="marker.type === 'quicknote' ? 'bg-blue-500 hover:bg-blue-400' : 'bg-purple-500 hover:bg-purple-400'"
                :style="{ left: `${getMarkerPosition(marker.timestamp)}%` }"
                :title="`${formatTimestamp(marker.timestamp)}${marker.text ? ' - ' + marker.text : ''}`"
            >
            </button>
        </div>
        
        <!-- Legend -->
        <div class="flex items-center gap-4 mt-1">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Quick Notes</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Timestamps</span>
            </div>
        </div>
    </div>
</template>