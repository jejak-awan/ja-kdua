<template>
  <nav class="fullwidth-menu-block" :style="containerStyles">
    <div class="menu-inner">
      <!-- Logo -->
      <div v-if="settings.showLogo" class="menu-logo">
        <img v-if="settings.logoImage" :src="settings.logoImage" :style="logoStyles" alt="Logo" />
        <div v-else class="logo-placeholder">Logo</div>
      </div>
      
      <!-- Menu Items -->
      <ul class="menu-items" :style="menuItemsStyles">
        <li class="menu-item" v-for="i in 5" :key="i">
          <a href="#" class="menu-link" :style="linkStyles">Menu Item {{ i }}</a>
        </li>
      </ul>
      
      <!-- Mobile Toggle -->
      <button class="mobile-toggle">
        <MenuIcon />
      </button>
    </div>
  </nav>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Menu as MenuIcon } from 'lucide-vue-next'
import { 
  getSpacingStyles, 
  getBackgroundStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles,
  getTypographyStyles,
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

const containerStyles = computed(() => {
  const styles = { width: '100%' }
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

const logoStyles = computed(() => ({
  maxHeight: `${getResponsiveValue(settings.value, 'logoMaxHeight', device.value) || 60}px`,
  width: 'auto'
}))

const menuItemsStyles = computed(() => {
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'center'
  const spacing = getResponsiveValue(settings.value, 'itemSpacing', device.value) || 24
  
  return {
    justifyContent: alignment === 'right' ? 'flex-end' : 
                    alignment === 'center' ? 'center' : 'flex-start',
    gap: `${spacing}px`,
    display: 'flex',
    listStyle: 'none',
    margin: 0,
    padding: 0,
    flex: 1
  }
})

const linkStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'menu_', device.value)
  return {
    ...styles,
    display: 'block',
    padding: '12px 0',
    textDecoration: 'none'
  }
})
</script>

<style scoped>
.fullwidth-menu-block { width: 100%; }
.menu-inner {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
}
.menu-logo img { display: block; }
.logo-placeholder {
  font-size: 24px;
  font-weight: 700;
  color: #333;
}
.menu-item { position: relative; }
.menu-link { transition: color 0.2s; }
.menu-link:hover { color: #2059ea !important; }
.mobile-toggle {
  display: none;
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
}
@media (max-width: 980px) {
  .menu-items { display: none !important; }
  .mobile-toggle { display: block; }
}
</style>
