<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="progressbar-block"
  >
    <!-- Title above -->
    <div v-if="titlePosition === 'above'" class="progressbar-header">
      <span 
        class="progressbar-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateField('title', $event.target.innerText)"
        v-text="titleValue"
      ></span>
      <span v-if="showPercentage" class="progressbar-percentage" :style="percentageStyles">{{ percentageValue }}%</span>
    </div>
    
    <!-- Bar -->
    <div class="progressbar-track" :style="trackStyles">
      <div class="progressbar-fill" :style="fillStyles">
        <span v-if="titlePosition === 'inside'" class="progressbar-inside-text">
          {{ titleValue }} {{ showPercentage ? percentageValue + '%' : '' }}
        </span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const percentageValue = computed(() => {
  const p = parseInt(getVal(props.settings, 'percentage')) || 75
  return Math.min(100, Math.max(0, p))
})

const showPercentage = computed(() => getVal(props.settings, 'showPercentage') !== false)
const titlePosition = computed(() => getVal(props.settings, 'titlePosition') || 'above')
const titleValue = computed(() => getVal(props.settings, 'title') || 'Progress')

const trackStyles = computed(() => {
  const height = parseInt(getVal(props.settings, 'height')) || 20
  const radius = parseInt(getVal(props.settings, 'borderRadius')) || 10
  const trackColor = getVal(props.settings, 'trackColor') || '#e0e0e0'
  return {
    backgroundColor: trackColor,
    height: `${height}px`,
    borderRadius: `${radius}px`,
    overflow: 'hidden',
    width: '100%'
  }
})

const fillStyles = computed(() => {
  const radius = parseInt(getVal(props.settings, 'borderRadius')) || 10
  const barColor = getVal(props.settings, 'barColor') || 'var(--theme-primary-color, #2059ea)'
  return {
    backgroundColor: barColor,
    width: `${percentageValue.value}%`,
    height: '100%',
    borderRadius: `${radius}px`,
    transition: 'width 1.5s cubic-bezier(0.1, 0.5, 0.5, 1)',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
    paddingRight: '12px'
  }
})

const titleStyles = computed(() => getTypographyStyles(props.settings, 'title_'))
const percentageStyles = computed(() => getTypographyStyles(props.settings, 'percentage_'))

const updateField = (key, value) => {
  if (props.mode !== 'edit' || !builder) return
  
  const current = props.settings[key]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [builder.device.value]: value }
  } else {
    newValue = { [builder.device.value]: value }
  }
  
  builder.updateModuleSettings(props.id, { [key]: newValue })
}
</script>

<style scoped>
.progressbar-block {
  width: 100%;
}

.progressbar-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 8px;
}

.progressbar-title {
  font-weight: 600;
  outline: none;
}

.progressbar-percentage {
  font-weight: 700;
}

.progressbar-track {
  width: 100%;
}

.progressbar-inside-text {
  color: white;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
</style>
