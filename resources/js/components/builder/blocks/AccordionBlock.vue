<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <h2 v-if="title" class="text-3xl font-bold text-center mb-12 tracking-tight">{{ title }}</h2>
            
            <div class="max-w-3xl mx-auto space-y-4">
                <div 
                    v-for="(faq, index) in items" 
                    :key="index"
                    class="border rounded-2xl overflow-hidden bg-card/30 backdrop-blur-sm transition-all duration-300"
                    :class="{ 'ring-1 ring-primary/20 border-primary/20 bg-primary/5': openIndex === index }"
                >
                    <button 
                        @click="openIndex = openIndex === index ? null : index"
                        class="w-full flex items-center justify-between p-6 text-left hover:bg-muted/30 transition-colors"
                    >
                        <span class="font-bold text-lg">{{ faq.question }}</span>
                        <svg 
                            class="w-5 h-5 transition-transform duration-300" 
                            :class="{ 'rotate-180': openIndex === index }"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div 
                        v-show="openIndex === index"
                        class="p-6 pt-0 text-muted-foreground leading-relaxed animate-in slide-in-from-top-2 duration-300"
                    >
                        {{ faq.answer }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: String,
    items: { type: Array, default: () => [] },
    width: { type: String, default: 'max-w-5xl' },
    padding: { type: String, default: 'py-20' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});

const openIndex = ref(null);
</script>
