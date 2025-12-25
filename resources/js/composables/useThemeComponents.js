import { ref, markRaw } from 'vue'
import { useTheme } from './useTheme'

/**
 * Composable for dynamic theme component loading
 */
export function useThemeComponents() {
  const { activeTheme } = useTheme()
  const loadedComponents = ref({})
  const loading = ref(false)
  const error = ref(null)

  /**
   * Load theme component dynamically
   */
  const loadComponent = async (type, name = 'default') => {
    const key = `${type}.${name}`

    // Return cached component
    if (loadedComponents.value[key]) {
      return loadedComponents.value[key]
    }

    loading.value = true
    error.value = null

    try {
      if (!activeTheme.value) {
        throw new Error('No active theme')
      }

      const manifest = activeTheme.value.manifest || {}
      const componentPath = manifest.components?.[type]?.[name]?.path

      if (!componentPath) {
        console.warn(`Component not found in manifest: ${type}.${name}`)
        return null
      }

      // Construct full path
      const themeSlug = activeTheme.value.slug
      const fullPath = `/themes/${themeSlug}/${componentPath}`

      // Dynamic import
      const module = await import(/* @vite-ignore */ fullPath)

      // Cache the component (use markRaw to avoid reactivity overhead)
      loadedComponents.value[key] = markRaw(module.default || module)

      return loadedComponents.value[key]
    } catch (err) {
      error.value = `Failed to load component: ${type}.${name}`
      console.error(error.value, err)
      return null
    } finally {
      loading.value = false
    }
  }

  /**
   * Get component by type and name
   */
  const getComponent = (type, name = 'default') => {
    const key = `${type}.${name}`
    return loadedComponents.value[key] || null
  }

  /**
   * Check if component is loaded
   */
  const isComponentLoaded = (type, name = 'default') => {
    const key = `${type}.${name}`
    return !!loadedComponents.value[key]
  }

  /**
   * Preload commonly used components
   */
  const preloadComponents = async () => {
    const componentsToLoad = [
      ['layouts', 'default'],
      ['headers', 'default'],
      ['footers', 'default'],
    ]

    await Promise.all(
      componentsToLoad.map(([type, name]) => loadComponent(type, name))
    )
  }

  /**
   * Clear component cache
   */
  const clearCache = () => {
    loadedComponents.value = {}
  }

  return {
    // State
    loadedComponents,
    loading,
    error,

    // Methods
    loadComponent,
    getComponent,
    isComponentLoaded,
    preloadComponents,
    clearCache,
  }
}

