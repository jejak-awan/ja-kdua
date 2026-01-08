<template>
    <div class="w-full h-full bg-background" :class="{'opacity-50': loading, 'pointer-events-none': loading}">
        <iframe
            ref="previewFrame"
            :src="previewUrl"
            class="w-full h-full border-0"
            @load="onPreviewLoad"
        />

        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-background/50 backdrop-blur-sm z-10">
            <div class="flex flex-col items-center gap-2">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <!-- <p class="text-xs text-muted-foreground font-medium">Loading Preview...</p> -->
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

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
const loading = ref(true);

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

onMounted(() => {
    // Set initial preview URL with theme parameter if needed
});
</script>

<style scoped>
iframe {
    transition: opacity 0.3s ease;
}
</style>

