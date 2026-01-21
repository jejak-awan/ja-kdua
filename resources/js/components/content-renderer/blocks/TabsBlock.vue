<script setup>
import { ref, computed } from 'vue';
import * as Icons from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    items: {
        type: Array,
        default: () => []
    },
    tabPosition: { type: String, default: 'top' },
    tabAlignment: { type: String, default: 'left' },
    tabBackgroundColor: { type: String, default: '' },
    tabActiveBackgroundColor: { type: String, default: '' },
    contentBackgroundColor: { type: String, default: '' },
    padding: { type: [String, Object], default: '' }
});

const activeIndex = ref(0);

const getIcon = (name) => {
    if (!name) return null;
    const normalized = name.charAt(0).toUpperCase() + name.slice(1);
    return Icons[normalized] || null;
};

const tabsContainerClass = computed(() => {
    const isLeft = props.tabPosition === 'left';
    return [
        'tabs-renderer w-full',
        isLeft ? 'flex items-start' : 'flex flex-col'
    ].filter(Boolean);
});

const listClass = computed(() => {
    const isLeft = props.tabPosition === 'left';
    const align = props.tabAlignment;
    
    return [
        'tabs-list flex shrink-0',
        isLeft ? 'flex-col w-48 border-r' : 'w-full border-b',
        !isLeft && align === 'center' ? 'justify-center' : '',
        !isLeft && align === 'right' ? 'justify-end' : '',
        props.tabPosition === 'bottom' ? 'order-last border-t border-b-0' : ''
    ].filter(Boolean);
});

const getTabButtonClass = (index) => {
    const isActive = activeIndex.value === index;
    const isLeft = props.tabPosition === 'left';
    const isBottom = props.tabPosition === 'bottom';
    
    return [
        'tabs-trigger flex items-center gap-2 px-6 py-3 font-semibold text-sm transition-all duration-300 relative',
        isActive ? 'text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted/30',
        isActive && !isLeft && !isBottom ? 'after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-primary' : '',
        isActive && isLeft ? 'after:absolute after:right-0 after:top-0 after:bottom-0 after:w-0.5 after:bg-primary' : '',
        isActive && isBottom ? 'after:absolute after:top-0 after:left-0 after:right-0 after:h-0.5 after:bg-primary' : '',
        props.tabAlignment === 'fill' ? 'flex-1 justify-center' : ''
    ].filter(Boolean);
};
</script>

<template>
    <div :class="tabsContainerClass">
        <!-- Tabs Header -->
        <div :class="listClass" :style="{ backgroundColor: tabBackgroundColor || 'transparent' }">
            <button 
                v-for="(item, index) in items" 
                :key="index"
                @click="activeIndex = index"
                :class="getTabButtonClass(index)"
                :style="index === activeIndex ? { backgroundColor: tabActiveBackgroundColor } : {}"
                type="button"
            >
                <component v-if="item.icon" :is="getIcon(item.icon)" class="w-4 h-4 shrink-0" />
                <span class="truncate">{{ item.title || 'Tab ' + (index + 1) }}</span>
            </button>
        </div>

        <!-- Content -->
        <div 
            class="tabs-content flex-1 p-6" 
            :style="{ backgroundColor: contentBackgroundColor || 'transparent' }"
        >
            <transition name="tab-fade" mode="out-in">
                <div 
                    v-for="(item, index) in items" 
                    v-show="index === activeIndex"
                    :key="index"
                    class="tab-pane prose prose-sm max-w-none dark:prose-invert"
                    v-html="item.content || ''"
                ></div>
            </transition>
        </div>
    </div>
</template>

<style scoped>
.tab-fade-enter-active,
.tab-fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.tab-fade-enter-from {
    opacity: 0;
    transform: translateY(4px);
}
.tab-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
