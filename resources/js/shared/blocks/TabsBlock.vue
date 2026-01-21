<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="tabs-block" :style="tabsBlockStyles" :class="layoutClass">
        <!-- Tab Headers -->
        <div class="tabs-header" :style="headerStyles">
          <button 
            v-for="(tab, index) in items" 
            :key="index"
            class="tab-button"
            :class="{ 'tab-button--active': activeTabIndex === index }"
            :style="getTabStyles(index)"
            @click="activeTabIndex = index"
          >
            <span class="flex items-center gap-2">
              <LucideIcon v-if="tab.icon" :name="tab.icon" class="w-4 h-4" />
              {{ tab.title || 'Tab' }}
            </span>
          </button>
        </div>
        
        <!-- Tab Content -->
        <div class="tabs-content" :style="contentStyles">
          <div 
            v-for="(tab, index) in items" 
            v-show="activeTabIndex === index"
            :key="index"
            class="tab-pane prose prose-sm max-w-none"
            v-html="tab.content || 'Tab content...'"
          ></div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, ref } from 'vue'
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
const activeTabIndex = ref(0)

const layoutClass = computed(() => {
  const position = getVal(settings.value, 'tabPosition', props.device) || 'top'
  return `tabs--${position}`
})

const tabsBlockStyles = computed(() => {
  return { 
    width: '100%', 
    overflow: 'hidden',
    borderRadius: '12px'
  }
})

const headerStyles = computed(() => {
  const styles = { display: 'flex' }
  const alignment = getVal(settings.value, 'tabAlignment', props.device) || 'left'
  
  if (alignment === 'center') {
    styles.justifyContent = 'center'
  } else if (alignment === 'right') {
    styles.justifyContent = 'flex-end'
  }
  
  return styles
})

const getTabStyles = (index) => {
  const isActive = activeTabIndex.value === index
  const alignment = getVal(settings.value, 'tabAlignment', props.device) || 'left'
  
  const styles = {
    padding: '14px 24px',
    border: 'none',
    cursor: 'pointer',
    transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
    flex: alignment === 'fill' ? '1' : 'none',
    position: 'relative'
  }
  
  const typographyPrefix = isActive ? 'tab_active_' : 'tab_'
  Object.assign(styles, getTypographyStyles(settings.value, typographyPrefix, props.device))
  
  const activeBg = getVal(settings.value, 'tabActiveBackgroundColor', props.device) || '#ffffff'
  const normalBg = getVal(settings.value, 'tabBackgroundColor', props.device) || 'transparent'
  
  styles.backgroundColor = isActive ? activeBg : normalBg
    
  return styles
}

const contentStyles = computed(() => {
  const bgColor = getVal(settings.value, 'contentBackgroundColor', props.device) || '#ffffff'
  const styles = { 
    backgroundColor: bgColor,
    padding: '24px',
    minHeight: '100px'
  }
  Object.assign(styles, getTypographyStyles(settings.value, 'content_', props.device))
  return styles
})
</script>

<style scoped>
.tabs-block { width: 100%; }
.tabs-header { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }

.tab-button {
  color: #64748b;
  border-bottom: 2px solid transparent;
}

.tab-button:hover { background: rgba(0,0,0,0.02); color: #0f172a; }

.tab-button--active { 
  color: var(--theme-primary-color, #2059ea) !important;
  border-bottom-color: var(--theme-primary-color, #2059ea);
}

.tabs--left { display: flex; }
.tabs--left .tabs-header { flex-direction: column; border-bottom: none; border-right: 1px solid #e2e8f0; width: 220px; }
.tabs--left .tab-button { border-bottom: none; border-right: 2px solid transparent; text-align: left; }
.tabs--left .tab-button--active { border-right-color: var(--theme-primary-color, #2059ea); }
.tabs--left .tabs-content { flex: 1; }

.tabs--bottom { display: flex; flex-direction: column-reverse; }
.tabs--bottom .tabs-header { border-bottom: none; border-top: 1px solid #e2e8f0; }
.tabs--bottom .tab-button { border-bottom: none; border-top: 2px solid transparent; }
.tabs--bottom .tab-button--active { border-top-color: var(--theme-primary-color, #2059ea); }
</style>
