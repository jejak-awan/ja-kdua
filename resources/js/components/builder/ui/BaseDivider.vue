<template>
  <div 
    class="base-divider"
    :class="[`base-divider--${orientation}`]"
    :style="dividerStyle"
  ></div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  orientation: {
    type: String,
    default: 'horizontal', // horizontal, vertical
    validator: (value) => ['horizontal', 'vertical'].includes(value)
  },
  margin: {
    type: [String, Number],
    default: 8
  }
})

const dividerStyle = computed(() => {
  const marginValue = typeof props.margin === 'number' ? `${props.margin}px` : props.margin
  return props.orientation === 'horizontal' 
    ? { marginTop: marginValue, marginBottom: marginValue }
    : { marginLeft: marginValue, marginRight: marginValue }
})
</script>

<style scoped>
.base-divider {
  background-color: var(--builder-border, #374151);
}

.base-divider--horizontal {
  height: 1px;
  width: 100%;
}

.base-divider--vertical {
  width: 1px;
  height: 100%;
  display: inline-block;
}
</style>
