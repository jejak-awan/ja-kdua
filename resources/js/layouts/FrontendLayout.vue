<template>
  <div 
    class="frontend-layout"
    :class="layoutWrapperClasses"
    :style="layoutWrapperStyles"
  >
    <!-- Layout Container for Boxed/Wide/Framed modes -->
    <div 
      class="layout-container"
      :class="layoutContainerClasses"
      :style="layoutContainerStyles"
    >
      <!-- Theme Header -->
      <ThemePageResolver page="components/Header" />
      
      <!-- Breadcrumbs -->
      <Breadcrumbs v-if="!isHomePage" />
      
      <!-- Main Content -->
      <main class="main-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
      
      <!-- Theme Footer -->
      <ThemePageResolver page="components/Footer" />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import { useContentStore } from '@/stores/content'
import ThemePageResolver from '@/components/ThemePageResolver.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const route = useRoute()
const { activeTheme, loadActiveTheme, getSetting } = useTheme()
const contentStore = useContentStore()

// Hide breadcrumbs on homepage
const isHomePage = computed(() => route.path === '/')

// Layout settings from theme customizer
const layoutStyle = computed(() => getSetting('layout_style', 'full'))
const containerMaxWidth = computed(() => getSetting('container_max_width', 1400))
const boxedBgColor = computed(() => getSetting('boxed_bg_color', '#f1f5f9'))
const boxedShadow = computed(() => getSetting('boxed_shadow', 'lg'))
const framedPadding = computed(() => getSetting('framed_padding', 16))

// Wrapper classes (outer container - for background)
const layoutWrapperClasses = computed(() => {
  const style = layoutStyle.value
  return {
    'layout-full': style === 'full',
    'layout-boxed': style === 'boxed',
    'layout-wide': style === 'wide',
    'layout-framed': style === 'framed',
  }
})

// Wrapper styles (background color for boxed modes)
const layoutWrapperStyles = computed(() => {
  const style = layoutStyle.value
  if (style === 'boxed' || style === 'wide' || style === 'framed') {
    return {
      backgroundColor: boxedBgColor.value,
    }
  }
  return {}
})

// Container classes (inner content container)
const layoutContainerClasses = computed(() => {
  const style = layoutStyle.value
  const shadow = boxedShadow.value
  
  const classes = []
  
  if (style !== 'full') {
    classes.push('layout-container-constrained')
    
    // Shadow classes
    if (shadow && shadow !== 'none') {
      classes.push(`shadow-${shadow}`)
    }
  }
  
  return classes
})

// Container styles (max-width and padding)
const layoutContainerStyles = computed(() => {
  const style = layoutStyle.value
  
  if (style === 'full') {
    return {}
  }
  
  const styles = {
    maxWidth: `${containerMaxWidth.value}px`,
    margin: '0 auto',
  }
  
  if (style === 'framed') {
    styles.margin = `${framedPadding.value}px auto`
    styles.borderRadius = '12px'
  }
  
  return styles
})

onMounted(async () => {
  if (!activeTheme.value) {
    await loadActiveTheme()
  }
})
</script>

<style scoped>
.frontend-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Full width - default */
.layout-full .layout-container {
  width: 100%;
}

/* Boxed/Wide/Framed modes */
.layout-boxed,
.layout-wide,
.layout-framed {
  min-height: 100vh; /* Background color is applied via inline styles */
}

.layout-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  width: 100%;
}

.layout-container-constrained {
  background-color: hsl(var(--background));
}

/* Shadow utilities */
.shadow-sm {
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}
.shadow-md {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}
.shadow-lg {
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
}
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

.main-content {
  flex: 1;
}

/* Page transition - smoother fade */
.fade-enter-active {
  transition: opacity 0.2s ease-out;
}

.fade-leave-active {
  transition: opacity 0.15s ease-in;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive adjustments for boxed layouts */
@media (max-width: 1024px) {
  .layout-container-constrained {
    max-width: 100% !important;
    margin: 0 !important;
    border-radius: 0 !important;
  }
  
  .layout-boxed,
  .layout-wide,
  .layout-framed {
    background-color: transparent !important;
  }
}
</style>
