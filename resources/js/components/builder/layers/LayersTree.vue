<template>
    <div class="relative">
        <draggable
            :list="blocks"
            group="layers"
            item-key="id"
            handle=".drag-handle"
            class="space-y-0.5 min-h-[1px]"
            :animation="200"
            ghost-class="opacity-50"
            :class="{ 'pl-4': depth > 0 }" 
        >
            <template #item="{ element: block, index }">
                <div class="relative">
                    <!-- Tree Line Guide (Vertical) -->
                    <div v-if="depth > 0" class="absolute -left-2.5 top-0 bottom-0 w-px bg-border/50"></div>
                    <!-- Horizontal Connector -->
                    <div v-if="depth > 0" class="absolute -left-2.5 top-3.5 w-2.5 h-px bg-border/50"></div>

                    <!-- Block Row -->
                    <div 
                        class="group flex items-center gap-1.5 p-1.5 rounded-md border border-transparent text-xs transition-all cursor-pointer hover:bg-sidebar-accent relative z-10"
                        :class="[
                            builder.activeBlockId.value === block.id ? 'bg-primary/10 border-primary/20 text-primary' : 'text-sidebar-foreground',
                        ]"
                        @click.stop="selectBlock(block)"
                    >
                        <!-- Expand/Collapse Toggle -->
                        <button 
                            v-if="hasChildren(block)"
                            @click.stop="toggleExpand(block.id)"
                            class="w-4 h-4 flex items-center justify-center rounded hover:bg-black/5 text-muted-foreground transition-transform duration-200"
                            :class="{ 'rotate-90': isExpanded(block.id) }"
                        >
                            <ChevronRight class="w-3 h-3" />
                        </button>
                        <span v-else class="w-4"></span>

                        <!-- Block Icon -->
                        <component 
                            :is="getBlockIcon(block.type)" 
                            class="w-3.5 h-3.5 shrink-0 opacity-70"
                            :class="getBlockColorClass(block.type)" 
                        />
                        
                        <!-- Label -->
                        <span class="truncate font-medium flex-1 select-none">{{ builder.getBlockLabel(block.type) }}</span>
                        
                        <!-- Drag Handle (Right side hover) -->
                        <GripVertical class="w-3.5 h-3.5 text-muted-foreground opacity-0 group-hover:opacity-50 cursor-move drag-handle shrink-0" />
                        
                        <!-- Active Indicator -->
                        <span v-if="builder.activeBlockId.value === block.id" class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                    </div>

                    <!-- Recursion (Children) -->
                    <div 
                        v-if="hasChildren(block)" 
                        v-show="isExpanded(block.id)"
                        class="relative"
                    >
                        <!-- Section Children Logic -->
                        <template v-if="block.type === 'section' && Array.isArray(block.settings.blocks)">
                            <LayersTree 
                                :blocks="block.settings.blocks" 
                                :depth="depth + 1"
                            />
                        </template>

                        <!-- Container Children Logic -->
                        <template v-if="block.type === 'columns' && Array.isArray(block.settings.columns)">
                            <div v-for="(col, colIndex) in block.settings.columns" :key="colIndex">
                                <!-- Virtual Column Node -->
                                <div class="relative pl-4 mt-0.5">
                                    <div class="absolute -left-2.5 top-0 bottom-0 w-px bg-border/50"></div> <!-- Vertical Guide -->
                                    <div class="absolute -left-2.5 top-3 w-2.5 h-px bg-border/50"></div> <!-- Conn -->
                                    
                                    <!-- Column Header -->
                                    <div class="flex items-center gap-1.5 p-1 text-[10px] uppercase font-bold text-muted-foreground/70 select-none">
                                        <Columns3 class="w-3 h-3" />
                                        <span>Col {{ colIndex + 1 }}</span>
                                    </div>
                                    
                                    <!-- Children of Column -->
                                    <LayersTree 
                                        :blocks="col.blocks" 
                                        :depth="depth + 1"
                                    />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </draggable>
    </div>
</template>

<script setup>
import { inject, ref } from 'vue';
import draggable from 'vuedraggable';
import { 
    GripVertical, 
    ChevronRight, 
    Box, 
    Columns3, 
    Type, 
    Image as ImageIcon,
    Layout
} from 'lucide-vue-next';

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

// Basic Expansion State (Local to this list instance)
const expandedState = ref({});

const isExpanded = (id) => {
    // Default to Expanded for now? Or Collapsed?
    // Let's default to Expanded for better visibility initially
    return expandedState.value[id] !== false;
};

const toggleExpand = (id) => {
    expandedState.value[id] = !isExpanded(id);
};

const hasChildren = (block) => {
    if (block.type === 'section') return Array.isArray(block.settings?.blocks) && block.settings.blocks.length > 0;
    if (block.type === 'columns') return Array.isArray(block.settings?.columns) && block.settings.columns.length > 0;
    return false;
};

const selectBlock = (block) => {
    builder.activeBlockId.value = block.id;
};

const getBlockIcon = (type) => {
    const map = {
        'columns': Layout,
        'image': ImageIcon,
        'heading': Type,
        'text': Type,
        // Add others...
    };
    // Fallback to registry icon or Box
    return builder.getBlockComponent(type)?.icon || Box;
};

const getBlockColorClass = (type) => {
    // Green for containers/layout? Blue for content?
    if (type === 'columns' || type === 'section') return 'text-green-500';
    if (type === 'image') return 'text-purple-500';
    if (type === 'heading' || type === 'text') return 'text-blue-500';
    return 'text-muted-foreground';
};
</script>
