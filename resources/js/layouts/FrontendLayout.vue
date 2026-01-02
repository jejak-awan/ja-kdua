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
import { useThemeBuilderStore } from '@/stores/themeBuilder'
import { useContentStore } from '@/stores/content'
import ThemePageResolver from '@/components/ThemePageResolver.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'

const route = useRoute()
const { activeTheme, loadActiveTheme } = useTheme()
const themeBuilderStore = useThemeBuilderStore()
const contentStore = useContentStore()

// Hide breadcrumbs on homepage
const isHomePage = computed(() => route.path === '/')

const resolveCurrentTemplate = () => {
    // 1. Fetch Content if available (for Dynamic Blocks)
    if (route.params.slug) {
        contentStore.fetchContent(route.params.slug)
    } else {
        // If homepage, maybe fetch homepage content?
        // contentStore.fetchContent('home') ??
        contentStore.clearContent()
    }

    // 2. Resolve Theme Template
    const templateContext = {
        url: route.path,
        route_name: route.name,
        is_home: route.path === '/',
        is_404: route.name === 'NotFound' || route.name === '404',
        is_category: !!route.params.categorySlug || route.name === 'Category',
        is_archive: route.name?.includes('Archive') || route.name === 'Blog',
    }

    // Add category ID if we have it in the route params (some setups store ID)
    if (route.params.categoryId) {
        templateContext.category_id = route.params.categoryId;
    }

    // If we have a post type in route meta, pass it
    if (route.meta?.postType) {
        templateContext.post_type = route.meta.postType;
    }

    themeBuilderStore.resolveTemplate(templateContext)
}

onMounted(async () => {
  if (!activeTheme.value) {
    await loadActiveTheme()
  }
  resolveCurrentTemplate()
})

watch(() => route.path, resolveCurrentTemplate)
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
