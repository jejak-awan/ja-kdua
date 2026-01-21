<template>
  <BaseBlock :module="module" :settings="settings" class="menu-block">
    <nav 
        class="menu-nav flex items-center w-full" 
        :class="[
            alignment === 'center' ? 'justify-center' : (alignment === 'right' ? 'justify-end' : 'justify-start'),
            menuStyle === 'vertical' ? 'flex-col' : 'flex-row'
        ]"
    >
      <!-- Logo Section -->
      <div v-if="showLogo && settings.logoImage" class="menu-logo flex-shrink-0 mr-8" :class="logoPosition === 'right' ? 'order-last ml-8 mr-0' : ''">
        <img :src="settings.logoImage" alt="Logo" class="logo-image h-10 w-auto object-contain" />
      </div>
      
      <!-- Desktop Menu -->
      <ul 
        class="menu-items hidden md:flex list-none p-0 m-0" 
        :class="menuStyle === 'vertical' ? 'flex-col gap-4' : 'flex-row gap-8'"
        :style="menuStyles"
      >
        <li v-for="item in menuItems" :key="item" class="menu-item">
          <a href="#" class="menu-link block transition-all hover:text-blue-500 font-medium" :style="linkStyles">{{ item }}</a>
        </li>
      </ul>
      
      <!-- Mobile Toggle -->
      <button class="menu-toggle md:hidden p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700" @click="mobileOpen = !mobileOpen">
        <MenuIcon class="w-6 h-6" />
      </button>
      
      <!-- Mobile Menu (Simple overlay as placeholder) -->
      <div v-if="mobileOpen" class="mobile-menu-overlay fixed inset-0 z-[100] bg-white dark:bg-gray-900 md:hidden p-10">
        <button class="absolute top-6 right-6 p-2" @click="mobileOpen = false">
            <X class="w-8 h-8" />
        </button>
        <ul class="flex flex-col gap-6 mt-12 text-center text-xl font-bold">
            <li v-for="item in menuItems" :key="item">
                <a href="#" @click="mobileOpen = false">{{ item }}</a>
            </li>
        </ul>
      </div>
    </nav>
  </BaseBlock>
</template>

<script setup>
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Menu as MenuIcon, X } from 'lucide-vue-next'
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

const mobileOpen = ref(false)
const menuItems = ['Home', 'About', 'Services', 'Portfolio', 'Contact']

const showLogo = computed(() => getResponsiveValue(settings.value, 'showLogo', device.value))
const logoPosition = computed(() => getResponsiveValue(settings.value, 'logoPosition', device.value) || 'left')
const menuStyle = computed(() => getResponsiveValue(settings.value, 'style', device.value) || 'horizontal')
const alignment = computed(() => getResponsiveValue(settings.value, 'alignment', device.value) || 'left')

const menuStyles = computed(() => {
  const bgColor = getResponsiveValue(settings.value, 'menuBackgroundColor', device.value) || 'transparent'
  return { 
    backgroundColor: bgColor 
  }
})

const linkStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'menu_', device.value)
  return styles
})
</script>

<style scoped>
.menu-block { width: 100%; }
.mobile-menu-overlay { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
