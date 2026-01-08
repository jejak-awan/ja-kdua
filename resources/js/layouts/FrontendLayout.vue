<template>
  <div 
    class="frontend-layout min-h-screen flex flex-col"
    :class="rootClasses"
    :style="rootStyles"
  >
    <!-- CASE 1: FULL WIDTH (Default) & HYBRID -->
    <!-- For Hybrid: Header/Footer are here (full), Main is constrained below -->
    <!-- For Full: Everything is here -->
    <template v-if="layoutStyle === 'full' || layoutStyle === 'hybrid'">
      <ThemePageResolver page="components/Header" />
      
      <!-- Main Content -->
      <!-- Hybrid: Main is boxed | Full: Main is full -->
      <main class="main-content flex-1 w-full">
         <div :class="{
           'container mx-auto': layoutStyle === 'hybrid',
           'px-6 md:px-12 lg:px-20': layoutStyle === 'hybrid', // Increased padding
           'w-full': layoutStyle === 'full'
         }" :style="hybridContentStyles">
            <Breadcrumbs v-if="!isHomePage" :class="{'mb-6': layoutStyle === 'hybrid'}" />
            <router-view v-slot="{ Component }">
              <transition name="fade" mode="out-in">
                <component :is="Component" />
              </transition>
            </router-view>
         </div>
      </main>

      <div class="mt-auto">
        <ThemePageResolver page="components/Footer" />
      </div>
    </template>


    <!-- CASE 2: BOXED, WIDE, FRAMED -->
    <!-- Everything wraps inside a container -->
    <div 
      v-else
      class="layout-wrapper mx-auto flex flex-col min-h-screen bg-background shadow-xl"
      :class="wrapperClasses"
      :style="wrapperStyles"
    >
      <ThemePageResolver page="components/Header" />
      
      <main class="main-content flex-1 px-6 md:px-12 lg:px-16 py-8"> <!-- Added padding here too -->
         <Breadcrumbs v-if="!isHomePage" class="mb-6" />
         <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
      </main>

      <div class="mt-auto">
        <ThemePageResolver page="components/Footer" />
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import ThemePageResolver from '@/components/ThemePageResolver.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const route = useRoute()
const { activeTheme, loadActiveTheme, getSetting } = useTheme()

// Hide breadcrumbs on homepage
const isHomePage = computed(() => route.path === '/')

// Layout settings
const layoutStyle = computed(() => getSetting('layout_style', 'full'))
const containerMaxWidth = computed(() => getSetting('container_max_width', 1400))
const boxedBgColor = computed(() => getSetting('boxed_bg_color', '#f1f5f9'))
const boxedShadow = computed(() => getSetting('boxed_shadow', 'lg'))
const framedPadding = computed(() => getSetting('framed_padding', 16))

// ROOT CLASSES (Outer most div)
const rootClasses = computed(() => {
  const style = layoutStyle.value
  return {
    'bg-page-background': style === 'boxed' || style === 'wide' || style === 'framed',
    'p-4 md:p-8': style === 'framed', // Visible padding for framed
  }
})

const rootStyles = computed(() => {
  const style = layoutStyle.value
  if (style === 'boxed' || style === 'wide' || style === 'framed') {
    return { backgroundColor: boxedBgColor.value }
  }
  return {}
})

// wrapper classes for Boxed/Wide/Framed
const wrapperClasses = computed(() => {
  const shadow = boxedShadow.value
  return {
    'rounded-xl overflow-hidden': layoutStyle.value === 'framed',
    [`shadow-${shadow}`]: shadow !== 'none'
  }
})

const wrapperStyles = computed(() => {
  if (['boxed', 'wide', 'framed'].includes(layoutStyle.value)) {
    return { 
      maxWidth: `${containerMaxWidth.value}px`,
      width: '100%' 
    }
  }
  return {}
})

const hybridContentStyles = computed(() => {
  if (layoutStyle.value === 'hybrid') {
    return { maxWidth: `${containerMaxWidth.value}px` }
  }
  return {}
})

onMounted(async () => {
  if (!activeTheme.value) {
    await loadActiveTheme()
  }
})
</script>

<style scoped>
/* Transition Utility */
.fade-enter-active {
  transition: opacity 0.2s ease-out;
}
.fade-leave-active {
  transition: opacity 0.15s ease-in;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Shadow utilities re-implementation because tailwind classes might be purged if dynamic */
.shadow-sm { box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); }
.shadow-md { box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
.shadow-lg { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
.shadow-xl { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
</style>
