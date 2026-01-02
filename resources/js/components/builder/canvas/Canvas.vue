<template>
    <div class="flex-1 min-h-0 overflow-y-auto bg-background/50 bg-dot-pattern p-6 custom-scrollbar relative flex flex-col items-center">
        <!-- Canvas Toolbar (Device Switching) -->
        <div class="w-full max-w-5xl mb-4 flex items-center justify-center gap-2 relative shrink-0">
            <div class="flex items-center gap-1 p-1 bg-muted rounded-full shadow-sm border border-border">
                <Button 
                    v-for="mode in ['mobile', 'tablet', 'desktop']" 
                    :key="mode"
                    variant="ghost" 
                    size="icon" 
                    class="h-7 w-12 rounded-full transition-all text-muted-foreground hover:text-foreground" 
                    :class="builder.deviceMode.value === mode ? 'bg-background shadow-sm text-foreground' : ''"
                    @click="builder.deviceMode.value = mode"
                >
                    <component :is="mode === 'mobile' ? Smartphone : mode === 'tablet' ? Tablet : Monitor" class="w-3.5 h-3.5" />
                </Button>
            </div>
        </div>

        <!-- Draggable Canvas -->
        <div 
            :class="[
                canvasWidthClass, 
                'bg-background shadow-2xl transition-all duration-500 ease-in-out rounded-xl border border-border relative w-full shrink-0 flex flex-col'
            ]"
            :style="{ minHeight: '600px' }"
        >
            <!-- Theme Provider Wrapper: Remaps global variables to theme-specific ones -->
            <div class="theme-provider contents" :style="themeStyles">
                <draggable 
                    v-model="builder.blocks.value" 
                    item-key="id"
                    group="blocks"
                    handle=".drag-handle"
                    class="space-y-0 min-h-[600px] w-full pb-20 flex-1"
                    ghost-class="block-ghost"
                >
                    <template #item="{ element: block, index }">
                        <BlockWrapper 
                            :block="block" 
                            :index="index"
                            :isNested="false"
                            @edit="editBlock(index)"
                            @duplicate="builder.duplicateBlock(index)"
                            @delete="builder.removeBlock(index)"
                        />
                    </template>
                </draggable>
            </div>
            
            <!-- Empty Placeholder -->
            <div v-if="builder.blocks.value.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground gap-6 pointer-events-none p-6 md:p-12 text-center">
                <div class="w-20 h-20 rounded-3xl bg-muted/50 border border-border flex items-center justify-center animate-pulse">
                    <Plus class="w-10 h-10 opacity-20" />
                </div>
                <div class="space-y-1 w-full">
                    <h3 class="text-lg font-bold text-foreground">{{ t('features.builder.canvas.empty.title') }}</h3>
                    <p class="text-sm max-w-[260px] md:max-w-xs mx-auto text-muted-foreground">{{ t('features.builder.canvas.empty.description') }}</p>
                </div>
            </div>
        </div>

        <ContextMenu 
            :visible="contextMenu.visible" 
            :x="contextMenu.x" 
            :y="contextMenu.y" 
            :actions="contextMenuActions"
            @close="contextMenu.visible = false"
            @action="handleContextMenuAction"
        />
        
        <!-- Global Undo/Redo/History (Coming Soon) -->

        <div class="h-20 w-full shrink-0"></div>
    </div>
</template>

<script setup>
import { inject, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import draggable from 'vuedraggable';
import { 
    Smartphone, Tablet, Monitor, GripVertical, Copy, Trash2, Plus, MousePointer2, PanelLeftOpen 
} from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import BlockRenderer from '../blocks/BlockRenderer.vue';
import BlockWrapper from './BlockWrapper.vue';
import ContextMenu from '../ContextMenu.vue';
import { ref } from 'vue';

const builder = inject('builder');
const { t } = useI18n();

const contextMenu = ref({
    visible: false,
    x: 0,
    y: 0,
    index: null
});

const contextMenuActions = computed(() => [
    { label: t('features.builder.actions.duplicate'), icon: Copy, action: 'duplicate' },
    { label: t('features.builder.actions.delete'), icon: Trash2, action: 'delete', variant: 'destructive' }
]);

const handleContextMenu = (e, index) => {
    e.preventDefault();
    contextMenu.value = {
        visible: true,
        x: e.clientX,
        y: e.clientY,
        index
    };
};

const handleContextMenuAction = (action) => {
    if (contextMenu.value.index === null) return;
    
    switch (action.action) {
        case 'duplicate':
            builder.duplicateBlock(contextMenu.value.index);
            break;
        case 'delete':
            builder.removeBlock(contextMenu.value.index);
            break;
    }
};

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
    background-image: radial-gradient(rgba(0, 0, 0, 0.05) 1px, transparent 1px);
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
