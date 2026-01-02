<template>
    <div 
        v-if="path.length > 0" 
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-1.5 px-3 py-1.5 bg-background/80 backdrop-blur-md border border-border rounded-full shadow-lg animate-in slide-in-from-bottom-4 duration-300"
    >
        <template v-for="(item, index) in path" :key="item.id">
            <button 
                class="flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold transition-all hover:bg-primary/10 hover:text-primary whitespace-nowrap"
                :class="index === path.length - 1 ? 'text-primary bg-primary/5' : 'text-muted-foreground'"
                @click="selectBlock(item.id)"
            >
                <component :is="getBlockIcon(item.type)" class="w-3 h-3" />
                {{ item.label }}
            </button>
            <ChevronRight v-if="index < path.length - 1" class="w-3 h-3 text-muted-foreground/30" />
        </template>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import { ChevronRight, Box } from 'lucide-vue-next';
import { blockRegistry } from '../BlockRegistry';

const builder = inject('builder');

const path = computed(() => {
    return builder.getBlockPath(builder.activeBlockId.value);
});

const getBlockIcon = (type) => {
    const def = blockRegistry.get(type);
    return def ? def.icon : Box;
};

const selectBlock = (id) => {
    builder.activeBlockId.value = id;
    builder.activeTab.value = 'content';
    // If it's a top-level block, we might need to set editingIndex too for legacy support
    const index = builder.blocks.value.findIndex(b => b.id === id);
    if (index !== -1) {
        builder.editingIndex.value = index;
    } else {
        builder.editingIndex.value = null;
    }
};
</script>
