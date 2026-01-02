<template>
    <div 
        v-if="modelValue" 
        ref="menuRef"
        class="fixed z-[9999] bg-popover border border-border rounded-lg shadow-2xl p-1 min-w-[180px] animate-in fade-in zoom-in-95 duration-100"
        :style="{ top: y + 'px', left: x + 'px' }"
        @click.stop
    >
        <div class="space-y-0.5">
            <button 
                v-for="item in menuItems" 
                :key="item.label"
                class="w-full flex items-center justify-between gap-3 px-2.5 py-1.5 text-xs font-medium rounded-md hover:bg-accent hover:text-accent-foreground transition-colors group"
                :class="{ 'opacity-50 cursor-not-allowed': item.disabled }"
                @click="handleAction(item.action)"
                :disabled="item.disabled"
            >
                <div class="flex items-center gap-2">
                    <component :is="item.icon" class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100" />
                    <span>{{ item.label }}</span>
                </div>
                <span v-if="item.shortcut" class="text-[10px] text-muted-foreground font-mono">{{ item.shortcut }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { 
    Settings2, 
    Copy, 
    Clipboard, 
    Trash2, 
    Sparkles, 
    Layers,
    Scissors,
    FilePlus2
} from 'lucide-vue-next';

const props = defineProps({
    modelValue: Boolean,
    x: Number,
    y: Number,
    block: Object,
    index: Number,
    canPaste: Boolean
});

const emit = defineEmits(['update:modelValue', 'action']);

const menuRef = ref(null);

const menuItems = computed(() => [
    { label: 'Edit Settings', icon: Settings2, action: 'edit', shortcut: 'Click' },
    { label: 'Copy Block', icon: Copy, action: 'copy', shortcut: 'Ctrl+C' },
    { label: 'Cut Block', icon: Scissors, action: 'cut', shortcut: 'Ctrl+X' },
    { label: 'Paste After', icon: Clipboard, action: 'paste', shortcut: 'Ctrl+V', disabled: !props.canPaste },
    { label: 'Duplicate', icon: FilePlus2, action: 'duplicate', shortcut: 'Ctrl+D' },
    { label: 'Save as Preset', icon: Sparkles, action: 'preset', shortcut: null },
    { label: 'View in Layers', icon: Layers, action: 'layers', shortcut: null },
    { type: 'separator' },
    { label: 'Delete', icon: Trash2, action: 'delete', shortcut: 'Del', variant: 'destructive' }
].filter(i => i.type !== 'separator' || true)); // Simple filter for now

const handleAction = (action) => {
    emit('action', { action, block: props.block, index: props.index });
    emit('update:modelValue', false);
};

// Close on outside click
const handleClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        emit('update:modelValue', false);
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
});
</script>
