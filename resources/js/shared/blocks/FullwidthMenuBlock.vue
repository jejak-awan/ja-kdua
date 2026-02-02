<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    tag="nav"
    class="fullwidth-menu-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Fullwidth Menu'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div class="menu-inner max-w-[1400px] mx-auto px-6 flex items-center justify-between transition-colors duration-300">
        <!-- Logo -->
        <div v-if="blockSettings.showLogo || mode === 'edit'" class="menu-logo flex-shrink-0">
          <img v-if="blockSettings.logoImage" :src="blockSettings.logoImage" :style="logoStyles" :alt="blockSettings.logoText || 'Logo'" />
          <div 
            v-else 
            class="logo-placeholder text-2xl font-bold transition-colors duration-300"
            :contenteditable="mode === 'edit'"
            @blur="updateText('logoText', $event)"
          >
{{ blockSettings.logoText || 'Logo' }}
</div>
        </div>
        
        <!-- Menu Items -->
        <ul class="menu-items flex list-none m-0 p-0 transition-colors duration-300" :style="menuItemsStyles">
          <li class="menu-item relative group" v-for="item in menuItems" :key="item.id">
            <a 
              :href="mode === 'view' ? (item.url || undefined) : undefined" 
              class="menu-link block px-4 py-3 no-underline transition-colors duration-300 whitespace-nowrap" 
              :style="linkStyles"
              @click.prevent="handleLinkClick(item.url)"
            >
              {{ item.label }}
            </a>
          </li>
        </ul>
        
        <!-- Mobile Toggle -->
        <button class="mobile-toggle block lg:hidden p-2 transition-colors duration-300 bg-transparent border-0 cursor-pointer" @click="mobileMenuOpen = !mobileMenuOpen">
          <MenuIcon class="w-6 h-6" />
        </button>
      </div>
      
      <!-- Mobile Menu Overlay -->
      <transition 
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-12"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-12"
      >
        <div v-if="mobileMenuOpen" class="mobile-menu-overlay fixed inset-0 z-[100] bg-black/50 flex justify-end" @click="mobileMenuOpen = false">
            <ul class="mobile-menu-list w-[280px] h-full bg-white dark:bg-gray-900 p-10 list-none shadow-2xl overflow-y-auto" @click.stop>
                <li v-for="item in menuItems" :key="item.id" class="border-b border-gray-100 dark:border-gray-800">
                    <a :href="item.url" class="mobile-menu-link block py-4 text-gray-900 dark:text-gray-100 no-underline font-medium hover:text-primary transition-colors" :style="linkStyles">{{ item.label }}</a>
                </li>
            </ul>
        </div>
      </transition>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import MenuIcon from 'lucide-vue-next/dist/esm/icons/menu.js';import { 
    getVal,
    getTypographyStyles,
    getLayoutStyles,
    getResponsiveValue 
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)
const mobileMenuOpen = ref(false)

const updateText = (key: string, event: FocusEvent) => {
    if (props.mode !== 'edit' || !event.target) return
    const newValue = (event.target as HTMLElement).innerText;
    (window as any).builder?.updateModuleSettings(props.module.id, { [key]: newValue });
}

const handleLinkClick = (url: string) => {
    if (props.mode === 'edit') return
    if (url) window.location.href = url
}

const menuItems = computed(() => {
    if (props.module.children && props.module.children.length > 0) {
        return props.module.children.map(child => ({
            id: child.id,
            label: (child.settings?.label as string) || 'Item',
            url: (child.settings?.url as string) || '#'
        }))
    }
    return [
        { id: 1, label: 'Home', url: '/' },
        { id: 2, label: 'About', url: '/about' },
        { id: 3, label: 'Services', url: '/services' },
        { id: 4, label: 'Contact', url: '/contact' }
    ]
})

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const logoStyles = computed(() => ({
  maxHeight: `${getResponsiveValue(settings.value, 'logoMaxHeight', props.device) || 60}px`,
  width: 'auto'
}))

const menuItemsStyles = computed(() => {
  const layoutStyles = getLayoutStyles(settings.value, props.device)
  const alignment = getResponsiveValue(settings.value, 'alignment', props.device) || 'center'
  const spacing = getResponsiveValue(settings.value, 'itemSpacing', props.device) || 24
  
  return {
    ...layoutStyles,
    justifyContent: alignment === 'right' ? 'flex-end' : 
                    alignment === 'center' ? 'center' : 'flex-start',
    gap: `${spacing}px`,
    flex: 1
  }
})

const linkStyles = computed(() => getTypographyStyles(settings.value, 'menu_', props.device))
</script>

<style scoped>
.fullwidth-menu-block { width: 100%; }
.fullwidth-menu-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.fullwidth-menu-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.menu-link { 
  transition: opacity 0.3s ease, color 0.3s ease; 
  opacity: 1;
}
.menu-link:hover { opacity: 0.7; }

@media (max-width: 980px) {
  .menu-items { display: none !important; }
}
[contenteditable]:focus {
  outline: none;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>
