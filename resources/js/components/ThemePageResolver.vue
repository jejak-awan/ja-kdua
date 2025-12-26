<template>
  <component 
    :is="resolvedComponent" 
    v-bind="$attrs" 
    v-if="resolvedComponent"
  />
  <div v-else class="flex items-center justify-center min-h-[50vh]">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
  </div>
</template>

<script setup>
import { shallowRef, watch, defineAsyncComponent, markRaw } from 'vue'
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
// Note: This relies on Vite's import.meta.glob behavior.
// We look for .vue files in any subfolder of views/themes
const viewModules = import.meta.glob('../views/themes/**/*.vue')

async function resolveView() {
  const themeSlug = activeTheme.value?.slug || 'default'
  const pageName = props.page
  
  // 1. Try Active Theme
  const targetPath = `../views/themes/${themeSlug}/${pageName}.vue`
  
  // 2. Fallback to Default Theme
  const fallbackPath = `../views/themes/default/${pageName}.vue`
  
  let loader = viewModules[targetPath]
  
  if (!loader && themeSlug !== 'default') {
    // console.debug(`[ThemeResolver] View '${pageName}' not found in '${themeSlug}', attempting fallback.`)
    loader = viewModules[fallbackPath]
  }
  
  if (!loader) {
    console.error(`[ThemeResolver] View '${pageName}' not found in default theme.`)
    return
  }
  
  try {
    const mod = await loader()
    resolvedComponent.value = markRaw(mod.default)
  } catch (err) {
    console.error(`[ThemeResolver] Failed to load view '${pageName}'`, err)
  }
}

// Re-resolve when theme changes or page prop changes
watch([() => activeTheme.value?.slug, () => props.page], resolveView, { immediate: true })
</script>
