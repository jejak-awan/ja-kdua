<template>
  <BlockRenderer 
    v-if="builderData" 
    :blocks="builderData"
    :is-preview="true"
    :context="{ 
        page: page, 
        post: currentContent, 
        product: currentContent && currentContent.type === 'product' ? currentContent : null 
    }"
    class="theme-builder-content"
  />
  
  <component 
    v-else-if="resolvedComponent" 
    :is="resolvedComponent" 
    v-bind="$attrs" 
  />
  
  <div v-else class="flex items-center justify-center min-h-[50vh]">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
  </div>
</template>

<script setup>
import { shallowRef, watch, defineAsyncComponent, markRaw, computed } from 'vue'
import { useTheme } from '@/composables/useTheme'
import { useThemeBuilderStore } from '@/stores/themeBuilder'
import { useContentStore } from '@/stores/content'
import { storeToRefs } from 'pinia'
import BlockRenderer from '@/components/builder/blocks/BlockRenderer.vue'

const props = defineProps({
  page: {
    type: String,
    required: true
  }
})

const { activeTheme } = useTheme()
const themeBuilderStore = useThemeBuilderStore()
const { currentTemplate } = storeToRefs(themeBuilderStore)
const contentStore = useContentStore()
const { currentContent } = storeToRefs(contentStore)
const resolvedComponent = shallowRef(null)

// Glob all theme views
const viewModules = import.meta.glob('../views/themes/**/*.vue')

// Determine if we should render Builder content
const builderData = computed(() => {
    if (!currentTemplate.value) return null;
    
    // Header
    if (props.page === 'components/Header' && currentTemplate.value.header_data) {
        return currentTemplate.value.header_data;
    }
    
    // Footer
    if (props.page === 'components/Footer' && currentTemplate.value.footer_data) {
        return currentTemplate.value.footer_data;
    }
    
    // Body (Content)
    // List of page types that represent the "Main Content" area
    const bodyPages = ['Post', 'Page', 'Home', 'Search', 'Archive', '404', 'About', 'Contact', 'Blog'];
    if (bodyPages.includes(props.page) && currentTemplate.value.body_data) {
        return currentTemplate.value.body_data;
    }
    
    return null;
});

async function resolveView() {
  // If builder data is present, we don't need to resolve the file view
  if (builderData.value) {
      resolvedComponent.value = null;
      return;
  }

  const themeSlug = activeTheme.value?.slug || 'default'
  const pageName = props.page
  
  // 1. Try Active Theme
  const targetPath = `../views/themes/${themeSlug}/${pageName}.vue`
  
  // 2. Fallback to Default Theme
  const fallbackPath = `../views/themes/default/${pageName}.vue`
  
  let loader = viewModules[targetPath]
  
  if (!loader && themeSlug !== 'default') {
    loader = viewModules[fallbackPath]
  }
  
  if (!loader) {
    if (pageName !== 'components/Header' && pageName !== 'components/Footer') {
         console.warn(`[ThemeResolver] View '${pageName}' not found in default theme.`)
    }
    return
  }
  
  try {
    const mod = await loader()
    resolvedComponent.value = markRaw(mod.default)
  } catch (err) {
    console.error(`[ThemeResolver] Failed to load view '${pageName}'`, err)
  }
}

// Re-resolve when theme changes, page prop changes, OR builderData availability changes
// Actually, if builderData changes (e.g. template loads late), we need to update.
watch([() => activeTheme.value?.slug, () => props.page, builderData], resolveView, { immediate: true })
</script>
