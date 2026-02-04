<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
        <div class="feature-grid-container grid" :style="gridStyles(settings, blockDevice as string)">
            <div 
                v-for="(item, index) in items(settings)" 
                :key="index"
                class="feature-item transition-[width] duration-500 hover:-translate-y-3 group p-8 border border-transparent"
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

<script setup lang="ts">
import { type CSSProperties } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal } from '../utils/styleUtils'
import Star from 'lucide-vue-next/dist/esm/icons/star.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Globe from 'lucide-vue-next/dist/esm/icons/globe.js';
import Code2 from 'lucide-vue-next/dist/esm/icons/code-xml.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Zap from 'lucide-vue-next/dist/esm/icons/zap.js';
import Shield from 'lucide-vue-next/dist/esm/icons/shield.js';
import Heart from 'lucide-vue-next/dist/esm/icons/heart.js';
import HelpCircle from 'lucide-vue-next/dist/esm/icons/circle-question-mark.js';
import type { Component } from 'vue'
import type { BlockProps, ModuleSettings } from '@/types/builder'

const iconMap: Record<string, Component> = {
    Star, Layers, Palette, Globe, Code2, Check, Zap, Shield, Heart, HelpCircle
}

defineProps<BlockProps>()

interface FeatureItem {
    title: string;
    description: string;
    icon?: string;
}
const items = (settings: ModuleSettings) => (getVal<FeatureItem[]>(settings, 'items') || [])

const gridStyles = (settings: ModuleSettings, device: string): CSSProperties => {
    const cols = getVal<number>(settings, 'columns', device) || 3
    const gap = getVal<string>(settings, 'gap', device) || 'gap-8'
    
    const gapMap: Record<string, string> = {
        'gap-4': '1rem',
        'gap-8': '2rem',
        'gap-12': '3rem'
    }

    return {
        gridTemplateColumns: `repeat(${cols}, 1fr)`,
        gap: gapMap[gap] || '2rem'
    } as CSSProperties
}

const cardStyles = (settings: ModuleSettings): CSSProperties => {
    return {
        backgroundColor: getVal<string>(settings, 'cardBgColor') || 'rgba(255, 255, 255, 0.5)',
        borderColor: getVal<string>(settings, 'cardBorderColor') || 'rgba(0,0,0,0.1)',
        borderWidth: '1px',
        borderStyle: 'solid'
    } as CSSProperties
}

const iconWrapperStyles = (settings: ModuleSettings): CSSProperties => {
    return {
        backgroundColor: getVal<string>(settings, 'iconBgColor') || 'rgba(37, 99, 235, 0.1)'
    } as CSSProperties
}

const iconSizeClass = (settings: ModuleSettings) => {
    const wrapperSize = getVal<string>(settings, 'iconSize') || 'w-14 h-14'
    if (wrapperSize.includes('w-20')) return 'w-10 h-10'
    if (wrapperSize.includes('w-14')) return 'w-7 h-7'
    return 'w-5 h-5'
}

const getIcon = (iconName: string) => {
    if (!iconName) return iconMap.Star
    const pascalName = iconName
        .split('-')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join('')
    return iconMap[pascalName] || iconMap[iconName] || iconMap.Star
}
</script>

<style scoped>
.feature-grid-container {
  width: 100%;
}
</style>
