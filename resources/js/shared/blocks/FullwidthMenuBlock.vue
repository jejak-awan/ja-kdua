<template>
  <BaseBlock :module="module" :settings="settings" tag="nav" class="fullwidth-menu-block" :style="containerStyles">
    <div class="menu-inner">
      <!-- Logo -->
      <div v-if="settings.showLogo || mode === 'edit'" class="menu-logo">
        <img v-if="settings.logoImage" :src="settings.logoImage" :style="logoStyles" alt="Logo" />
        <div 
          v-else 
          class="logo-placeholder"
          :contenteditable="mode === 'edit'"
          @blur="updateText('logoText', $event)"
        >{{ settings.logoText || 'Logo' }}</div>
      </div>
      
      <!-- Menu Items -->
      <ul class="menu-items" :style="menuItemsStyles">
        <li class="menu-item" v-for="item in menuItems" :key="item.id">
          <a 
            :href="mode === 'view' ? item.url : null" 
            class="menu-link" 
            :style="linkStyles"
            @click.prevent="handleLinkClick(item.url)"
          >{{ item.label }}</a>
        </li>
      </ul>
      
      <!-- Mobile Toggle -->
      <button class="mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen">
        <MenuIcon />
      </button>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div v-if="mobileMenuOpen" class="mobile-menu-overlay" @click="mobileMenuOpen = false">
        <ul class="mobile-menu-list" @click.stop>
            <li v-for="item in menuItems" :key="item.id">
                <a :href="item.url" class="mobile-menu-link" :style="linkStyles">{{ item.label }}</a>
            </li>
        </ul>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Menu as MenuIcon } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const mobileMenuOpen = ref(false)

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    const value = event.target.innerText
    builder?.updateModuleSettings(props.module.id, { [key]: value })
}

const handleLinkClick = (url) => {
    if (props.mode === 'edit') return
    if (url) window.location.href = url
}

// Mock or dynamic items
const menuItems = computed(() => {
    if (props.module.children?.length > 0) {
        return props.module.children.map(child => ({
            id: child.id,
            label: child.settings?.label || 'Item',
            url: child.settings?.url || '#'
        }))
    }
    return [
        { id: 1, label: 'Home', url: '/' },
        { id: 2, label: 'About', url: '/about' },
        { id: 3, label: 'Services', url: '/services' },
        { id: 4, label: 'Contact', url: '/contact' }
    ]
})

const containerStyles = computed(() => {
  return { width: '100%' }
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
  color: inherit;
}
.menu-item { position: relative; }
.menu-link { transition: opacity 0.2s; }
.menu-link:hover { opacity: 0.7; }
.mobile-toggle {
  display: none;
  background: none;
  border: none;
  padding: 8px;
  cursor: pointer;
  color: inherit;
}

.mobile-menu-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100;
    display: flex;
    justify-content: flex-end;
}
.mobile-menu-list {
    width: 280px;
    height: 100%;
    background: white;
    padding: 40px 20px;
    list-style: none;
}
.mobile-menu-link {
    display: block;
    padding: 12px 0;
    border-bottom: 1px solid #efefef;
    color: #333 !important;
}

@media (max-width: 980px) {
  .menu-items { display: none !important; }
  .mobile-toggle { display: block; }
}
[contenteditable]:focus {
  outline: none;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>
