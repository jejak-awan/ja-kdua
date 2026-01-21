<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="accordion-block" :style="accordionBlockStyles">
        <div class="accordion-list" :style="listStyles">
          <div 
            v-for="(item, index) in items" 
            :key="index"
            class="accordion-item"
            :class="{ 'accordion-item--open': openIndices.includes(index) }"
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
              v-show="openIndices.includes(index)"
              class="accordion-content prose prose-sm max-w-none"
              :style="getContentStyles(index)"
              v-html="item.content || 'Content goes here...'"
            ></div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const settings = computed(() => props.module?.settings || {})
const items = computed(() => settings.value.items || [])
const openIndices = ref([])

const allowMultiple = computed(() => getVal(settings.value, 'allowMultiple', props.device) === true)
const toggleIcon = computed(() => getVal(settings.value, 'toggleIcon', props.device) || 'chevron-down')
const iconPosition = computed(() => getVal(settings.value, 'iconPosition', props.device) || 'right')
const iconSize = computed(() => getVal(settings.value, 'iconSize', props.device) || 18)

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

const accordionBlockStyles = computed(() => {
  return { width: '100%', overflow: 'hidden' }
})

const listStyles = computed(() => ({
    display: 'flex',
    flexDirection: 'column',
    gap: `${getVal(settings.value, 'gap', props.device) || 16}px`
}))

const getHeaderStyles = (index) => {
    const isOpen = openIndices.value.includes(index)
    const bgColor = isOpen 
        ? getVal(settings.value, 'openHeaderBackgroundColor', props.device) || '#f1f5f9'
        : getVal(settings.value, 'headerBackgroundColor', props.device) || '#f8fafc'
    
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

const titleStyles = computed(() => getTypographyStyles(settings.value, 'header_', props.device))

const iconStyles = computed(() => {
    const color = getVal(settings.value, 'iconColor', props.device) || 'currentColor'
    return {
        color: color,
        width: `${iconSize.value}px`,
        height: `${iconSize.value}px`,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'transform 0.3s ease'
    }
})

const getContentStyles = (index) => {
    const styles = {
        padding: '20px',
        backgroundColor: getVal(settings.value, 'contentBackgroundColor', props.device) || '#ffffff'
    }
    Object.assign(styles, getTypographyStyles(settings.value, 'content_', props.device))
    return styles
}
</script>

<style scoped>
.accordion-header:hover { opacity: 0.9; }
.rotate-180 { transform: rotate(180deg); }
</style>
