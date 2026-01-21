<template>
  <div 
    class="row-block transition-[background-position] duration-200 ease-out" 
    :style="styles" 
    ref="rowRef"
    @mousemove="handleMouseMove"
    @mouseleave="handleMouseLeave"
  >
    <div 
        v-for="(col, index) in nestedBlocks" 
        :key="col.id" 
        class="row-column"
        :style="getColumnWrapperStyle(index)"
    >
        <ColumnBlock 
            v-bind="col.settings" 
            :settings="col.settings"
            :nestedBlocks="col.children"
            :context="context"
            :isPreview="isPreview"
        />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import ColumnBlock from './ColumnBlock.vue'
import { getSpacingStyles, getBorderStyles, getBoxShadowStyles, getBackgroundStyles, getSizingStyles } from '../utils'

const props = defineProps({
    columns: { type: String, default: '1' },
    gutterWidth: { type: [Number, String], default: 30 },
    equalizeColumns: Boolean,
    backgroundColor: String,
    padding: Object,
    margin: Object,
    border: Object,
    boxShadow: Object,
    settings: { type: Object, default: () => ({}) },
    nestedBlocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: Boolean
})

// Parallax State
const scrollY = ref(0)
const mouseX = ref(0)
const mouseY = ref(0)
const rowRef = ref(null)

const handleMouseMove = (e) => {
    if (!props.isPreview) return
    const rect = rowRef.value.getBoundingClientRect()
    mouseX.value = ((e.clientX - rect.left) / rect.width) * 2 - 1
    mouseY.value = ((e.clientY - rect.top) / rect.height) * 2 - 1
}

const handleMouseLeave = () => {
    mouseX.value = 0
    mouseY.value = 0
}

const handleScroll = (e) => {
    if (e && e.target && e.target.scrollTop !== undefined) {
        scrollY.value = e.target.scrollTop
    } else {
        scrollY.value = window.scrollY
    }
}

onMounted(() => {
    const frame = document.querySelector('.canvas-frame')
    if (frame) {
        frame.addEventListener('scroll', handleScroll)
        scrollY.value = frame.scrollTop
    } else {
        window.addEventListener('scroll', handleScroll)
    }
})

onUnmounted(() => {
    const frame = document.querySelector('.canvas-frame')
    if (frame) frame.removeEventListener('scroll', handleScroll)
    window.removeEventListener('scroll', handleScroll)
})

const styles = computed(() => {
    const s = {
        display: 'flex',
        flexWrap: 'wrap',
        gap: `${props.gutterWidth}px`
    }
    
    if(props.backgroundColor) s.backgroundColor = props.backgroundColor

    // Use robust background logic from utils
    const settings = props.settings || {}
    if (settings) {
        const bgStyles = getBackgroundStyles(settings)
        Object.assign(s, bgStyles)

        // Handle "True Parallax" (JS-based)
        const isParallax = settings.parallax === true || settings.parallax === 'true'
        const isTrueParallax = isParallax && (settings.parallaxMethod === 'true' || settings.parallaxMethod === true)
        
        if (isTrueParallax && rowRef.value) {
            const speed = 0.5
            const itemTop = rowRef.value.offsetTop || 0
            const scrollOffset = (scrollY.value - itemTop) * speed
            
            // More noticeable mouse nudge
            const mouseNudgeX = mouseX.value * 30
            const mouseNudgeY = mouseY.value * 30

            const basePos = bgStyles.backgroundPosition || 'center center'
            const parts = basePos.split(',').map(pos => {
                const p = pos.trim().split(/\s+/)
                let x = p[0] || 'center'
                let y = p[1] || 'center'
                
                const xNorm = x === 'left' ? '0%' : x === 'right' ? '100%' : x === 'center' ? '50%' : x
                const yNorm = y === 'top' ? '0%' : y === 'bottom' ? '100%' : y === 'center' ? '50%' : y
                
                const xFinal = xNorm.includes('%') || xNorm.includes('px') ? `calc(${xNorm} + ${mouseNudgeX}px)` : xNorm
                const yFinal = `calc(${yNorm} + ${scrollOffset + mouseNudgeY}px)`
                
                return `${xFinal} ${yFinal}`
            })
            s.backgroundPosition = parts.join(', ')
            s.backgroundAttachment = 'scroll'
        }
    }

    if(props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if(props.margin) Object.assign(s, getSpacingStyles(props.margin, 'margin'))
    if(props.border) Object.assign(s, getBorderStyles(props.border))
    if(props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    // Apply Sizing styles
    if (settings) {
        Object.assign(s, getSizingStyles(settings))
    }

    if(props.equalizeColumns) s.alignItems = 'stretch'
    
    return s
})

const widths = computed(() => {
    if (!props.columns) return [100]
    const parts = props.columns.split('-')
    const total = parts.reduce((a, b) => Number(a) + Number(b), 0)
    return parts.map(p => (Number(p) / total) * 100)
})

const getColumnWrapperStyle = (index) => {
    const w = widths.value[index] || 100
    const gap = Number(props.gutterWidth) || 0
    
    // Simplified width logic accounting for gap
    // Use calc to subtract appropriate gap portion
    // Note: This logic assumes gap is handled by flex gap.
    // If 2 columns, each needs (100% - gap) / 2
    // If 3 columns, each needs (100% - 2*gap) / 3
    
    // Robust logic:
    // flex-grow: 0, flex-shrink: 0
    // flex-basis: calc(X% - (gap * (N-1) / N) ?) -- too complex for dynamic N
    
    // Fallback: Just subtract gap?
    // calc(${w}% - ${gap}px) is safe for 2 cols (50% - 30px). 
    // Total = 100% - 60px + 30px gap = 100%-30px. Leaves space.
    // It's a bit loose but works safe.
    
    return {
        flex: `0 0 calc(${w}% - ${gap}px)`,
        maxWidth: `calc(${w}% - ${gap}px)`
    }
}
</script>

<style scoped>
.row-block {
    width: 100%;
}
.row-column {
    display: flex;
    flex-direction: column;
}
</style>
