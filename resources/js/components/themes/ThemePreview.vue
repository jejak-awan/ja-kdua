<template>
    <div class="theme-preview-container">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Theme Preview</h3>
            <div class="flex items-center gap-2">
                <Select v-model="selectedDevice">
                    <SelectTrigger class="h-8 w-[100px]">
                        <SelectValue placeholder="Device" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="desktop">Desktop</SelectItem>
                        <SelectItem value="tablet">Tablet</SelectItem>
                        <SelectItem value="mobile">Mobile</SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    variant="secondary"
                    size="icon"
                    class="h-8 w-8"
                    @click="refreshPreview"
                    title="Refresh preview"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8"
                    @click="$emit('close')"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </Button>
            </div>
        </div>

        <div class="border border-input rounded-lg overflow-hidden bg-secondary" :class="deviceClasses">
            <div class="bg-card" :style="iframeStyle">
                <iframe
                    ref="previewFrame"
                    :src="previewUrl"
                    class="w-full border-0"
                    :style="iframeStyle"
                    @load="onPreviewLoad"
                />
            </div>
        </div>

        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-card bg-opacity-75">
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                <p class="mt-2 text-sm text-muted-foreground">Loading preview...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import Button from '../ui/button.vue';
import Select from '../ui/select.vue';
import SelectContent from '../ui/select-content.vue';
import SelectItem from '../ui/select-item.vue';
import SelectTrigger from '../ui/select-trigger.vue';
import SelectValue from '../ui/select-value.vue';

const props = defineProps({
    theme: {
        type: Object,
        required: true,
    },
    previewUrl: {
        type: String,
        default: '/',
    },
});

const emit = defineEmits(['close']);

const previewFrame = ref(null);
const selectedDevice = ref('desktop');
const loading = ref(true);

const deviceClasses = computed(() => {
    const classes = {
        desktop: 'max-w-full',
        tablet: 'max-w-3xl mx-auto',
        mobile: 'max-w-sm mx-auto',
    };
    return classes[selectedDevice.value] || classes.desktop;
});

const iframeStyle = computed(() => {
    const styles = {
        desktop: { height: '800px' },
        tablet: { height: '1024px', width: '768px' },
        mobile: { height: '667px', width: '375px' },
    };
    return styles[selectedDevice.value] || styles.desktop;
});

const refreshPreview = () => {
    if (previewFrame.value) {
        loading.value = true;
        previewFrame.value.src = previewFrame.value.src;
    }
};

// Inject CSS variables and custom CSS into iframe
const injectThemeStyles = () => {
    if (!previewFrame.value || !props.theme) return;

    try {
        const iframeDoc = previewFrame.value.contentDocument || previewFrame.value.contentWindow.document;
        if (!iframeDoc) return;

        // Remove existing theme styles
        const existingStyle = iframeDoc.getElementById('theme-customizer-styles');
        if (existingStyle) {
            existingStyle.remove();
        }

        // Create new style element
        const style = iframeDoc.createElement('style');
        style.id = 'theme-customizer-styles';
        
        let css = '';

        // Add CSS variables
        if (props.theme.css_variables) {
            css += props.theme.css_variables + '\n\n';
        }

        // Apply settings as CSS variables if not already done
        if (props.theme.settings) {
            const settings = props.theme.settings;
            
            // Color variables
            if (settings.primary_color) {
                css += `:root { --theme-primary-color: ${settings.primary_color}; }\n`;
            }
            if (settings.secondary_color) {
                css += `:root { --theme-secondary-color: ${settings.secondary_color}; }\n`;
            }
            if (settings.accent_color) {
                css += `:root { --theme-accent-color: ${settings.accent_color}; }\n`;
            }
            if (settings.background_color) {
                css += `body { background-color: ${settings.background_color}; }\n`;
            }
            if (settings.text_color) {
                css += `body { color: ${settings.text_color}; }\n`;
            }

            // Typography
            if (settings.heading_font) {
                css += `h1, h2, h3, h4, h5, h6 { font-family: '${settings.heading_font}', sans-serif; }\n`;
            }
            if (settings.body_font) {
                css += `body { font-family: '${settings.body_font}', sans-serif; }\n`;
            }
            if (settings.font_size_base) {
                css += `html { font-size: ${settings.font_size_base}px; }\n`;
            }

            // Layout
            if (settings.container_width) {
                css += `.container, .max-w-\\[1280px\\] { max-width: ${settings.container_width}; }\n`;
            }
        }

        // Add custom CSS
        if (props.theme.custom_css) {
            css += '\n' + props.theme.custom_css;
        }

        style.textContent = css;
        iframeDoc.head.appendChild(style);
    } catch (error) {
        console.warn('Failed to inject theme styles into preview:', error);
    }
};

const onPreviewLoad = () => {
    loading.value = false;
    
    // Inject theme CSS assets if available
    if (previewFrame.value && previewFrame.value.contentDocument) {
        const iframeDoc = previewFrame.value.contentDocument;
        const iframeHead = iframeDoc.head;
        
        // Inject theme CSS if available
        if (props.theme.assets?.css) {
            props.theme.assets.css.forEach(cssFile => {
                const link = iframeDoc.createElement('link');
                link.rel = 'stylesheet';
                link.href = cssFile.startsWith('http') ? cssFile : `/${cssFile}`;
                iframeHead.appendChild(link);
            });
        }
    }
    
    // Inject theme styles (CSS variables, settings, custom CSS)
    injectThemeStyles();
};

// Watch for theme changes and re-inject styles
watch(() => props.theme, () => {
    if (previewFrame.value && previewFrame.value.contentDocument) {
        injectThemeStyles();
    }
}, { deep: true });

watch(() => selectedDevice.value, () => {
    // Device change doesn't need full refresh, just re-inject styles
    setTimeout(() => {
        injectThemeStyles();
    }, 100);
});

onMounted(() => {
    // Set initial preview URL with theme parameter
    if (!props.previewUrl.includes('?')) {
        // Add theme preview parameter if needed
    }
});
</script>

<style scoped>
.theme-preview-container {
    position: relative;
    background-color: hsl(var(--card));
    border-radius: var(--radius);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    border: 1px solid hsl(var(--border));
}

iframe {
    transition: all 0.3s ease;
}
</style>

