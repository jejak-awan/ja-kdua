<template>
  <div class="accordion-block" :style="wrapperStyles">
    <div class="accordion-list" :style="listStyles">
      <div 
        v-for="(item, index) in items" 
        :key="index"
        class="accordion-item"
        :class="{ 'accordion-item--open': openIndices.includes(index) }"
        :style="getItemStyles(index)"
      >
        <!-- Header -->
        <button 
          class="accordion-header"
          :style="getHeaderStyles(index)"
          @click="toggle(index)"
        >
          <!-- Left Icon -->
          <div 
            v-if="toggleIcon !== 'none' && iconPosition === 'left'"
            class="accordion-icon"
            :class="{ 'rotate-180': openIndices.includes(index) && shouldRotate }"
            :style="iconStyles"
          >
            <LucideIcon :name="getIconName(index)" :size="iconSize" />
          </div>

          <span class="accordion-title flex-1" :style="titleStyles">{{ item.title || 'Accordion Title' }}</span>

          <!-- Right Icon -->
          <div 
            v-if="toggleIcon !== 'none' && iconPosition === 'right'"
            class="accordion-icon"
            :class="{ 'rotate-180': openIndices.includes(index) && shouldRotate }"
            :style="iconStyles"
          >
            <LucideIcon :name="getIconName(index)" :size="iconSize" />
          </div>
        </button>

        <!-- Content -->
        <div 
          v-if="openIndices.includes(index)"
          class="accordion-content prose prose-sm max-w-none"
          :style="contentStyles"
          v-html="item.content || 'Content goes here...'"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject, onMounted } from 'vue'
import LucideIcon from '../../ui/LucideIcon.vue'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Support Repeater Items
const items = computed(() => settings.value.items || [])
const openIndices = ref([])

const allowMultiple = computed(() => getResponsiveValue(settings.value, 'allowMultiple', device.value) === true)
const toggleIcon = computed(() => getResponsiveValue(settings.value, 'toggleIcon', device.value) || 'chevron-down')
const iconPosition = computed(() => getResponsiveValue(settings.value, 'iconPosition', device.value) || 'right')
const iconSize = computed(() => getResponsiveValue(settings.value, 'iconSize', device.value) || 18)

onMounted(() => {
    items.value.forEach((item, index) => {
        if (item.open) openIndices.value.push(index)
    })
})

const toggle = (index) => {
    if (allowMultiple.value) {
        if (openIndices.value.includes(index)) {
            openIndices.value = openIndices.value.filter(i => i !== index)
        } else {
            openIndices.value.push(index)
        }
    } else {
        openIndices.value = openIndices.value.includes(index) ? [] : [index]
    }
}

const getIconName = (index) => {
    const icon = toggleIcon.value;
    if (icon === 'plus') return openIndices.value.includes(index) ? 'minus' : 'plus';
    if (icon === 'chevron') return 'chevron-down'; 
    return icon;
}

const shouldRotate = computed(() => {
    const icon = toggleIcon.value.toLowerCase();
    return icon.includes('chevron') || icon.includes('arrow');
})

const wrapperStyles = computed(() => {
  const styles = { width: '100%', overflow: 'hidden' }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const listStyles = computed(() => ({
    display: 'flex',
    flexDirection: 'column',
    gap: `${getResponsiveValue(settings.value, 'gap', device.value) || 16}px`
}))

const getItemStyles = (index) => {
    const styles = { overflow: 'hidden' }
    Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
    return styles
}

const getHeaderStyles = (index) => {
    const isOpen = openIndices.value.includes(index)
    const bgColor = isOpen 
        ? getResponsiveValue(settings.value, 'openHeaderBackgroundColor', device.value) || '#f1f5f9'
        : getResponsiveValue(settings.value, 'headerBackgroundColor', device.value) || '#f8fafc'
    
    return {
        backgroundColor: bgColor,
        padding: '16px 20px',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        width: '100%',
        border: 'none',
        cursor: 'pointer',
        textAlign: 'left'
    }
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'header_', device.value))

const iconStyles = computed(() => {
    const color = getResponsiveValue(settings.value, 'iconColor', device.value) || 'currentColor'
    const size = iconSize.value // used for container size matching
    return {
        color: color,
        width: `${size}px`,
        height: `${size}px`,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'transform 0.3s ease'
    }
})

const contentStyles = computed(() => {
    const styles = {
        padding: '20px',
        backgroundColor: getResponsiveValue(settings.value, 'contentBackgroundColor', device.value) || '#ffffff'
    }
    Object.assign(styles, getTypographyStyles(settings.value, 'content_', device.value))
    return styles
})
</script>

<style scoped>
.accordion-header:hover { opacity: 0.9; }
</style>
