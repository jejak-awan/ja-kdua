<template>
    <div class="flex-1 overflow-y-auto bg-background/50 bg-dot-pattern p-8 custom-scrollbar relative flex flex-col items-center">
        <!-- Canvas Toolbar (Device Switching) -->
        <div class="w-full max-w-5xl mb-6 flex items-center justify-center gap-2 relative">
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
        <div :class="[canvasWidthClass, 'bg-background shadow-2xl min-h-[700px] h-fit transition-all duration-500 ease-in-out rounded-xl overflow-hidden border border-border relative']">
            <draggable 
                v-model="builder.blocks.value" 
                item-key="id"
                group="blocks"
                handle=".drag-handle"
                class="space-y-0 min-h-[700px] h-full w-full pb-20"
                ghost-class="block-ghost"
            >
                <template #item="{ element: block, index }">
                     <div 
                        class="group/block relative border-2 border-transparent hover:border-primary/50 transition-all cursor-pointer"
                        :class="{ 'border-primary ring-4 ring-primary/10 z-10': builder.editingIndex.value === index }"
                        @click.stop="editBlock(index)"
                    >
                        <!-- Block Toolbar (Elementor Style) -->
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 opacity-0 group-hover/block:opacity-100 transition-all z-30 flex items-center gap-0.5 bg-primary text-primary-foreground rounded-full px-1.5 py-1 shadow-xl scale-90 translate-y-1 group-hover/block:translate-y-0">
                            <GripVertical class="w-3 h-3 cursor-move drag-handle mx-0.5" />
                            <div class="w-px h-3 bg-primary-foreground/20 mx-1"></div>
                            <button class="h-6 w-6 flex items-center justify-center hover:bg-primary-foreground/20 rounded-full transition-colors" @click.stop="builder.duplicateBlock(index)">
                                <Copy class="w-3 h-3" />
                            </button>
                            <button class="h-6 w-6 flex items-center justify-center hover:bg-primary-foreground/20 rounded-full transition-colors" @click.stop="builder.removeBlock(index)">
                                <Trash2 class="w-3 h-3" />
                            </button>
                        </div>

                        <!-- Live Rendering of Block -->
                        <div class="relative">
                            <BlockRenderer :blocks="[block]" class="builder-render" />
                            <!-- Overlay for clicks since blocks might have links -->
                            <div class="absolute inset-0 z-[5]"></div>
                        </div>
                    </div>
                </template>
            </draggable>
            
            <!-- Empty Placeholder -->
            <div v-if="builder.blocks.value.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-muted-foreground gap-6 pointer-events-none p-12 text-center">
                <div class="w-20 h-20 rounded-3xl bg-muted/50 border border-border flex items-center justify-center animate-pulse">
                    <Plus class="w-10 h-10 opacity-20" />
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-foreground">{{ t('features.builder.canvas.empty.title') }}</h3>
                    <p class="text-sm max-w-xs mx-auto text-muted-foreground">{{ t('features.builder.canvas.empty.description') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Global Undo/Redo/History (Coming Soon) -->

        <div class="h-40 w-full shrink-0"></div>
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
import BlockRenderer from '../blocks/BlockRenderer.vue'; // Correct path from builder/canvas/

const builder = inject('builder');
const { t } = useI18n();

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
