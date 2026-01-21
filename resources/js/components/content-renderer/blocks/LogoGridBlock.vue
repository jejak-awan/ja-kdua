<script setup>
import { computed } from 'vue';
import { Building } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    items: { type: Array, default: () => [] },
    showTitle: { type: Boolean, default: true },
    title: { type: String, default: '' },
    columns: { type: [Number, String], default: 4 },
    gap: { type: [Number, String], default: 40 },
    logoSize: { type: [Number, String], default: 140 },
    grayscale: { type: Boolean, default: true },
    hoverColor: { type: Boolean, default: true },
    logoOpacity: { type: [Number, String], default: 0.6 }
});

const gridStyles = computed(() => {
    const cols = parseInt(props.columns) || 4;
    return {
        display: 'grid',
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: `${props.gap}px`,
        alignItems: 'center',
        justifyItems: 'center'
    };
});

const logoContainerStyles = computed(() => ({
    width: '100%',
    maxWidth: `${props.logoSize}px`,
    opacity: props.logoOpacity,
    transition: 'all 0.3s ease'
}));
</script>

<template>
    <div class="logo-grid-renderer w-full text-center">
        <!-- Title -->
        <h3 v-if="showTitle && title" class="text-2xl md:text-3xl font-bold mb-12 text-foreground/80">
            {{ title }}
        </h3>

        <!-- Grid -->
        <div :style="gridStyles">
            <div 
                v-for="(item, index) in items" 
                :key="index"
                class="logo-item w-full transition-all duration-300"
                :class="{ 
                    'grayscale group-hover:grayscale-0': grayscale && hoverColor,
                    'grayscale': grayscale && !hoverColor 
                }"
            >
                <a 
                    v-if="item.url" 
                    :href="item.url" 
                    class="logo-link block group"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <div class="logo-container mx-auto" :style="logoContainerStyles" :class="{ 'group-hover:opacity-100 group-hover:scale-110': hoverColor }">
                        <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-auto max-h-20 object-contain" />
                        <div v-else class="w-full aspect-[2/1] bg-muted flex items-center justify-center rounded-lg text-muted-foreground/30">
                            <Building class="w-8 h-8" />
                        </div>
                    </div>
                </a>
                <div v-else class="logo-container mx-auto" :style="logoContainerStyles" :class="{ 'hover:opacity-100 hover:scale-110': hoverColor }">
                    <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-auto max-h-20 object-contain" />
                    <div v-else class="w-full aspect-[2/1] bg-muted flex items-center justify-center rounded-lg text-muted-foreground/30">
                        <Building class="w-8 h-8" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.logo-item {
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
