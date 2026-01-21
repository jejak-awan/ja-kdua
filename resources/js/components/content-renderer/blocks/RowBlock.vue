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
import { getSpacingStyles, getBorderStyles, getBoxShadowStyles, getBackgroundStyles, getSizingStyles, getTransformStyles, getVal } from '../utils'

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
    if (!props.isPreview || !rowRef.value) return
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
    
    const settings = props.settings || {}

    if(props.backgroundColor) s.backgroundColor = props.backgroundColor

    if (settings) {
        const bgStyles = getBackgroundStyles(settings)
        Object.assign(s, bgStyles)
    }

    if(props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if(props.margin) Object.assign(s, getSpacingStyles(props.margin, 'margin'))
    if(props.border) Object.assign(s, getBorderStyles(props.border))
    if(props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    // Apply Sizing styles
    if (settings) {
        Object.assign(s, getSizingStyles(settings))
        Object.assign(s, getTransformStyles(settings))
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
    const numCols = nestedBlocks.value?.length || 1
    const rawGap = getVal(props.settings, 'gutterWidth') || props.gutterWidth || 0
    const gap = parseFloat(String(rawGap).replace('px', '')) || 0
    
    // Formula: calc(P% - (TotalGap / NumCols))
    // We share the total gap burden equally across all columns
    const totalGapBurden = (numCols - 1) * gap
    const share = totalGapBurden / numCols
    
    const widthCalc = `calc(${w}% - ${share}px)`
    
    return {
        flex: `0 0 ${widthCalc}`,
        width: widthCalc,
        maxWidth: widthCalc
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
