<template>
  <div class="frontend-layout">
    <!-- Theme Header (Dynamic or Default) -->
    <component :is="DynamicHeader || ThemeHeaderDefault" />
    
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
    
    <!-- Theme Footer (Dynamic or Default) -->
    <component :is="DynamicFooter || ThemeFooterDefault" />
  </div>
</template>

<script setup>
import { onMounted, watch, computed, ref, markRaw, defineAsyncComponent } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@vueuse/head'
import { useI18n } from 'vue-i18n'
import { useTheme } from '@/composables/useTheme'
import { useThemeComponents } from '@/composables/useThemeComponents'
import ThemeHeaderDefault from '@/components/theme/ThemeHeader.vue'
import ThemeFooterDefault from '@/components/theme/ThemeFooter.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const { t } = useI18n()
const route = useRoute()
const { activeTheme, loadActiveTheme } = useTheme()
const { loadComponent } = useThemeComponents()

const DynamicHeader = ref(null)
const DynamicFooter = ref(null)

// Hide breadcrumbs on homepage
const isHomePage = computed(() => route.path === '/')

// Load active theme and its specific components reactively
watch(activeTheme, async (newTheme) => {
  if (newTheme) {
    console.log('Active theme detected in Layout, loading Header/Footer')
    try {
      const [header, footer] = await Promise.all([
        loadComponent('headers', 'default'),
        loadComponent('footers', 'default')
      ])
      
      if (header) DynamicHeader.value = markRaw(header)
      if (footer) DynamicFooter.value = markRaw(footer)
    } catch (err) {
      console.error('Failed to load Layout components:', err)
    }
  }
}, { immediate: true })

onMounted(async () => {
  if (!activeTheme.value) {
    await loadActiveTheme()
  }
})

// Update page title and meta tags on route change
watch(() => route.meta, (meta) => {
  useHead({
    title: meta.title ? `${meta.title} - ${activeTheme.value?.name || t('features.frontend.home.title')}` : activeTheme.value?.name || t('features.frontend.home.title'),
    meta: [
      { name: 'description', content: meta.description || activeTheme.value?.description || t('features.frontend.home.subtitle') },
      { property: 'og:title', content: meta.title || activeTheme.value?.name || t('features.frontend.home.title') },
      { property: 'og:description', content: meta.description || activeTheme.value?.description || t('features.frontend.home.subtitle') },
    ]
  })
}, { immediate: true })
</script>

<style scoped>
.frontend-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
}

/* Page transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
