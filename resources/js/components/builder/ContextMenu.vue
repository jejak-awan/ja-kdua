<template>
    <div 
        v-if="visible" 
        class="fixed z-50 bg-popover text-popover-foreground border border-border shadow-md rounded-md min-w-[160px] p-1 animate-in fade-in zoom-in-95 duration-100"
        :style="{ top: y + 'px', left: x + 'px' }"
        @click.stop
        ref="menuRef"
    >
        <div 
            v-for="action in actions" 
            :key="action.label"
            class="flex items-center gap-2 px-2 py-1.5 text-xs rounded-sm cursor-pointer hover:bg-accent hover:text-accent-foreground select-none"
            :class="{ 'text-destructive hover:text-destructive': action.variant === 'destructive' }"
            @click="handleAction(action)"
        >
            <component :is="action.icon" class="w-3.5 h-3.5 opacity-70" />
            <span>{{ action.label }}</span>
            <span v-if="action.shortcut" class="ml-auto text-[10px] text-muted-foreground opacity-70">{{ action.shortcut }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { onClickOutside } from '@vueuse/core'; // Assuming usage or manual implementation

const props = defineProps({
    visible: Boolean,
    x: Number,
    y: Number,
    actions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'action']);
const menuRef = ref(null);

// Close on click outside
const close = () => emit('close');

// Manual click outside if @vueuse/core not available, keeping it simple
const handleClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        close();
    }
};

onMounted(() => {
    window.addEventListener('mousedown', handleClickOutside);
    window.addEventListener('scroll', close, true); // Close on scroll
});

onUnmounted(() => {
    window.removeEventListener('mousedown', handleClickOutside);
    window.removeEventListener('scroll', close, true);
});

const handleAction = (action) => {
    emit('action', action);
    close();
};
</script>
