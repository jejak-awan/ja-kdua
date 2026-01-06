<template>
  <div class="frontend-layout">
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
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useTheme } from '@/composables/useTheme'
import { useContentStore } from '@/stores/content'
import ThemePageResolver from '@/components/ThemePageResolver.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const route = useRoute()
const { activeTheme, loadActiveTheme } = useTheme()
const contentStore = useContentStore()

// Hide breadcrumbs on homepage
const isHomePage = computed(() => route.path === '/')

const handleRouteChange = () => {
    // Fetch Content if available (for dynamic pages)
    if (route.params.slug) {
        contentStore.fetchContent(route.params.slug)
    } else {
        contentStore.clearContent()
    }
}

onMounted(async () => {
  if (!activeTheme.value) {
    await loadActiveTheme()
  }
  handleRouteChange()
})

watch(() => route.path, handleRouteChange)
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
