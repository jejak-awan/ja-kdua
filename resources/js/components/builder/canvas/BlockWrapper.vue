<template>
    <div 
        class="group/block relative border-2 border-transparent hover:border-primary/50 transition-all cursor-pointer"
        :class="{ 'border-primary ring-4 ring-primary/10 z-10': isSelected }"
        @click.stop="onEdit"
        @contextmenu.prevent="onContextMenu"
    >
        <!-- Block Toolbar (Elementor/Divi Style) -->
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 opacity-0 group-hover/block:opacity-100 transition-all z-30 flex items-center gap-0.5 bg-primary text-primary-foreground rounded-full px-1.5 py-1 shadow-xl scale-90 translate-y-1 group-hover/block:translate-y-0">
            <GripVertical class="w-3 h-3 cursor-move drag-handle mx-0.5" />
            <div class="w-px h-3 bg-primary-foreground/20 mx-1"></div>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-primary-foreground/20 rounded-full transition-colors" @click.stop="onEdit" title="Settings">
                <Settings2 class="w-3 h-3" />
            </button>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-primary-foreground/20 rounded-full transition-colors" @click.stop="onDuplicate" :title="t('features.builder.properties.tooltips.duplicate')">
                <Copy class="w-3 h-3" />
            </button>
            <button class="h-6 w-6 flex items-center justify-center hover:bg-primary-foreground/20 rounded-full transition-colors" @click.stop="onDelete" :title="t('features.builder.properties.tooltips.delete')">
                <Trash2 class="w-3 h-3" />
            </button>
        </div>

        <!-- Live Rendering of Block -->
        <div class="relative">
            <BlockRenderer :blocks="[block]" :context="context" class="builder-render" />
            <!-- Overlay for clicks since blocks might have links -->
            <div class="absolute inset-0 z-[5]"></div>
        </div>

        <!-- Context Menu -->
        <ContextMenu 
            v-model="showContextMenu" 
            :x="menuX" 
            :y="menuY" 
            :block="block" 
            :index="index"
            :can-paste="builder?.canPaste?.value ?? false"
            @action="handleMenuAction"
        />
    </div>
</template>

<script setup>
import { computed, inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { GripVertical, Copy, Trash2, Settings2 } from 'lucide-vue-next';
import BlockRenderer from '../blocks/BlockRenderer.vue';
import ContextMenu from './ContextMenu.vue';

const props = defineProps({
    block: { type: Object, required: true },
    index: { type: Number, required: true },
    context: { type: Object, default: () => ({}) },
    isNested: { type: Boolean, default: false } // To handle logic diffs if needed
});

const emit = defineEmits(['edit', 'delete', 'duplicate']);

const builder = inject('builder');
const { t } = useI18n();

// Determine if this block is currently selected
const isSelected = computed(() => {
    if (!builder) return false;
    
    // If nested, we might need a unique ID check instead of index
    if (props.isNested) {
         return builder.activeBlockId.value === props.block.id;
    }
    return builder.editingIndex.value === props.index;
});

const onEdit = () => {
    if (props.isNested) {
        // If nested, use ID based selection? 
        // We need to update builder to support ID-based selection or path-based.
        // For now, let's just set the ID and open properties
        builder.activeBlockId.value = props.block.id;
        builder.activeTab.value = 'content';
        // We might lose 'index' context for "Layers" tab properties in nested mode.
        // But Properties Panel largely works on 'selectedBlock' computed.
        
        // Hack: If we rely on editingIndex, it breaks for nested. 
        // We need to update PropertiesPanel to find block by ID if editingIndex is null?
        // Or we pass a special "path" index?
    } else {
        builder.editingIndex.value = props.index;
        builder.activeTab.value = 'content';
        builder.activeBlockId.value = props.block.id;
    }
    
    emit('edit');
};

const onDelete = () => {
    emit('delete');
};

const onDuplicate = () => {
    emit('duplicate');
};

const onContextMenu = (e) => {
    menuX.value = e.clientX;
    menuY.value = e.clientY;
    showContextMenu.value = true;
};

const handleMenuAction = ({ action }) => {
    switch (action) {
        case 'edit':
            onEdit();
            break;
        case 'duplicate':
            onDuplicate();
            break;
        case 'copy':
            builder.copyBlock(props.index);
            break;
        case 'cut':
            builder.cutBlock(props.index);
            break;
        case 'paste':
            builder.pasteBlock(props.index);
            break;
        case 'delete':
            onDelete();
            break;
        case 'preset':
            builder.activeRightSidebarTab.value = 'presets';
            builder.isRightSidebarOpen.value = true;
            onEdit();
            break;
        case 'layers':
            builder.activeRightSidebarTab.value = 'layers';
            builder.isRightSidebarOpen.value = true;
            break;
    }
};

const showContextMenu = ref(false);
const menuX = ref(0);
const menuY = ref(0);
</script>
