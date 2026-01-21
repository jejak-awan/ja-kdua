<script setup>
import { ref, computed } from 'vue';
import { ChevronDown, Plus, Minus } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: 'Toggle Title' },
    content: { type: String, default: '' },
    defaultOpen: { type: Boolean, default: false },
    toggleIcon: { type: String, default: 'chevron' },
    iconPosition: { type: String, default: 'right' },
    iconColor: { type: String, default: '' },
    headerStyle: { type: String, default: 'bordered' },
    headerBackgroundColor: { type: String, default: '' },
    contentBackgroundColor: { type: String, default: '' },
    padding: { type: [String, Object], default: '' } // Base wrapper padding
});

const isOpen = ref(props.defaultOpen);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const containerClasses = computed(() => {
    return [
        'toggle-container w-full overflow-hidden transition-all duration-300',
        props.headerStyle === 'bordered' ? 'border rounded-xl' : '',
        props.headerStyle === 'filled' ? 'bg-muted/30 rounded-xl' : '',
        props.headerStyle === 'minimal' ? 'border-b rounded-none' : ''
    ].filter(Boolean);
});

const headerClasses = computed(() => {
    return [
        'w-full flex items-center gap-4 transition-colors p-4',
        props.iconPosition === 'left' ? 'flex-row' : 'flex-row-reverse justify-between',
        props.headerStyle === 'filled' ? 'hover:bg-muted/50' : 'hover:bg-muted/20'
    ].filter(Boolean);
});

const headerStyles = computed(() => ({
    backgroundColor: props.headerBackgroundColor || (props.headerStyle === 'filled' ? 'hsl(var(--muted)/0.3)' : 'transparent')
}));

const contentStyles = computed(() => ({
    backgroundColor: props.contentBackgroundColor || 'transparent'
}));

const iconStyles = computed(() => ({
    color: props.iconColor || 'currentColor'
}));
</script>

<template>
    <div :class="containerClasses" class="group">
        <!-- Header -->
        <button 
            @click="toggle"
            :class="headerClasses"
            :style="headerStyles"
            type="button"
        >
            <!-- Icon -->
            <div 
                v-if="toggleIcon !== 'none'"
                class="shrink-0 transition-transform duration-300"
                :class="{ 'rotate-180': isOpen && toggleIcon === 'chevron' }"
                :style="iconStyles"
            >
                <template v-if="toggleIcon === 'chevron'">
                    <ChevronDown class="w-5 h-5" />
                </template>
                <template v-else-if="toggleIcon === 'plus'">
                    <Minus v-if="isOpen" class="w-5 h-5" />
                    <Plus v-else class="w-5 h-5" />
                </template>
            </div>

            <span class="font-bold text-base flex-1">{{ title }}</span>
        </button>
        
        <!-- Content -->
        <div 
            class="overflow-hidden transition-all duration-300 ease-in-out"
            :class="isOpen ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0 pointer-events-none'"
        >
            <div 
                class="leading-relaxed prose prose-sm dark:prose-invert max-w-none p-4 pt-2"
                :style="contentStyles"
                v-html="content || 'Toggle content goes here.'"
            ></div>
        </div>
    </div>
</template>

<style scoped>
.toggle-container {
    transition: box-shadow 0.3s ease;
}
.toggle-container:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
</style>
