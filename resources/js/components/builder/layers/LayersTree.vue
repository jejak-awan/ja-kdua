<template>
    <draggable
        :list="blocks"
        group="layers"
        item-key="id"
        handle=".drag-handle"
        class="space-y-1 min-h-[10px]"
        :animation="200"
        ghost-class="opacity-50"
    >
        <template #item="{ element: block, index }">
            <div class="space-y-1">
                <!-- Block Row -->
                <div 
                    class="group flex items-center gap-2 p-2 rounded-md border text-sm transition-all cursor-pointer hover:border-primary/50 relative"
                    :class="[
                        builder.activeBlockId.value === block.id ? 'bg-primary/5 border-primary text-primary' : 'bg-sidebar-accent/20 border-transparent hover:bg-sidebar-accent text-sidebar-foreground',
                        depth > 0 ? 'ml-4' : ''
                    ]"
                    @click.stop="selectBlock(block)"
                >
                    <!-- Drag Handle -->
                    <GripVertical class="w-4 h-4 text-muted-foreground opacity-0 group-hover:opacity-50 cursor-move drag-handle shrink-0" />
                    
                    <!-- Icon -->
                    <component :is="builder.getBlockComponent(block.type)?.icon" class="w-3.5 h-3.5 shrink-0 opacity-70" />
                    
                    <!-- Label -->
                    <span class="truncate font-medium flex-1 text-xs select-none">{{ builder.getBlockLabel(block.type) }}</span>
                    
                    <!-- Active Indicator -->
                    <span v-if="builder.activeBlockId.value === block.id" class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>

                    <!-- Column Indicator (if container) -->
                    <div v-if="block.type === 'columns'" class="absolute right-2 top-1/2 -translate-y-1/2 opacity-20 group-hover:opacity-100">
                        <Columns3 class="w-3 h-3" />
                    </div>
                </div>

                <!-- Recursion for Columns (Container) -->
                <div v-if="block.type === 'columns' && block.settings.columns" class="ml-4 pl-2 border-l border-border/50">
                    <div v-for="(col, colIndex) in block.settings.columns" :key="colIndex" class="mt-1">
                         <div class="px-2 py-1 text-[9px] font-bold text-muted-foreground flex items-center gap-1">
                             <span class="w-1.5 h-1.5 rounded-sm bg-muted-foreground/30"></span>
                             Column {{ colIndex + 1 }}
                         </div>
                         <LayersTree 
                            :blocks="col.blocks" 
                            :depth="depth + 1"
                         />
                    </div>
                </div>
            </div>
        </template>
    </draggable>
</template>

<script setup>
import { inject } from 'vue';
import draggable from 'vuedraggable';
import { GripVertical, Columns3 } from 'lucide-vue-next';

const props = defineProps({
    blocks: {
        type: Array,
        required: true
    },
    depth: {
        type: Number,
        default: 0
    }
});

const builder = inject('builder');

const selectBlock = (block) => {
    builder.activeBlockId.value = block.id;
    // We also need to set the editingIndex if it's a root block, but for nested blocks we rely on activeBlockId
    // The main Builder properties panel now prefers activeBlockId (updated in Step 2983 line 396)
};
</script>
