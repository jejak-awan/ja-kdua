<template>
    <div class="text-block" :style="styles" v-html="content"></div>
</template>

<script setup>
import { computed } from 'vue'
import { getBorderStyles, getSpacingStyles, getBoxShadowStyles } from '../utils'

const props = defineProps({
    content: String,
    alignment: String,
    fontSize: [Number, String],
    fontWeight: [Number, String],
    textColor: String,
    lineHeight: [Number, String],
    padding: Object,
    margin: Object,
    border: Object,
    boxShadow: Object,
    // Ignored props but required to avoid warnings if passed
    nestedBlocks: Array,
    context: Object,
    isPreview: Boolean
})

const styles = computed(() => {
    const s = {}
    
    // Typography
    if(props.alignment) s.textAlign = props.alignment
    if(props.fontSize) s.fontSize = `${props.fontSize}px`
    if(props.fontWeight) s.fontWeight = props.fontWeight
    if(props.textColor) s.color = props.textColor
    if(props.lineHeight) s.lineHeight = props.lineHeight
    
    // Box Model
    if(props.padding) Object.assign(s, getSpacingStyles(props.padding, 'padding'))
    if(props.margin) Object.assign(s, getSpacingStyles(props.margin, 'margin'))
    if(props.border) Object.assign(s, getBorderStyles(props.border))
    if(props.boxShadow) Object.assign(s, getBoxShadowStyles(props.boxShadow))
    
    return s
})
</script>

<style scoped>
.text-block :deep(p) {
    margin-bottom: 1em;
}
.text-block :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
