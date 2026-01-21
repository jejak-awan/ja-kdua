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
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles, getSizingStyles } from '../utils'

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
        // height: '100%' // careful with height if content is short
    }
    
    if (props.verticalAlignment) {
        if (props.verticalAlignment === 'center') s.justifyContent = 'center'
        else if (props.verticalAlignment === 'bottom') s.justifyContent = 'flex-end'
        else s.justifyContent = 'flex-start'
    }

    if(props.backgroundColor) s.backgroundColor = props.backgroundColor
    if(props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if(props.border) Object.assign(s, getBorderStyles(props.border))
    if(props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    if (props.settings) {
        Object.assign(s, getSizingStyles(props.settings))
    }

    return s
})
</script>
