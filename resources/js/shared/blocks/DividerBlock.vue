<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ settings, device: blockDevice }">
      <div class="divider-block-wrapper" :style="wrapperStyles(settings, blockDevice)">
        <!-- Solid / Standard Line -->
        <hr 
            v-if="getVal(settings, 'pattern', blockDevice) === 'classic' || getVal(settings, 'pattern', blockDevice) === 'dashed'" 
            class="divider-line" 
            :style="lineStyles(settings, blockDevice)" 
        />

        <!-- Pattern Line (SVG) -->
        <svg 
            v-else 
            class="divider-pattern-svg" 
            :style="patternSvgStyles(settings, blockDevice)"
            preserveAspectRatio="none"
            fill="none"
        >
          <defs>
            <linearGradient v-if="getVal(settings, 'use_gradient', blockDevice)" :id="`grad-${module.id}`" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop 
                    v-for="(stop, i) in (getVal(settings, 'gradient', blockDevice)?.stops || [])" 
                    :key="i" 
                    :offset="`${stop.position}%`" 
                    :style="{ stopColor: stop.color }" 
                />
            </linearGradient>
          </defs>
          
          <path 
            :d="getPathData(getVal(settings, 'pattern', blockDevice))" 
            :stroke="getStrokeColor(settings, blockDevice)"
            :stroke-width="getVal(settings, 'lineWeight', blockDevice) || 2"
            :stroke-dasharray="getVal(settings, 'pattern', blockDevice) === 'dots' ? '0.1 8' : 'none'"
            :stroke-linecap="getVal(settings, 'pattern', blockDevice) === 'dots' ? 'round' : 'butt'"
            vector-effect="non-scaling-stroke"
          />
        </svg>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, toCSS } from '../utils/styleUtils'

const props = withDefaults(defineProps<{
  module: any; // BlockInstance
  mode: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile' | null;
}>(), {
  mode: 'view',
  device: 'desktop'
})

const getStrokeColor = (settings: any, device: string) => {
    return getVal(settings, 'use_gradient', device) 
        ? `url(#grad-${props.module.id})` 
        : (getVal(settings, 'lineColor', device) || '#cccccc')
}

const wrapperStyles = (settings: any, device: string) => {
  const align = getVal(settings, 'alignment', device) || 'center'
  return {
    width: '100%',
    display: 'flex',
    justifyContent: align === 'center' ? 'center' : align === 'right' ? 'flex-end' : 'flex-start',
    padding: '20px 0'
  }
}

const lineStyles = (settings: any, device: string) => {
  const weight = getVal(settings, 'lineWeight', device) || 2
  const width = getVal(settings, 'lineWidth', device) || '100%'
  const style = getVal(settings, 'pattern', device) === 'dashed' ? 'dashed' : 'solid'
  const color = getVal(settings, 'lineColor', device) || '#cccccc'
  
  return {
    border: 'none',
    borderTop: `${weight}px ${style} ${color}`,
    width: toCSS(width),
    margin: 0
  }
}

const patternSvgStyles = (settings: any, device: string) => {
    const width = getVal(settings, 'lineWidth', device) || '100%'
    const weight = getVal(settings, 'lineWeight', device) || 2
    return {
        width: toCSS(width),
        height: `${weight * 4}px`, // Providing some vertical space for the waves/zig-zag
        overflow: 'visible'
    }
}

const getPathData = (pattern: string) => {
    if (pattern === 'waves') {
        return "M 0 10 Q 25 0, 50 10 T 100 10 T 150 10 T 200 10" // Tiling handled by repetitive paths or non-conservative viewbox
        // Proper tiling SVG path for 100% width:
    }
    if (pattern === 'zigzag') {
        return "M 0 10 L 10 0 L 20 10 L 30 0 L 40 10 L 50 0 L 60 10 L 70 0 L 80 10 L 90 0 L 100 10"
    }
    // Dots or Fallback
    return "M 0 10 L 100 10"
}

// Overwrite getPathData for better 100% width support via scaling
// We'll use a standard coordinate system and let preserveAspectRatio="none" or non-scaling-stroke handle it.
const getPathDataScaled = (pattern: string) => {
    if (pattern === 'waves') return "M 0 50 C 10 20, 20 20, 30 50 C 40 80, 50 80, 60 50 C 70 20, 80 20, 90 50 L 100 50" // Stylized wave
    if (pattern === 'zigzag') return "M 0 80 L 10 20 L 20 80 L 30 20 L 40 80 L 50 20 L 60 80 L 70 20 L 80 80 L 90 20 L 100 80"
    return "M 0 50 L 100 50"
}
</script>

<style scoped>
.divider-block-wrapper { box-sizing: border-box; }
.divider-line { flex-shrink: 0; }
.divider-pattern-svg {
    display: block;
    min-height: 20px;
}
</style>
