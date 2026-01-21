<template>
  <BaseBlock :module="module" :settings="settings">
    <div class="feature-grid-container grid gap-8" :style="gridStyles">
        <div 
            v-for="(item, index) in items(settings)" 
            :key="index"
            class="feature-item p-8 rounded-2xl border bg-card/50 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:border-primary/20 hover:-translate-y-1 group"
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
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const settings = computed(() => props.module.settings || {})

const items = (settings) => getVal(settings, 'items') || []

const gridStyles = computed(() => {
    const cols = getVal(settings.value, 'columns') || 3
    return {
        gridTemplateColumns: `repeat(${cols}, 1fr)`
    }
})

const getIcon = (iconName) => {
    if (!iconName) return null
    const pascalName = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('')
        return LucideIcons[pascalName] || LucideIcons[iconName] || LucideIcons.Star
}
</script>
