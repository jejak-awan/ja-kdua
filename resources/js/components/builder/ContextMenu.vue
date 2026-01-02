<template>
    <Teleport to="body">
        <div 
            v-if="visible" 
            class="fixed z-[100] bg-popover text-popover-foreground border border-border shadow-xl rounded-lg min-w-[180px] py-1.5 animate-in fade-in zoom-in-95 duration-100"
            :style="{ top: y + 'px', left: x + 'px' }"
            @click.stop
            ref="menuRef"
        >
            <template v-for="(action, index) in actions" :key="action.label || 'sep-' + index">
                <!-- Separator -->
                <div v-if="action.separator" class="h-px bg-border my-1 mx-2"></div>
                
                <!-- Action Item -->
                <div 
                    v-else
                    class="flex items-center gap-3 px-3 py-2 text-sm cursor-pointer select-none mx-1 rounded-md transition-colors"
                    :class="[
                        action.disabled 
                            ? 'opacity-40 cursor-not-allowed' 
                            : 'hover:bg-accent hover:text-accent-foreground',
                        action.variant === 'destructive' && !action.disabled
                            ? 'text-destructive hover:bg-destructive/10 hover:text-destructive' 
                            : ''
                    ]"
                    @click="!action.disabled && handleAction(action)"
                >
                    <component :is="action.icon" class="w-4 h-4 opacity-70" />
                    <span class="flex-1">{{ action.label }}</span>
                    <span v-if="action.shortcut" class="text-[10px] text-muted-foreground/70 font-mono">{{ action.shortcut }}</span>
                </div>
            </template>
        </div>
    </Teleport>
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
