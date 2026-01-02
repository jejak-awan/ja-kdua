<template>
    <div 
        v-if="builder.showLayersPanel.value"
        class="fixed top-20 right-[340px] z-40 w-72 bg-sidebar border border-border shadow-2xl rounded-xl flex flex-col max-h-[70vh] animate-in slide-in-from-right-4 duration-300"
    >
        <!-- Panel Header -->
        <div class="h-10 shrink-0 flex items-center justify-between px-3 border-b border-border bg-sidebar-accent/10">
            <div class="flex items-center gap-2">
                <Layers class="w-4 h-4 text-primary" />
                <span class="text-xs font-bold uppercase tracking-wider">Layers</span>
            </div>
            <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon" class="h-6 w-6" @click="builder.showLayersPanel.value = false">
                    <X class="w-3.5 h-3.5" />
                </Button>
            </div>
        </div>

        <!-- Tree Content -->
        <div class="flex-1 overflow-y-auto p-2 custom-scrollbar">
            <LayersTree :blocks="builder.blocks.value" />
        </div>
        
        <!-- Footer / Stats -->
        <div class="h-8 shrink-0 px-3 flex items-center justify-between border-t border-border bg-muted/30">
            <span class="text-[9px] text-muted-foreground uppercase font-medium">{{ builder.blocks.value.length }} Root Elements</span>
        </div>
    </div>
</template>

<script setup>
import { inject } from 'vue';
import { Layers, X } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import LayersTree from './LayersTree.vue';

const builder = inject('builder');
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: hsl(var(--muted-foreground) / 0.15);
    border-radius: 10px;
}
</style>
