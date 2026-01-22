<template>
  <nav class="menu-block" :style="wrapperStyles">
    <div v-if="showLogo && settings.logoImage" class="menu-logo" :class="`menu-logo--${logoPosition}`">
      <img :src="settings.logoImage" alt="Logo" class="logo-image" />
    </div>
    <ul class="menu-items" :class="`menu-items--${menuStyle}`" :style="menuStyles">
      <li v-for="item in menuItems" :key="item" class="menu-item">
        <a href="#" class="menu-link" :style="linkStyles">{{ item }}</a>
      </li>
    </ul>
    <button class="menu-toggle" @click="mobileOpen = !mobileOpen">
      <MenuIcon class="toggle-icon" />
    </button>
  </nav>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import { Menu as MenuIcon } from 'lucide-vue-next'
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
const device = computed(() => builder?.device || 'desktop')
const mobileOpen = ref(false)
const menuItems = ['Home', 'About', 'Services', 'Portfolio', 'Contact']
const showLogo = computed(() => getResponsiveValue(settings.value, 'showLogo', device.value))
const logoPosition = computed(() => getResponsiveValue(settings.value, 'logoPosition', device.value) || 'left')
const menuStyle = computed(() => getResponsiveValue(settings.value, 'style', device.value) || 'horizontal')

const wrapperStyles = computed(() => {
  const styles = { 
    display: 'flex', 
    alignItems: 'center', 
    gap: '24px',
    width: '100%'
  }
  
  const alignment = getResponsiveValue(settings.value, 'alignment', device.value) || 'left'
  if (alignment === 'center') styles.justifyContent = 'center'
  else if (alignment === 'right') styles.justifyContent = 'flex-end'
  else styles.justifyContent = 'flex-start'

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

const menuStyles = computed(() => {
  const bgColor = getResponsiveValue(settings.value, 'menuBackgroundColor', device.value) || ''
  return { 
    backgroundColor: bgColor 
  }
})

const linkStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'menu_', device.value)
  const hoverColor = getResponsiveValue(settings.value, 'menu_colorHover', device.value) || '#2059ea'
  return {
    ...styles,
    textDecoration: 'none',
    '--hover-color': hoverColor
  }
})
</script>

<style scoped>
.menu-block { position: relative; }
.menu-items { display: flex; list-style: none; margin: 0; padding: 0; gap: 24px; }
.menu-items--vertical { flex-direction: column; gap: 12px; }
.menu-link { padding: 8px 0; transition: color 0.2s; }
.menu-link:hover { color: var(--hover-color, #2059ea); }
.logo-image { height: 40px; width: auto; }
.menu-toggle { display: none; background: none; border: none; cursor: pointer; }
.toggle-icon { width: 24px; height: 24px; }
@media (max-width: 980px) {
  .menu-items { display: none; }
  .menu-toggle { display: block; }
}
</style>
