<template>
    <div class="column-block" :style="styles">
        <BlockRenderer 
            :blocks="nestedBlocks" 
            :context="context"
            :isPreview="isPreview"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import BlockRenderer from '../BlockRenderer.vue'
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles, getSizingStyles, getTransformStyles, getBackgroundStyles } from '../utils'

const props = defineProps({
    backgroundColor: String,
    padding: Object,
    border: Object,
    boxShadow: Object,
    verticalAlignment: String,
    settings: { type: Object, default: () => ({}) },
    nestedBlocks: { type: Array, default: () => [] },
    context: Object,
    isPreview: Boolean
})

const styles = computed(() => {
    const s = {
        display: 'flex',
        flexDirection: 'column',
        width: '100%',
    }
    
    const settings = props.settings || {}

    // Background
    if (props.backgroundColor) s.backgroundColor = props.backgroundColor
    if (settings) Object.assign(s, getBackgroundStyles(settings))

    const vAlign = getVal(settings, 'verticalAlign') || props.verticalAlignment || 'start'
    if (vAlign === 'center') s.justifyContent = 'center'
    else if (vAlign === 'bottom' || vAlign === 'end') s.justifyContent = 'flex-end'
    else s.justifyContent = 'flex-start'

    if(props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if(props.border) Object.assign(s, getBorderStyles(props.border))
    if(props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    if (settings) {
        Object.assign(s, getSizingStyles(settings))
        Object.assign(s, getTransformStyles(settings))
    }

    return s
})

</script>
