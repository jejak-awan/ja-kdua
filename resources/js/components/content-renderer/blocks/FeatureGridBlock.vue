<template>
    <section 
        :class="['transition-all duration-500', padding, radius, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width]">
            <h2 v-if="title" class="text-3xl font-bold mb-12 text-center tracking-tight">{{ title }}</h2>
            <div class="grid gap-8 md:grid-cols-3">
                <div 
                    v-for="(item, index) in items" 
                    :key="index"
                    class="p-8 rounded-2xl border bg-card/50 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:border-primary/20 hover:-translate-y-1 group"
                >
                    <div 
                        v-if="item.icon" 
                        class="w-14 h-14 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center mb-6 text-primary group-hover:bg-primary group-hover:text-primary-foreground transition-colors"
                    >
                        <component 
                            :is="getIcon(item.icon)" 
                            class="w-7 h-7" 
                        />
                    </div>
                    <h3 class="font-bold text-xl mb-3">{{ item.title }}</h3>
                    <p class="opacity-80 leading-relaxed">{{ item.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import * as LucideIcons from 'lucide-vue-next';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
    title: String,
    items: { type: Array, default: () => [] },
    padding: { type: String, default: 'py-16' },
    width: { type: String, default: 'max-w-7xl' },
    bgColor: String,
    radius: { type: String, default: 'rounded-none' },
    animation: { type: String, default: '' }
});

const getIcon = (iconName) => {
    if (!iconName) return null;
    // Handle kebab-case to PascalCase (e.g. arrow-right -> ArrowRight)
    const pascalName = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
        
    return LucideIcons[pascalName] || LucideIcons[iconName] || LucideIcons.Star;
};
</script>
