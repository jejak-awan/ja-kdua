<template>
  <div class="border-field">
    <!-- Rounded Corners -->
    <BaseCollapsible class="mb-4" :default-open="true">
      <template #title>
        <BaseLabel :muted="false">
          <template #prefix><CornerUpLeft :size="14" /></template>
          {{ t('builder.fields.border.radius.label') }}
        </BaseLabel>
      </template>
      
      <div class="radius-controls grid grid-cols-2 gap-2">
        <div v-for="corner in cornerKeys" :key="corner" class="control-group">
            <BaseInput 
              v-model.number="radius[corner]" 
              type="number" 
              @input="updateRadius(corner)" 
              :placeholder="String(placeholderValue?.radius?.[corner] ?? 0)"
              class="text-center"
            />
           <span class="label-corner">{{ t(`builder.fields.border.radius.${corner}`) }}</span>
        </div>
        
        <!-- Link Button -->
        <div class="link-wrapper absolute-center">
             <button class="link-btn" :class="{active: radius.linked}" @click="toggleRadiusLink" :title="t('builder.fields.spacing.link')">
                <Link2 :size="12" />
             </button>
        </div>
      </div>
    </BaseCollapsible>

    <!-- Border Styles -->
    <div class="border-section px-3">
      <BaseLabel class="mb-2">
        <template #prefix><BoxSelect :size="14" /></template>
        {{ t('builder.fields.border.styles.label') }}
      </BaseLabel>

      <!-- Side Selection -->
      <BaseSegmentedControl
        v-model="activeSide"
        :options="mappedSides"
        :full-width="true"
        class="mb-3"
      />
      
      <!-- Helpers for active side style -->
      <div class="style-inputs flex flex-col gap-3">
         
         <!-- Width -->
         <div class="input-row">
            <BaseLabel>{{ t('builder.fields.border.width') }}</BaseLabel>
            <BaseSliderInput 
              v-model.number="currentStyle.width" 
              :min="0" 
              :max="50" 
              :placeholder-value="placeholderValue?.styles?.[activeSide]?.width ?? placeholderValue?.styles?.all?.width ?? null"
              @update:model-value="updateStyle('width')"
              unit="px"
            />
         </div>

         <!-- Color -->
         <div class="input-row">
             <BaseLabel>{{ t('builder.fields.border.color') }}</BaseLabel>
             <ColorField 
               :value="currentStyle.color" 
               :placeholder-value="placeholderValue?.styles?.[activeSide]?.color ?? placeholderValue?.styles?.all?.color ?? null"
               @update:value="(val) => { currentStyle.color = val; updateStyle('color', val) }" 
             />
         </div>

         <!-- Style -->
         <div class="input-row">
             <BaseLabel>{{ t('builder.fields.border.style') }}</BaseLabel>
             <select class="builder-select w-full" v-model="currentStyle.style" @change="updateStyle('style')">
                 <option value="solid">{{ t('builder.fields.border.styleOptions.solid') }}</option>
                 <option value="dashed">{{ t('builder.fields.border.styleOptions.dashed') }}</option>
                 <option value="dotted">{{ t('builder.fields.border.styleOptions.dotted') }}</option>
                 <option value="double">{{ t('builder.fields.border.styleOptions.double') }}</option>
                 <option value="none">{{ t('builder.fields.border.styleOptions.none') }}</option>
             </select>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link2, CornerUpLeft, BoxSelect, Square, ArrowUp, ArrowRight, ArrowDown, ArrowLeft } from 'lucide-vue-next'
import { 
  BaseCollapsible, 
  BaseLabel, 
  BaseSegmentedControl, 
  BaseSliderInput, 
  BaseInput 
} from '../ui'
import ColorField from './ColorField.vue'

const props = defineProps({
  field: Object,
  value: {
    type: Object,
    default: () => ({
      radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
      styles: {
        all: { width: 0, color: '#333333', style: 'solid' },
        top: { width: 0, color: '#333333', style: 'solid' },
        right: { width: 0, color: '#333333', style: 'solid' },
        bottom: { width: 0, color: '#333333', style: 'solid' },
        left: { width: 0, color: '#333333', style: 'solid' }
      }
    })
  },
  placeholderValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:value'])
const { t } = useI18n()

// State
const cornerKeys = ['tl', 'tr', 'bl', 'br']
const sides = [
    { id: 'all', label: 'All', icon: Square },
    { id: 'top', label: 'Top', icon: ArrowUp },
    { id: 'right', label: 'Right', icon: ArrowRight },
    { id: 'bottom', label: 'Bottom', icon: ArrowDown },
    { id: 'left', label: 'Left', icon: ArrowLeft }
]

const mappedSides = computed(() => {
    return sides.map(s => ({
        label: s.label, // Ideally translated too? But symbols OK for now or hardcoded. 'All', 'Top' etc. are icons mostly. Context?
        // Note: The BaseSegmentedControl uses label for title/tooltip mostly if icon is present.
        // We can translate specific side names if needed.
        value: s.id,
        icon: s.icon,
        iconOnly: true
    }))
})

const activeSide = ref('all')

// Internal models
const radius = reactive({ tl: 0, tr: 0, bl: 0, br: 0, linked: true })
const styles = reactive({
    all: { width: 0, color: '#333333', style: 'solid' },
    top: { width: 0, color: '#333333', style: 'solid' },
    right: { width: 0, color: '#333333', style: 'solid' },
    bottom: { width: 0, color: '#333333', style: 'solid' },
    left: { width: 0, color: '#333333', style: 'solid' }
})

// Sync from props
watch(() => props.value, (newVal) => {
    if(!newVal) return
    if(newVal.radius) Object.assign(radius, newVal.radius)
    if(newVal.styles) Object.assign(styles, newVal.styles)
}, { deep: true, immediate: true })

// Derived control for current active side
const currentStyle = computed(() => {
    return styles[activeSide.value] || styles.all
})

// Updates
const updateRadius = (corner) => {
    if(radius.linked) {
        const val = radius[corner]
        radius.tl = radius.tr = radius.bl = radius.br = val
    }
    emitUpdate()
}

const toggleRadiusLink = () => {
    radius.linked = !radius.linked
    if(radius.linked) {
        const val = radius.tl 
        radius.tr = radius.bl = radius.br = val
        emitUpdate()
    }
}

const updateStyle = (prop, val) => {
    if(activeSide.value === 'all') {
         ['top','right','bottom','left'].forEach(s => {
             styles[s][prop] = styles.all[prop]
         })
    }
    emitUpdate()
}

const emitUpdate = () => {
    emit('update:value', {
        radius: { ...radius },
        styles: JSON.parse(JSON.stringify(styles))
    })
}
</script>

<style scoped>
.radius-controls {
    position: relative;
}
.link-wrapper {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
}
.link-btn {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: var(--builder-text-muted);
    transition: all 0.2s;
}
.link-btn.active {
    background: var(--builder-accent);
    color: white;
    border-color: var(--builder-accent);
}
.label-corner {
    font-size: 9px;
    color: var(--builder-text-muted);
    position: absolute;
    right: 8px; top: 10px;
    pointer-events: none;
    opacity: 0.5;
}
.control-group { position: relative; }

.input-row {
    display: flex;
    flex-direction: column;
}
</style>
