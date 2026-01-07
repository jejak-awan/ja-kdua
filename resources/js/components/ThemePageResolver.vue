<template>
  <!-- Default Theme Component -->
  <component 
    v-if="resolvedComponent" 
    :is="resolvedComponent" 
    v-bind="$attrs" 
  />
  
  <!-- Loading State -->
  <div v-else class="flex items-center justify-center min-h-[50vh]">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
  </div>
</template>

<script setup>
import { shallowRef, watch, markRaw } from 'vue'
import { useTheme } from '@/composables/useTheme'

const props = defineProps({
  page: {
    type: String,
    required: true
  }
})

const { activeTheme } = useTheme()
const resolvedComponent = shallowRef(null)

// Glob all theme views
const viewModules = import.meta.glob('../views/themes/**/*.vue')

async function resolveView() {
  const themeSlug = activeTheme.value?.slug || 'janari'
  const pageName = props.page
  
  // 1. Try Active Theme
  const targetPath = `../views/themes/${themeSlug}/${pageName}.vue`
  
  // 2. Fallback to Janari Theme
  const fallbackPath = `../views/themes/janari/${pageName}.vue`
  
  let loader = viewModules[targetPath]
  
  if (!loader && themeSlug !== 'janari') {
    loader = viewModules[fallbackPath]
  }
  
  if (!loader) {
    if (pageName !== 'components/Header' && pageName !== 'components/Footer') {
         console.warn(`[ThemeResolver] View '${pageName}' not found in theme.`)
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

// Re-resolve when theme or page changes
watch([() => activeTheme.value?.slug, () => props.page], resolveView, { immediate: true })
</script>
