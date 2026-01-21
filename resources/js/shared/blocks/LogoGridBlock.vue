<template>
  <BaseBlock
    :id="id"
    :mode="mode"
    :settings="settings"
    :is-preview="isPreview"
    class="logo-grid-wrapper"
  >
    <div 
      v-if="hasTitle" 
      class="logo-grid-header" 
      :style="titleStyles"
    >
      <div
        :contenteditable="mode === 'edit'"
        @blur="updateField('title', $event.target.innerText)"
        v-text="getVal(settings, 'title')"
      ></div>
    </div>

    <div class="logo-grid-container" :style="gridStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="logo-card"
        :class="{ 
          'logo--grayscale': getVal(settings, 'grayscale') !== false, 
          'logo--hover-color': getVal(settings, 'hoverColor') !== false 
        }"
      >
        <div class="logo-inner" :style="logoContainerStyles">
          <img 
            v-if="item.image" 
            :src="item.image" 
            :alt="item.name" 
            class="logo-image"
            :style="logoImgStyles"
          />
          <div v-else class="logo-placeholder" :style="placeholderStyles">
            <Building class="building-icon" />
          </div>
        </div>
      </div>
      
      <!-- Builder specific empty state -->
      <div v-if="items.length === 0 && mode === 'edit'" class="empty-logos-placeholder">
        <Building :size="24" />
        <span>Add logos in settings</span>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Building } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  id: String,
  mode: { type: String, default: 'view' },
  settings: { type: Object, default: () => ({}) },
  isPreview: Boolean
})

const builder = inject('builder', null)

const items = computed(() => props.settings.items || [])
const hasTitle = computed(() => props.mode === 'edit' || !!getVal(props.settings, 'title'))

const gridStyles = computed(() => {
  const cols = parseInt(getVal(props.settings, 'columns')) || 4
  const gap = parseInt(getVal(props.settings, 'gap')) || 40
  return {
    display: 'grid',
    gridTemplateColumns: `repeat(${cols}, 1fr)`,
    gap: `${gap}px`,
    alignItems: 'center',
    justifyItems: 'center',
    width: '100%'
  }
})

const titleStyles = computed(() => ({
  ...getTypographyStyles(props.settings, 'title_'),
  marginBottom: '32px',
  textAlign: 'center',
  width: '100%',
  outline: 'none'
}))

const logoContainerStyles = computed(() => {
  const size = parseInt(getVal(props.settings, 'logoSize')) || 140
  const opacity = parseFloat(getVal(props.settings, 'logoOpacity')) || 0.6
  return {
    width: '100%',
    maxWidth: `${size}px`,
    opacity: opacity,
    transition: 'all 0.3s ease'
  }
})

const logoImgStyles = computed(() => ({
  width: '100%',
  height: 'auto',
  maxHeight: '100px',
  objectFit: 'contain'
}))

const placeholderStyles = computed(() => ({
  width: '100%',
  aspectRatio: '3/2',
  backgroundColor: '#f8fafc',
  borderRadius: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: '#cbd5e1',
  border: '1px solid #e2e8f0'
}))

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
.logo-grid-wrapper {
  width: 100%;
}

.logo-card {
  width: 100%;
  display: flex;
  justify-content: center;
}

.logo--grayscale .logo-inner {
  filter: grayscale(100%);
}

.logo--hover-color:hover .logo-inner {
  filter: grayscale(0);
  opacity: 1 !important;
  transform: scale(1.05);
}

.building-icon {
  opacity: 0.5;
}

.empty-logos-placeholder {
  grid-column: 1 / -1;
  padding: 40px;
  text-align: center;
  color: #94a3b8;
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

@media (max-width: 768px) {
  .logo-grid-container {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 24px !important;
  }
}
</style>
