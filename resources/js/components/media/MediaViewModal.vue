<template>
    <div class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-card border border-border shadow-lg rounded-lg max-w-5xl w-full max-h-[90vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-3 border-b shrink-0">
                    <h3 class="text-base font-semibold truncate">{{ media.name }}</h3>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </Button>
                </div>

                <!-- Content -->
                <div class="px-5 py-4 overflow-y-auto flex-1">
                    <!-- Grid Layout: Image Left, Details Right -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Left: Image Preview -->
                        <div class="flex flex-col">
                            <div v-if="media.mime_type?.startsWith('image/')">
                                <!-- Display Mode Selector -->
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-muted-foreground">{{ $t('features.media.modals.view.preview') }}</span>
                                    <div class="flex items-center gap-0.5 bg-muted p-0.5 rounded">
                                        <Button
                                            v-for="mode in displayModes"
                                            :key="mode.value"
                                            variant="ghost"
                                            size="sm"
                                            @click="displayMode = mode.value"
                                            :class="[
                                                'h-6 px-2 text-xs',
                                                displayMode === mode.value ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground'
                                            ]"
                                        >
                                            <component :is="mode.icon" class="w-3 h-3 mr-1" />
                                            {{ $t(`features.media.modals.view.${mode.value}`) }}
                                        </Button>
                                    </div>
                                </div>

                                <!-- Preview Container -->
                                <div 
                                    class="relative rounded-lg bg-secondary border border-border h-72"
                                    :class="displayMode === 'actual' ? 'overflow-auto' : 'overflow-hidden'"
                                    :style="displayMode === 'actual' ? { cursor: isDragging ? 'grabbing' : 'grab' } : {}"
                                    ref="previewContainer"
                                    @mousedown="startDrag"
                                    @mousemove="onDrag"
                                    @mouseup="stopDrag"
                                    @mouseleave="stopDrag"
                                    @touchstart="startDrag"
                                    @touchmove="onDrag"
                                    @touchend="stopDrag"
                                >
                                    <img 
                                        v-if="displayMode === 'contain'"
                                        :src="media.url" 
                                        :alt="media.alt || media.name" 
                                        :style="{ width: '100%', height: '100%', objectFit: 'contain' }"
                                    >
                                    <img 
                                        v-else-if="displayMode === 'stretch'"
                                        :src="media.url" 
                                        :alt="media.alt || media.name" 
                                        :style="{ 
                                            width: '100%', 
                                            height: '100%', 
                                            objectFit: 'cover',
                                            objectPosition: `${fillPosition.x}% ${fillPosition.y}%`,
                                            cursor: isDragging ? 'grabbing' : 'grab'
                                        }"
                                    >
                                    <img 
                                        v-else-if="displayMode === 'actual'"
                                        :src="media.url" 
                                        :alt="media.alt || media.name" 
                                        class="select-none"
                                        :style="{ maxWidth: 'none', maxHeight: 'none', minWidth: 'auto', minHeight: 'auto' }"
                                        draggable="false"
                                    >
                                </div>

                                <!-- Pan Hint -->
                                <p v-if="displayMode === 'actual' || displayMode === 'stretch'" class="text-xs text-muted-foreground mt-1.5 text-center">
                                    <Move class="w-3 h-3 inline-block mr-1" />
                                    {{ $t('features.media.modals.view.dragToPan') }}
                                </p>
                            </div>
                            
                            <!-- Video Preview -->
                            <div v-else-if="media.mime_type?.startsWith('video/')">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-medium text-muted-foreground">{{ $t('features.media.modals.view.preview') }}</span>
                                </div>
                                <div class="relative rounded-lg bg-secondary border border-border h-72 overflow-hidden">
                                    <video 
                                        :src="media.url" 
                                        controls 
                                        class="w-full h-full object-contain"
                                        preload="metadata"
                                    >
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            </div>
                            
                            <!-- Other File Types -->
                            <div v-else class="bg-secondary rounded-lg flex items-center justify-center h-72">
                                <FileText class="w-16 h-16 text-muted-foreground opacity-50" />
                            </div>

                            <!-- Quick Actions for Images -->
                            <div v-if="media.mime_type?.startsWith('image/')" class="flex flex-wrap gap-2 mt-3">
                                <Button
                                    @click="showImageEditor = true"
                                    size="sm"
                                    variant="secondary"
                                    class="bg-purple-600 text-white hover:bg-purple-700"
                                >
                                    <Edit class="w-3.5 h-3.5 mr-1.5" />
                                    {{ $t('features.media.modals.view.edit') }}
                                </Button>
                                <Button
                                    @click="showResizeModal = true"
                                    size="sm"
                                    variant="outline"
                                >
                                    <Scissors class="w-3.5 h-3.5 mr-1.5" />
                                    {{ $t('features.media.modals.view.resize') }}
                                </Button>
                                <Button
                                    @click="generateThumbnail"
                                    size="sm"
                                    variant="outline"
                                    :disabled="generatingThumbnail"
                                >
                                    <RefreshCw :class="['w-3.5 h-3.5 mr-1.5', generatingThumbnail ? 'animate-spin' : '']" />
                                    {{ generatingThumbnail ? $t('features.media.modals.view.generating') : $t('features.media.modals.view.thumbnail') }}
                                </Button>
                            </div>
                        </div>

                        <!-- Right: Details -->
                        <div class="space-y-4">
                            <!-- Media Details -->
                            <div class="bg-muted/50 rounded-lg p-3">
                                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">{{ $t('features.media.modals.view.details') }}</h4>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">{{ $t('features.media.modals.view.name') }}</dt>
                                        <dd class="text-foreground font-medium text-right truncate max-w-[60%]">{{ media.name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">{{ $t('features.media.modals.view.type') }}</dt>
                                        <dd class="text-foreground">{{ media.mime_type }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-muted-foreground">{{ $t('features.media.modals.view.size') }}</dt>
                                        <dd class="text-foreground">{{ formatFileSize(media.size) }}</dd>
                                    </div>
                                    <div v-if="media.folder" class="flex justify-between">
                                        <dt class="text-muted-foreground">{{ $t('features.media.modals.view.folder') }}</dt>
                                        <dd class="text-foreground">{{ media.folder.name }}</dd>
                                    </div>
                                    <div v-if="media.alt" class="flex justify-between">
                                        <dt class="text-muted-foreground">{{ $t('features.media.modals.view.altText') }}</dt>
                                        <dd class="text-foreground text-right truncate max-w-[60%]">{{ media.alt }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- URL Copy -->
                            <div class="bg-muted/50 rounded-lg p-3">
                                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">{{ $t('features.media.modals.view.url') }}</h4>
                                <div class="flex items-center gap-2">
                                    <input
                                        :value="media.url"
                                        readonly
                                        class="flex-1 px-2 py-1.5 border border-input bg-card text-foreground rounded text-xs font-mono"
                                    >
                                    <Button @click="copyUrl" size="sm" variant="outline">
                                        <Copy class="w-3.5 h-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Usage -->
                            <div class="bg-muted/50 rounded-lg p-3">
                                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">{{ $t('features.media.modals.view.usage') }}</h4>
                                <div v-if="loadingUsage" class="text-xs text-muted-foreground">
                                    {{ $t('features.media.modals.view.loadingUsage') }}
                                </div>
                                <div v-else-if="usageDetail && usageDetail.length > 0" class="space-y-1.5 max-h-24 overflow-y-auto">
                                    <div
                                        v-for="usage in usageDetail"
                                        :key="usage.id"
                                        class="text-xs text-foreground p-1.5 bg-background rounded"
                                    >
                                        <span class="font-medium">{{ usage.type }}</span>
                                        <span v-if="usage.title" class="text-muted-foreground"> · {{ usage.title }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-muted-foreground">
                                    {{ $t('features.media.modals.view.notUsed') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end px-5 py-3 border-t shrink-0">
                    <Button
                        variant="outline"
                        @click="$emit('close')"
                    >
                        {{ $t('features.media.modals.view.close') }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- Resize Modal -->
        <ResizeMediaModal
            v-if="showResizeModal"
            @close="showResizeModal = false"
            @resized="handleResized"
            :media="media"
        />

        <!-- Image Editor Modal -->
        <ImageEditor
            v-if="showImageEditor"
            @close="showImageEditor = false"
            @updated="handleImageEdited"
            :media="media"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, markRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/composables/useToast.js';
import { X, Copy, Edit, Scissors, RefreshCw, Move, Square, Maximize, FileText } from 'lucide-vue-next';
import api from '../../services/api';
import Button from '../ui/button.vue';
import ResizeMediaModal from './ResizeMediaModal.vue';
import ImageEditor from './ImageEditor.vue';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'updated']);
const { t } = useI18n();
const toast = useToast();

const loadingUsage = ref(false);
const usageDetail = ref([]);
const generatingThumbnail = ref(false);
const showResizeModal = ref(false);
const showImageEditor = ref(false);

// Display mode state
const displayMode = ref('stretch');
const displayModes = [
    { value: 'contain', label: 'Fit', icon: markRaw(Square) },
    { value: 'stretch', label: 'Fill', icon: markRaw(Maximize) },
    { value: 'actual', label: '1:1', icon: markRaw(Move) },
];

// Drag state
const previewContainer = ref(null);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0, scrollLeft: 0, scrollTop: 0, fillX: 50, fillY: 50 });
const fillPosition = ref({ x: 50, y: 50 });

const startDrag = (e) => {
    if (displayMode.value !== 'actual' && displayMode.value !== 'stretch') return;
    isDragging.value = true;
    const pos = e.touches ? e.touches[0] : e;
    dragStart.value = {
        x: pos.clientX,
        y: pos.clientY,
        scrollLeft: previewContainer.value?.scrollLeft || 0,
        scrollTop: previewContainer.value?.scrollTop || 0,
        fillX: fillPosition.value.x,
        fillY: fillPosition.value.y,
    };
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    if (displayMode.value !== 'actual' && displayMode.value !== 'stretch') return;
    
    e.preventDefault();
    const pos = e.touches ? e.touches[0] : e;
    const dx = pos.clientX - dragStart.value.x;
    const dy = pos.clientY - dragStart.value.y;

    if (displayMode.value === 'actual' && previewContainer.value) {
        previewContainer.value.scrollLeft = dragStart.value.scrollLeft - dx;
        previewContainer.value.scrollTop = dragStart.value.scrollTop - dy;
    } else if (displayMode.value === 'stretch' && previewContainer.value) {
        // Calculate percentage change based on container size
        // Moving right (positive dx) means looking left (decreasing position %)
        const containerWidth = previewContainer.value.clientWidth;
        const containerHeight = previewContainer.value.clientHeight;
        
        const deltaXPercent = (dx / containerWidth) * 100;
        const deltaYPercent = (dy / containerHeight) * 100;

        // Invert delta because dragging content right means moving viewport left
        fillPosition.value = {
            x: Math.max(0, Math.min(100, dragStart.value.fillX - deltaXPercent)),
            y: Math.max(0, Math.min(100, dragStart.value.fillY - deltaYPercent)),
        };
    }
};

const stopDrag = () => {
    isDragging.value = false;
};

const fetchUsageDetail = async () => {
    loadingUsage.value = true;
    try {
        const response = await api.get(`/admin/ja/media/${props.media.id}/usage`);
        usageDetail.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch usage detail:', error);
        usageDetail.value = [];
    } finally {
        loadingUsage.value = false;
    }
};

const generateThumbnail = async () => {
    generatingThumbnail.value = true;
    try {
        await api.post(`/admin/ja/media/${props.media.id}/thumbnail`);
        toast.success.thumbnail();
        emit('updated');
    } catch (error) {
        console.error('Failed to generate thumbnail:', error);
        toast.error.fromResponse(error);
    } finally {
        generatingThumbnail.value = false;
    }
};

const handleResized = () => {
    emit('updated');
    showResizeModal.value = false;
};

const handleImageEdited = () => {
    emit('updated');
    showImageEditor.value = false;
};

const copyUrl = () => {
    navigator.clipboard.writeText(props.media.url);
    toast.success.urlCopied();
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchUsageDetail();
});
</script>
