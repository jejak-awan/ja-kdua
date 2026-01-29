<template>
  <!-- Default Theme Component -->
  <component 
    v-if="resolvedComponent" 
    :is="resolvedComponent" 
    v-bind="$attrs" 
  />
  
  <!-- Loading State - Only show if actually loading and not empty -->
  <div v-else-if="isLoading && !isNotFound" class="flex items-center justify-center min-h-[50vh]">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
  </div>
</template>

<script setup lang="ts">
import { shallowRef, watch, markRaw, ref, type Component } from 'vue'
import { useTheme } from '@/composables/useTheme'

const props = defineProps<{
  page: string
}>()

const { activeTheme } = useTheme()
const resolvedComponent = shallowRef<Component | null>(null)
const isLoading = ref(true)
const isNotFound = ref(false)

// Glob all theme views
const viewModules = import.meta.glob('../../views/themes/**/*.vue') as Record<string, () => Promise<{ default: Component }>>

async function resolveView() {
  isLoading.value = true
  isNotFound.value = false
  resolvedComponent.value = null

  const themeSlug = activeTheme.value?.slug || 'janari'
  const pageName = props.page
  
  const targetPath = `../../views/themes/${themeSlug}/${pageName}.vue`
  const fallbackPath = `../../views/themes/janari/${pageName}.vue`
  let loader = viewModules[targetPath]
  
  if (!loader && themeSlug !== 'janari') {
    loader = viewModules[fallbackPath]
  }
  
  if (!loader) {
    console.warn(`[ThemeResolver] View '${pageName}' not found. Tried:`, { targetPath, fallbackPath, themeSlug });
    if (pageName !== 'components/Header' && pageName !== 'components/Footer') {
         console.warn(`[ThemeResolver] View '${pageName}' not found in theme.`)
    }
    isNotFound.value = true
    isLoading.value = false
    return
  }
  
  try {
    console.log(`[ThemeResolver] Loading component: ${pageName} from theme ${themeSlug}`);
    const mod = await loader()
    resolvedComponent.value = markRaw(mod.default)
    console.log(`[ThemeResolver] Successfully resolved: ${pageName}`);
  } catch (err) {
    console.error(`[ThemeResolver] Failed to load view '${pageName}'`, err)
    isNotFound.value = true // Treat load error as not found to avoid infinite spinner
  } finally {
    isLoading.value = false
  }
}

// Re-resolve when theme or page changes
watch([() => activeTheme.value?.slug, () => props.page], resolveView, { immediate: true })
</script>
