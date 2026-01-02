<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <h2 v-if="title" class="text-4xl font-extrabold text-center mb-16 tracking-tight">{{ title }}</h2>
            
            <div class="grid gap-8 md:grid-cols-3">
                <div 
                    v-for="(plan, index) in items" 
                    :key="index"
                    class="relative p-8 rounded-3xl border bg-card/50 backdrop-blur-sm shadow-xl flex flex-col transition-all duration-300 hover:shadow-primary/10 hover:border-primary/50 group"
                    :class="{ 'border-primary ring-2 ring-primary/20 scale-105 z-10': index === 1 }"
                >
                    <div v-if="index === 1" class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary text-primary-foreground text-[10px] font-bold px-3 py-1 rounded-full">
                        Most Popular
                    </div>
                    
                    <div class="mb-8">
                        <h3 class="text-xl font-bold mb-2">{{ plan.name }}</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold">{{ plan.price }}</span>
                            <span class="text-muted-foreground text-sm">/month</span>
                        </div>
                    </div>

                    <ul class="space-y-4 mb-10 flex-1">
                        <li v-for="(feature, fIndex) in plan.features" :key="fIndex" class="flex items-start gap-3 text-sm">
                            <svg class="w-5 h-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ feature }}</span>
                        </li>
                    </ul>

                    <button class="w-full py-4 rounded-xl font-bold transition-all duration-300 transform active:scale-95 shadow-lg shadow-primary/20" :class="index === 1 ? 'bg-primary text-primary-foreground hover:opacity-90' : 'bg-muted hover:bg-muted/80 text-foreground'">
                        {{ plan.buttonText }}
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    title: String,
    items: { type: Array, default: () => [] },
    width: { type: String, default: 'max-w-6xl' },
    padding: { type: String, default: 'py-20' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});
</script>
