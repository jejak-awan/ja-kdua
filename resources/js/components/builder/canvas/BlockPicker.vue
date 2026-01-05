<template>
    <Teleport to="body">
        <div 
            v-if="visible"
            class="fixed inset-0 z-[10000] flex items-center justify-center"
            @click.self="close"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" @click="close"></div>
            
            <!-- Modal -->
            <div class="relative bg-popover border border-border rounded-xl shadow-2xl w-full max-w-lg max-h-[70vh] overflow-hidden animate-in zoom-in-95 duration-200">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-border">
                    <h3 class="font-bold text-sm">Add Block</h3>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
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
                                    <span class="text-sm font-medium block">Row</span>
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
import { ref, computed, inject, nextTick, watch } from 'vue';
import { Search, X, LayoutPanelTop, Columns3 } from 'lucide-vue-next';
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

// Focus search on open
watch(() => props.visible, (val) => {
    if (val) {
        nextTick(() => {
            // Safe Focus Fix
            const inputEl = searchInput.value?.$el || searchInput.value;
            inputEl?.focus?.();
        });
    }
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
