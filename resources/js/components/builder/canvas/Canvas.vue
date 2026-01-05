<template>
    <div class="flex-1 min-h-0 relative flex flex-col">
        <div 
            ref="scrollContainer"
            @scroll="handleScroll"
            class="flex-1 overflow-y-auto bg-muted/50 bg-dot-pattern p-6 pb-20 custom-scrollbar flex flex-col items-center"
        >

            <!-- Draggable Canvas -->
            <div 
                :class="[
                    canvasWidthClass, 
                    'bg-background shadow-2xl transition-[width] duration-200 ease-in-out rounded-xl border border-border relative w-full shrink-0 flex flex-col'
                ]"
                :style="{ minHeight: '600px' }"
            >
                <!-- Theme Provider Wrapper: Remaps global variables to theme-specific ones -->
                <div class="theme-provider contents" :style="themeStyles">
                    <draggable 
                        v-model="builder.blocks.value" 
                        item-key="id"
                        :group="{ name: 'blocks', pull: true, put: true }"
                        handle=".drag-handle"
                        class="space-y-0 min-h-[600px] w-full pb-20 flex-1"
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
                </div>
                
                <!-- Empty Placeholder -->
                <!-- Empty Placeholder / Add Section -->
                <div v-if="builder.blocks.value.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground gap-6 md:p-12 text-center z-50">
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
                <div v-else class="py-8 flex justify-center">
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
        default: return 'max-w-full';
    }
});

const editBlock = (index) => {
    builder.editingIndex.value = index;
    builder.activeTab.value = 'content';
};

const addSection = () => {
    console.log('[Canvas] Add Section clicked');
    const sectionDef = blockRegistry.get('section');
    console.log('[Canvas] Section def:', sectionDef);
    
    if (!sectionDef) {
        console.error('[Canvas] Section definition not found!');
        return;
    }

    const newBlock = {
        id: generateUUID(),
        type: 'section',
        settings: JSON.parse(JSON.stringify(sectionDef.defaultSettings))
    };
    
    console.log('[Canvas] Adding new block:', newBlock);
    builder.blocks.value.push(newBlock);
    builder.takeSnapshot();
    console.log('[Canvas] Block added. Helper blocks:', builder.blocks.value);
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
