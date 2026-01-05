<template>
    <Teleport to="body">
        <div 
            v-if="visible"
            ref="pickerRef"
            class="fixed z-[10000] animate-in zoom-in-95 duration-200"
            :style="pickerStyle"
        >
            <!-- Modal -->
            <div class="bg-popover border border-border rounded-xl shadow-2xl w-full max-w-lg max-h-[70vh] overflow-hidden flex flex-col">
                <!-- Header (Drag Handle) -->
                <div 
                    class="flex items-center justify-between p-4 border-b border-border cursor-move select-none"
                    @mousedown="startDrag"
                >
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm">Add Block</h3>
                        <div class="text-[10px] text-muted-foreground bg-muted px-1.5 py-0.5 rounded">Draggable</div>
                    </div>
                    
                    <div class="flex items-center gap-2" @mousedown.stop>
                        <div class="relative flex-1">
                            <SearchIcon class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input 
                                v-model="searchQuery" 
                                placeholder="Search blocks..." 
                                class="pl-9 h-9 text-xs w-48"
                                ref="searchInput"
                            />
                        </div>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="close">
                            <X class="w-4 h-4" />
                        </Button>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-4 overflow-y-auto max-h-[50vh] custom-scrollbar">
                    <!-- Quick Structure -->
                    <div class="mb-4">
                        <h4 class="text-[10px] font-bold text-muted-foreground mb-2 uppercase">Structure</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                @click="addBlock('section')"
                                class="p-3 border border-border rounded-lg hover:bg-accent hover:border-primary/50 transition-all flex items-center gap-3 group"
                            >
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                    <LayoutPanelTop class="w-5 h-5 text-primary" />
                                </div>
                                <div class="text-left">
                                    <span class="text-sm font-medium block">Section</span>
                                    <span class="text-[10px] text-muted-foreground">Full-width container</span>
                                </div>
                            </button>
                            <button 
                                @click="addBlock('columns')"
                                class="p-3 border border-border rounded-lg hover:bg-accent hover:border-primary/50 transition-all flex items-center gap-3 group"
                            >
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                    <Columns3 class="w-5 h-5 text-primary" />
                                </div>
                                <div class="text-left">
                                    <span class="text-sm font-medium block">Columns</span>
                                    <span class="text-[10px] text-muted-foreground">Multi-column layout</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    
                    <!-- All Blocks Grid -->
                    <div>
                        <h4 class="text-[10px] font-bold text-muted-foreground mb-2 uppercase">All Blocks</h4>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="block in filteredBlocks"
                                :key="block.name"
                                @click="addBlock(block.name)"
                                class="p-2 border border-border rounded-lg hover:bg-accent hover:border-primary/50 transition-all flex flex-col items-center gap-2 group"
                            >
                                <div class="w-8 h-8 rounded-md bg-muted flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                                    <component :is="block.icon" class="w-4 h-4 text-muted-foreground group-hover:text-primary" />
                                </div>
                                <span class="text-[10px] font-medium text-center truncate w-full">{{ block.label }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, inject, nextTick, watch, onMounted, onUnmounted } from 'vue';
import { Search as SearchIcon, X, LayoutPanelTop, Columns3 } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Input from '@/components/ui/input.vue';
import { blockRegistry } from '../BlockRegistry';
import { generateUUID } from '../utils';

const props = defineProps({
    visible: { type: Boolean, default: false },
    insertIndex: { type: Number, default: null }, // Where to insert
    targetBlocks: { type: Array, default: null } // For nested blocks (Section/Columns)
});

const emit = defineEmits(['close', 'add']);

const builder = inject('builder');
const searchQuery = ref('');
const searchInput = ref(null);
const pickerRef = ref(null);

// Positioning State
const position = ref({ x: 0, y: 0 });
const isDragging = ref(false);
const dragOffset = { x: 0, y: 0 };

const pickerStyle = computed(() => ({
    left: `${position.value.x}px`,
    top: `${position.value.y}px`
}));

// Initialize centered position
const centerPicker = () => {
    const width = 512; // max-w-lg
    const height = 400; // estimated
    position.value = {
        x: (window.innerWidth - width) / 2,
        y: Math.max(100, (window.innerHeight - height) / 2)
    };
};

// Dragging Logic
const startDrag = (e) => {
    isDragging.value = true;
    dragOffset.x = e.clientX - position.value.x;
    dragOffset.y = e.clientY - position.value.y;
    
    window.addEventListener('mousemove', handleDrag);
    window.addEventListener('mouseup', stopDrag);
};

const handleDrag = (e) => {
    if (!isDragging.value) return;
    position.value = {
        x: e.clientX - dragOffset.x,
        y: e.clientY - dragOffset.y
    };
};

const stopDrag = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', handleDrag);
    window.removeEventListener('mouseup', stopDrag);
};

// Focus search on open
watch(() => props.visible, (val) => {
    if (val) {
        centerPicker();
        nextTick(() => {
            const inputEl = searchInput.value?.$el || searchInput.value;
            inputEl?.focus?.();
        });
    }
});

onMounted(() => {
    if (props.visible) centerPicker();
});

onUnmounted(() => {
    window.removeEventListener('mousemove', handleDrag);
    window.removeEventListener('mouseup', stopDrag);
});

const allBlocks = computed(() => {
    return blockRegistry.getAll().filter(b => !['section'].includes(b.name)); // Hide section from general list
});

const filteredBlocks = computed(() => {
    if (!searchQuery.value) return allBlocks.value;
    return allBlocks.value.filter(b => 
        b.label.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const addBlock = (type) => {
    const definition = blockRegistry.get(type);
    if (!definition) return;

    const newBlock = {
        id: generateUUID(),
        type: definition.name,
        settings: JSON.parse(JSON.stringify(definition.defaultSettings))
    };

    emit('add', newBlock);
    close();
};

const close = () => {
    searchQuery.value = '';
    emit('close');
};
</script>
