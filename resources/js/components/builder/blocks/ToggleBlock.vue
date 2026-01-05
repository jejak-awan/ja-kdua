<script setup>
import { ref, computed } from 'vue';
import { ChevronDown, Plus, Minus } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    content: { type: String, default: '' },
    open_by_default: { type: Boolean, default: false },
    icon_style: { type: String, default: 'chevron' },
    style: { type: String, default: 'bordered' },
    padding: { type: String, default: 'py-4' }
});

const isOpen = ref(props.open_by_default);

const containerClasses = computed(() => {
    return ['transition-all duration-300', props.padding].filter(Boolean);
});

const styleClasses = computed(() => {
    const styles = {
        bordered: 'border rounded-xl',
        filled: 'bg-muted rounded-xl',
        minimal: 'border-b'
    };
    return styles[props.style] || styles.bordered;
});

const toggle = () => {
    isOpen.value = !isOpen.value;
};
</script>

<template>
    <div :class="containerClasses">
        <div :class="['overflow-hidden transition-all', styleClasses]">
            <!-- Header -->
            <button 
                @click="toggle"
                :class="[
                    'w-full flex items-center justify-between gap-4 text-left transition-colors',
                    style === 'filled' ? 'p-4 hover:bg-muted/80' : 'p-4 hover:bg-muted/50'
                ]"
            >
                <span class="font-semibold">{{ title || 'Toggle Title' }}</span>
                
                <!-- Icon -->
                <div 
                    class="shrink-0 transition-transform duration-300"
                    :class="{ 'rotate-180': isOpen && icon_style === 'chevron' }"
                >
                    <template v-if="icon_style === 'chevron'">
                        <ChevronDown class="w-5 h-5 text-muted-foreground" />
                    </template>
                    <template v-else>
                        <Minus v-if="isOpen" class="w-5 h-5 text-muted-foreground" />
                        <Plus v-else class="w-5 h-5 text-muted-foreground" />
                    </template>
                </div>
            </button>
            
            <!-- Content -->
            <div 
                class="overflow-hidden transition-all duration-300"
                :class="isOpen ? 'max-h-[500px] opacity-100' : 'max-h-0 opacity-0'"
            >
                <div 
                    :class="[
                        'opacity-80 leading-relaxed',
                        style === 'minimal' ? 'py-4' : 'px-4 pb-4'
                    ]"
                >
                    {{ content || 'Toggle content goes here. Click the header to expand or collapse.' }}
                </div>
            </div>
        </div>
    </div>
</template>
