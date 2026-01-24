<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div class="divider-wrapper" :style="getLayoutStyles(settings, blockDevice)">
        <div class="divider-content" :style="contentStyles(settings, blockDevice)">
          <!-- Left Line -->
          <div 
            v-if="showLeftLine(settings, blockDevice)" 
            class="divider-line-part left" 
            :style="linePartStyles(settings, blockDevice)"
          >
            <hr v-if="isClassic(settings, blockDevice)" :style="classicLineStyles(settings, blockDevice)" />
            <svg v-else :style="patternSvgStyles(settings, blockDevice)" preserveAspectRatio="none" fill="none">
              <defs><linearGradient :id="`grad-l-${module.id}`" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop v-for="(stop, i) in getGrad(settings, blockDevice)" :key="i" :offset="`${stop.position}%`" :style="{ stopColor: stop.color }" />
              </linearGradient></defs>
              <path :d="getPath(getVal(settings, 'pattern', blockDevice))" :stroke="getStroke(settings, blockDevice, 'l')" :stroke-width="getWeight(settings, blockDevice)" vector-effect="non-scaling-stroke" />
            </svg>
          </div>

          <!-- Middle Element (Icon or Text) -->
          <div v-if="hasElement(settings, blockDevice)" class="divider-element" :style="elementWrapperStyles(settings, blockDevice)">
            <template v-if="getVal(settings, 'add_icon')">
              <LucideIcon :name="getVal(settings, 'icon')" :size="getVal(settings, 'icon_size') || 24" :color="getVal(settings, 'icon_color')" />
            </template>
            <template v-else-if="getVal(settings, 'add_text')">
              <span class="divider-text" :style="getTypographyStyles(settings, 'text_styling', blockDevice)">
                {{ getVal(settings, 'text') }}
              </span>
            </template>
          </div>

          <!-- Right Line -->
          <div 
            v-if="showRightLine(settings, blockDevice)" 
            class="divider-line-part right" 
            :style="linePartStyles(settings, blockDevice)"
          >
            <hr v-if="isClassic(settings, blockDevice)" :style="classicLineStyles(settings, blockDevice)" />
            <svg v-else :style="patternSvgStyles(settings, blockDevice)" preserveAspectRatio="none" fill="none">
              <defs><linearGradient :id="`grad-r-${module.id}`" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop v-for="(stop, i) in getGrad(settings, blockDevice)" :key="i" :offset="`${stop.position}%`" :style="{ stopColor: stop.color }" />
              </linearGradient></defs>
              <path :d="getPath(getVal(settings, 'pattern', blockDevice))" :stroke="getStroke(settings, blockDevice, 'r')" :stroke-width="getWeight(settings, blockDevice)" vector-effect="non-scaling-stroke" />
            </svg>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, toCSS, getLayoutStyles, getTypographyStyles } from '../utils/styleUtils'

const props = withDefaults(defineProps<{
  module: any; 
  mode: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

// Helpers
const isClassic = (settings: any, device: string) => {
  const p = getVal(settings, 'pattern', device)
  return p === 'classic' || p === 'dashed'
}

const hasElement = (settings: any, device: string) => {
  return getVal(settings, 'add_icon', device) || getVal(settings, 'add_text', device)
}

const showLeftLine = (settings: any, device: string) => {
  if (!getVal(settings, 'visible', device)) return false
  if (!hasElement(settings, device)) return true
  const pos = getVal(settings, 'divider_element_position', device) || 'center'
  return pos === 'center' || pos === 'right'
}

const showRightLine = (settings: any, device: string) => {
  if (!getVal(settings, 'visible', device)) return false
  if (!hasElement(settings, device)) return false // Handled by showLeftLine if no element
  const pos = getVal(settings, 'divider_element_position', device) || 'center'
  return pos === 'center' || pos === 'left'
}

const getWeight = (settings: any, device: string) => getVal(settings, 'lineWeight', device) || 2
const getGrad = (settings: any, device: string) => getVal(settings, 'gradient', device)?.stops || []

const getStroke = (settings: any, device: string, side: 'l' | 'r') => {
  const useGrad = getVal(settings, 'use_gradient', device)
  if (useGrad) return `url(#grad-${side}-${props.module.id})`
  return getVal(settings, 'lineColor', device) || '#cccccc'
}

const getPath = (pattern: string) => {
    if (pattern === 'waves') return "M 0 50 C 10 20, 20 20, 30 50 C 40 80, 50 80, 60 50 C 70 20, 80 20, 90 50 L 100 50"
    if (pattern === 'zigzag') return "M 0 80 L 10 20 L 20 80 L 30 20 L 40 80 L 50 20 L 60 80 L 70 20 L 80 80 L 90 20 L 100 80"
    return "M 0 50 L 100 50"
}

// Styles
const contentStyles = (settings: any, device: string) => {
  const align = getVal(settings, 'alignment', device) || 'center'
  const width = getVal(settings, 'lineWidth', device) || '100%'
  return {
    width: toCSS(width),
    display: 'flex',
    alignItems: 'center',
    margin: align === 'center' ? '0 auto' : align === 'right' ? '0 0 0 auto' : '0 auto 0 0'
  }
}

const linePartStyles = (settings: any, device: string) => {
  return {
    flex: 1,
    display: 'flex',
    alignItems: 'center'
  }
}

const classicLineStyles = (settings: any, device: string) => {
  const weight = getWeight(settings, device)
  const style = getVal(settings, 'pattern', device) === 'dashed' ? 'dashed' : 'solid'
  const color = getVal(settings, 'lineColor', device) || '#cccccc'
  return {
    border: 'none',
    borderTop: `${weight}px ${style} ${color}`,
    width: '100%',
    margin: 0
  }
}

const patternSvgStyles = (settings: any, device: string) => {
    const weight = getWeight(settings, device)
    return {
        width: '100%',
        height: `${weight * 4}px`,
        overflow: 'visible'
    }
}

const elementWrapperStyles = (settings: any, device: string) => {
  const gap = getVal(settings, 'text_gap', device) || '15px'
  return {
    padding: `0 ${toCSS(gap)}`,
    flexShrink: 0,
    display: 'flex',
    alignItems: 'center'
  }
}
</script>

<style scoped>
.divider-wrapper {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.divider-content {
  width: 100%;
}

.divider-line-part {
  min-width: 10px;
}

.divider-line-part.left {
  padding-right: 0;
}

.divider-line-part.right {
  padding-left: 0;
}

.divider-element {
  white-space: nowrap;
}

.divider-text {
  display: inline-block;
  line-height: 1;
}
</style>
