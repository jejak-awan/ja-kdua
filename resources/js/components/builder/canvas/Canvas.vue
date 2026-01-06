<template>
    <div class="flex-1 min-h-0 relative flex flex-col">
        <div 
            ref="scrollContainer"
            @scroll="handleScroll"
            :class="[
                'flex-1 overflow-y-auto flex flex-col items-center custom-scrollbar',
                !builder.isPreview.value ? 'bg-muted/50 bg-dot-pattern p-6 pb-20' : 'bg-background'
            ]"
        >

            <!-- Draggable Canvas -->
            <div 
                :class="[
                    canvasWidthClass, 
                    'bg-background transition-all duration-300 ease-in-out relative w-full shrink-0 flex flex-col',
                    !builder.isPreview.value ? 'shadow-2xl rounded-xl border border-border mt-2' : ''
                ]"
                :style="{ minHeight: '600px' }"
            >
                <!-- Theme Provider Wrapper: Remaps global variables to theme-specific ones -->
                <div class="theme-provider flex flex-col min-h-full" :style="themeStyles">
                    <!-- Theme Header (Only in Preview) -->
                    <ThemePageResolver v-if="builder.isPreview.value" page="components/Header" class="shrink-0" />

                    <draggable 
                        v-model="builder.blocks.value" 
                        item-key="id"
                        :group="{ name: 'blocks', pull: true, put: true }"
                        handle=".drag-handle"
                        :class="[
                            builder.globalSettings.value.block_spacing,
                            'w-full flex-1',
                            !builder.isPreview.value ? 'min-h-[600px] pb-20' : ''
                        ]"
                        ghost-class="block-ghost"
                    >
                        <template #item="{ element: block, index }">
                            <BlockWrapper 
                                :block="block" 
                                :index="index"
                                :context="context"
                                :isNested="false"
                                @edit="editBlock(index)"
                                @duplicate="builder.duplicateBlock(index)"
                                @delete="builder.removeBlock(index)"
                            />
                        </template>
                    </draggable>

                    <!-- Theme Footer (Only in Preview) -->
                    <ThemePageResolver v-if="builder.isPreview.value" page="components/Footer" class="shrink-0" />
                </div>
                
                <!-- Empty Placeholder -->
                <!-- Empty Placeholder / Add Section -->
                <div v-if="builder.blocks.value.length === 0 && !builder.isPreview.value" class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground gap-6 md:p-12 text-center z-50">
                    <div class="w-20 h-20 rounded-3xl bg-muted/50 border border-border flex items-center justify-center animate-pulse">
                        <LayoutPanelTop class="w-10 h-10 opacity-20" />
                    </div>
                    <div class="space-y-1 w-full max-w-sm">
                        <h3 class="text-lg font-bold text-foreground">Start Building</h3>
                        <p class="text-sm text-muted-foreground mb-4">Add a container section to start your layout.</p>
                        <Button @click="addSection" size="lg" class="gap-2 shadow-lg">
                            <Plus class="w-4 h-4" />
                            Add Section
                        </Button>
                    </div>
                </div>
                
                <!-- Add Section at Bottom -->
                <div v-else-if="!builder.isPreview.value" class="py-8 flex justify-center">
                     <Button variant="outline" size="lg" class="gap-2 border-dashed border-2 text-muted-foreground hover:text-primary hover:border-primary hover:bg-primary/5 transition-all" @click="addSection">
                        <Plus class="w-5 h-5" />
                        Add Section
                    </Button>
                </div>
            </div>

            
            <!-- Global Undo/Redo/History (Coming Soon) -->

            <div class="h-20 w-full shrink-0"></div>
        </div>

        <BackToTop 
            :show="showBackToTop" 
            positionClass="bottom-6 right-6"
            @click="scrollToTop" 
        />
    </div>
</template>

<script setup>
import { inject, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import { 
    Smartphone, Tablet, Monitor, GripVertical, Copy, Trash2, Plus, MousePointer2, PanelLeftOpen, LayoutPanelTop 
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import BlockRenderer from '../blocks/BlockRenderer.vue';
import BlockWrapper from './BlockWrapper.vue';
import BackToTop from '@/components/ui/back-to-top.vue';
import { useScrollToTop } from '@/composables/useScrollToTop';
import { ref } from 'vue';
import { blockRegistry } from '../BlockRegistry';
import { generateUUID } from '../utils';
import ThemePageResolver from '@/components/ThemePageResolver.vue';

const props = defineProps({
    context: {
        type: Object,
        default: () => ({})
    }
});

const builder = inject('builder');
const { t } = useI18n();

const scrollContainer = ref(null);
const { showBackToTop, handleScroll, scrollToTop } = useScrollToTop(scrollContainer);

const canvasWidthClass = computed(() => {
    switch (builder.deviceMode.value) {
        case 'mobile': return 'max-w-[375px]';
        case 'tablet': return 'max-w-[768px]';
        default: return builder.globalSettings.value.container_max_width || 'max-w-full';
    }
});

const editBlock = (index) => {
    builder.editingIndex.value = index;
    builder.activeTab.value = 'content';
};

const addSection = () => {
    const sectionDef = blockRegistry.get('section');
    if (!sectionDef) return;

    const newBlock = {
        id: generateUUID(),
        type: 'section',
        settings: JSON.parse(JSON.stringify(sectionDef.defaultSettings))
    };
    
    builder.blocks.value.push(newBlock);
    builder.takeSnapshot();
    
    // Auto-select the new block
    builder.activeBlockId.value = newBlock.id;
    builder.editingIndex.value = builder.blocks.value.length - 1;
    builder.activeTab.value = 'content';
};

// Compute theme overrides for the canvas
const themeStyles = computed(() => {
    if (!builder.activeTheme?.value?.manifest?.settings_schema) return {};
    
    const styles = {};
    const settings = builder.activeTheme.value.manifest.settings_schema;
    
    Object.keys(settings).forEach(key => {
        const setting = settings[key];
        const cssKey = key.replace(/_/g, '-');
        
        // Colors mapping to Shadcn variables
        if (setting.type === 'color') {
            styles[`--${cssKey}`] = `var(--theme-${cssKey}-hsl)`;
        }
        
        // Typography mapping
        if (setting.type === 'font') {
            styles[`--font-${cssKey}`] = `var(--theme-font-${cssKey})`;
        }
    });
    
    return styles;
});
</script>

<style scoped>
.bg-dot-pattern {
    background-image: radial-gradient(hsl(var(--foreground) / 0.05) 1px, transparent 1px);
    background-size: 30px 30px;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.15);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: hsl(var(--muted-foreground) / 0.3);
}
</style>
