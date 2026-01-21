<script setup>
import { ref, computed } from 'vue';
import { ChevronDown, Plus, Minus } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    items: { type: Array, default: () => [] },
    layout: { type: String, default: 'accordion' },
    allowMultiple: { type: Boolean, default: false },
    // Styling
    accentColor: { type: String, default: '' },
    itemBorderColor: { type: String, default: '#e2e8f0' },
    // Animation
    animation: { type: String, default: '' },
    // Base wrapper padding (handled by ModuleWrapper in builder usually, but good for direct use)
    padding: { type: [String, Object], default: '' }
});

const openIndices = ref([]);

const toggleItem = (index) => {
    if (props.layout === 'list') return;

    if (props.allowMultiple) {
        if (openIndices.value.includes(index)) {
            openIndices.value = openIndices.value.filter(i => i !== index);
        } else {
            openIndices.value.push(index);
        }
    } else {
        openIndices.value = openIndices.value.includes(index) ? [] : [index];
    }
};

const isOpen = (index) => {
    if (props.layout === 'list') return true;
    return openIndices.value.includes(index);
};
</script>

<template>
    <div class="faq-renderer w-full max-w-4xl mx-auto flex flex-col gap-4">
        <div 
            v-for="(item, index) in items" 
            :key="index"
            class="faq-item border rounded-2xl overflow-hidden bg-card transition-all duration-300"
            :class="{ 'shadow-sm ring-1 ring-primary/5': isOpen(index) }"
            :style="{ borderColor: itemBorderColor }"
        >
            <button 
                class="faq-question w-full flex items-center justify-between p-5 text-left font-bold transition-colors hover:bg-muted/30"
                :class="{'cursor-default': layout === 'list'}"
                @click="toggleItem(index)"
                type="button"
            >
                <span class="text-lg md:text-xl">{{ item.question }}</span>
                <span 
                    v-if="layout === 'accordion'"
                    class="faq-icon shrink-0 transition-transform duration-300"
                    :class="{'rotate-180': isOpen(index)}"
                    :style="{ color: accentColor || 'hsl(var(--primary))' }"
                >
                    <ChevronDown class="w-6 h-6" />
                </span>
            </button>
            
            <div 
                class="overflow-hidden transition-all duration-300 ease-in-out"
                :class="isOpen(index) ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0 pointer-events-none'"
            >
                <div 
                    class="faq-answer px-5 pb-5 text-muted-foreground leading-relaxed prose prose-sm dark:prose-invert max-w-none"
                    v-html="item.answer"
                ></div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.faq-item:hover button {
    background-color: hsl(var(--muted) / 0.1);
}
</style>
