<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 overflow-hidden"
            @click.self="handleClose"
        >
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-card rounded-lg shadow-xl w-full h-full max-w-7xl flex flex-col">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-lg font-semibold">Content Preview</h2>
                            <div class="flex items-center space-x-2">
                                <button
                                    v-for="device in devices"
                                    :key="device.name"
                                    @click="selectedDevice = device.name"
                                    :class="[
                                        'px-3 py-1 text-sm rounded',
                                        selectedDevice === device.name
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-secondary text-foreground hover:bg-accent'
                                    ]"
                                >
                                    {{ device.label }}
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                v-if="canPublish"
                                @click="handlePublish"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Publish
                            </button>
                            <button
                                @click="handleClose"
                                class="p-2 text-gray-400 hover:text-muted-foreground"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Preview Content -->
                    <div class="flex-1 overflow-auto p-4" :class="deviceClass">
                        <div class="preview-content" v-html="renderedContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    content: {
        type: Object,
        default: () => ({}),
    },
    canPublish: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'publish']);

const { activeTheme, themeSettings } = useTheme();
const selectedDevice = ref('desktop');

const devices = [
    { name: 'desktop', label: 'Desktop', width: '100%' },
    { name: 'tablet', label: 'Tablet', width: '768px' },
    { name: 'mobile', label: 'Mobile', width: '375px' },
];

const deviceClass = computed(() => {
    const device = devices.find(d => d.name === selectedDevice.value);
    return {
        'max-w-full': selectedDevice.value === 'desktop',
        'max-w-3xl mx-auto': selectedDevice.value === 'tablet',
        'max-w-sm mx-auto': selectedDevice.value === 'mobile',
    };
});

const renderedContent = computed(() => {
    const { title, body, excerpt, featured_image, author, category, published_at } = props.content;
    
    let html = '';
    
    if (featured_image) {
        html += `<img src="${featured_image}" alt="${title}" class="w-full h-64 object-cover rounded-lg mb-6" />`;
    }
    
    html += `<h1 class="text-4xl font-bold mb-4">${title || 'Untitled'}</h1>`;
    
    if (excerpt) {
        html += `<p class="text-xl text-muted-foreground mb-6">${excerpt}</p>`;
    }
    
    if (author || category || published_at) {
        html += '<div class="flex items-center space-x-4 text-sm text-muted-foreground mb-6">';
        if (author) html += `<span>By ${author.name || author}</span>`;
        if (category) html += `<span>• ${category.name || category}</span>`;
        if (published_at) html += `<span>• ${new Date(published_at).toLocaleDateString()}</span>`;
        html += '</div>';
    }
    
    html += `<div class="prose max-w-none">${body || ''}</div>`;
    
    return html;
});

const handleClose = () => {
    emit('close');
};

const handlePublish = () => {
    emit('publish');
    handleClose();
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        selectedDevice.value = 'desktop';
    }
});
</script>

<style scoped>
.preview-content {
    min-height: 100%;
}

/* Apply theme styles if available */
:deep(.preview-content) {
    color: var(--theme-text-color, #1f2937);
    background-color: var(--theme-background-color, #ffffff);
}

:deep(.preview-content h1) {
    color: var(--theme-primary-color, #2563eb);
}

:deep(.preview-content .prose) {
    color: var(--theme-text-color, #1f2937);
}

:deep(.prose p) {
    margin-bottom: 1rem;
}

:deep(.prose img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}
</style>

