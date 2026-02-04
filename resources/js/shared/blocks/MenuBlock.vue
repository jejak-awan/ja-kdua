<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="menu-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Main Navigation'"
  >
    <nav 
        class="menu-nav flex items-center w-full transition-colors duration-300" 
        :class="[
            alignment === 'center' ? 'justify-center' : (alignment === 'right' ? 'justify-end' : 'justify-start'),
            menuStyle === 'vertical' ? 'flex-col' : 'flex-row'
        ]"
        :style="(containerStyles as any)"
    >
      <!-- Logo Section -->
      <div v-if="showLogo && settings.logoImage" class="menu-logo flex-shrink-0 mr-10" :class="logoPosition === 'right' ? 'order-last ml-10 mr-0' : ''">
        <img :src="(settings.logoImage as string)" alt="Logo" class="logo-image h-12 w-auto object-contain transition-transform duration-300 hover:scale-105" />
      </div>
      
      <!-- Desktop Menu -->
      <ul 
        class="menu-items hidden md:flex list-none p-0 m-0 transition-colors duration-300" 
        :class="menuStyle === 'vertical' ? 'flex-col gap-6' : 'flex-row gap-10'"
        :style="(menuStyles as any)"
      >
        <li v-for="item in menuItems" :key="item" class="menu-item group/item">
          <a 
            href="#" 
            class="menu-link block transition-colors duration-300 font-bold tracking-tight relative pb-1" 
            :style="(linkStyles as any)"
            @click.prevent
          >
            {{ item }}
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary transition-colors duration-300 group-hover/item:w-full"></span>
          </a>
        </li>
      </ul>
      
      <!-- Mobile Toggle -->
      <button 
        class="menu-toggle md:hidden p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm transition-[background-color,transform] active:scale-95" 
        @click="mobileOpen = !mobileOpen"
        aria-label="Toggle Menu"
      >
        <MenuIcon class="w-6 h-6 text-slate-600 dark:text-slate-400" />
      </button>
      
      <!-- Mobile Menu Overlay -->
      <div v-if="mobileOpen" class="mobile-menu-overlay fixed inset-0 z-[100] bg-white/95 dark:bg-slate-950/95 backdrop-blur-xl md:hidden p-12 transition-[width] duration-500">
        <button class="absolute top-10 right-10 p-3 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" @click="mobileOpen = false">
            <X class="w-10 h-10 text-slate-800 dark:text-white" />
        </button>
        <ul class="flex flex-col gap-8 mt-20 text-center text-3xl font-black tracking-tighter">
            <li v-for="(item, index) in menuItems" :key="item" :style="{ animationDelay: `${index * 100}ms` }" class="animate-in fade-in slide-in-from-bottom-4 duration-500 fill-mode-both">
                <a 
                    href="#" 
                    class="text-slate-900 dark:text-white hover:text-primary transition-colors"
                    @click="mobileOpen = false"
                >
                    {{ item }}
                </a>
            </li>
        </ul>
      </div>
    </nav>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import MenuIcon from 'lucide-vue-next/dist/esm/icons/menu.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<BuilderInstance | null>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const mobileOpen = ref(false)
const menuItems = ['Home', 'Products', 'Services', 'Insights', 'Company']

const showLogo = computed(() => getVal<boolean>(settings.value, 'showLogo', device.value))
const logoPosition = computed(() => getVal<string>(settings.value, 'logoPosition', device.value) || 'left')
const menuStyle = computed(() => getVal<string>(settings.value, 'style', device.value) || 'horizontal')
const alignment = computed(() => getVal<string>(settings.value, 'alignment', device.value) || 'left')

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, device.value)
})

const menuStyles = computed(() => {
  const bgColor = getVal<string>(settings.value, 'menuBackgroundColor', device.value) || 'transparent'
  const styles: Record<string, string | number> = { 
    backgroundColor: bgColor 
  }
  return styles
})

const linkStyles = computed(() => {
  const styles = (getTypographyStyles(settings.value, 'menu_', device.value) || {}) as Record<string, string | number>
  return styles
})
</script>

<style scoped>
.menu-block { width: 100%; }
.mobile-menu-overlay { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes fadeIn {
  from { opacity: 0; backdrop-filter: blur(0); }
  to { opacity: 1; backdrop-filter: blur(12px); }
}

.animate-in {
    animation-name: enter;
}

@keyframes enter {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

