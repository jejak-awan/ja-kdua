<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
        <div class="feature-grid-container grid" :style="gridStyles(settings, blockDevice)">
            <div 
                v-for="(item, index) in items(settings)" 
                :key="index"
                class="feature-item transition-all duration-500 hover:-translate-y-3 group p-8 border border-transparent"
                :class="[
                    getVal(settings, 'cardShadow') || 'shadow-none',
                    getVal(settings, 'cardRadius') || 'rounded-2xl',
                    getVal(settings, 'textAlign') || 'text-left'
                ]"
                :style="cardStyles(settings)"
            >
                <div 
                    v-if="item.icon" 
                    class="icon-wrapper flex items-center justify-center mb-6 transition-colors"
                    :class="[getVal(settings, 'iconSize') || 'w-14 h-14', 'rounded-xl']"
                    :style="iconWrapperStyles(settings)"
                >
                    <component 
                        :is="getIcon(item.icon)" 
                        :class="[iconSizeClass(settings)]"
                        :style="{ color: getVal(settings, 'iconColor') || '#2563eb' }"
                    />
                </div>
                <h3 class="font-bold text-xl mb-3">{{ item.title }}</h3>
                <p class="opacity-80 leading-relaxed">{{ item.description }}</p>
            </div>
        </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const items = (settings) => getVal(settings, 'items') || []

const gridStyles = (settings, device) => {
    const cols = getVal(settings, 'columns', device) || 3
    const gap = getVal(settings, 'gap', device) || 'gap-8'
    
    const gapMap = {
        'gap-4': '1rem',
        'gap-8': '2rem',
        'gap-12': '3rem'
    }

    return {
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: gapMap[gap] || '2rem'
    }
}

const cardStyles = (settings) => {
    return {
        backgroundColor: getVal(settings, 'cardBgColor') || 'rgba(255, 255, 255, 0.5)',
        borderColor: getVal(settings, 'cardBorderColor') || 'rgba(0,0,0,0.1)',
        borderWidth: '1px',
        borderStyle: 'solid'
    }
}

const iconWrapperStyles = (settings) => {
    return {
        backgroundColor: getVal(settings, 'iconBgColor') || 'rgba(37, 99, 235, 0.1)'
    }
}

const iconSizeClass = (settings) => {
    const wrapperSize = getVal(settings, 'iconSize') || 'w-14 h-14'
    if (wrapperSize.includes('w-20')) return 'w-10 h-10'
    if (wrapperSize.includes('w-14')) return 'w-7 h-7'
    return 'w-5 h-5'
}

const getIcon = (iconName) => {
    if (!iconName) return null
    const pascalName = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('')
        return LucideIcons[pascalName] || LucideIcons[iconName] || LucideIcons.Star
}
</script>

<style scoped>
.feature-grid-container {
  width: 100%;
}
</style>
