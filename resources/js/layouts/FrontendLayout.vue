<template>
  <div class="frontend-layout">
    <!-- Theme Header -->
    <ThemeHeader />
    
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
    <ThemeFooter />
  </div>
</template>

<script setup>
import { onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@vueuse/head'
import { useTheme } from '@/composables/useTheme'
import ThemeHeader from '@/components/theme/ThemeHeader.vue'
import ThemeFooter from '@/components/theme/ThemeFooter.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const route = useRoute()
const { activeTheme, loadActiveTheme } = useTheme()

// Hide breadcrumbs on homepage
const isHomePage = computed(() => route.path === '/')

// Load active theme on mount
onMounted(async () => {
  await loadActiveTheme()
})

// Update page title and meta tags on route change
watch(() => route.meta, (meta) => {
  useHead({
    title: meta.title ? `${meta.title} - ${activeTheme.value?.name || 'JA-CMS'}` : activeTheme.value?.name || 'JA-CMS',
    meta: [
      { name: 'description', content: meta.description || activeTheme.value?.description || 'Modern Content Management System' },
      { property: 'og:title', content: meta.title || activeTheme.value?.name || 'JA-CMS' },
      { property: 'og:description', content: meta.description || activeTheme.value?.description || 'Modern Content Management System' },
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
