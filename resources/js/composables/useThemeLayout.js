import { ref, computed, watch } from 'vue'
import { useTheme } from './useTheme'
import { useThemeComponents } from './useThemeComponents'

/**
 * Composable for theme layout management
 */
export function useThemeLayout() {
  const { themeSettings } = useTheme()
  const { loadComponent } = useThemeComponents()
  
  const currentLayout = ref(null)
  const layoutLoading = ref(false)
  const layoutError = ref(null)

  // Get default layout from theme settings
  const defaultLayout = computed(() => {
    return themeSettings.value?.default_layout || 'default'
  })

  /**
   * Load layout component
   */
  const loadLayout = async (layoutName = null) => {
    layoutLoading.value = true
    layoutError.value = null
    
    try {
      const layout = layoutName || defaultLayout.value
      const component = await loadComponent('layouts', layout)
      
      if (component) {
        currentLayout.value = component
      } else {
        throw new Error(`Layout not found: ${layout}`)
      }
    } catch (err) {
      layoutError.value = err.message
      console.error('Failed to load layout:', err)
      
      // Try to load default layout as fallback
      if (layoutName && layoutName !== 'default') {
        console.warn('Falling back to default layout')
        await loadLayout('default')
      }
    } finally {
      layoutLoading.value = false
    }
  }

  /**
   * Switch to different layout
   */
  const switchLayout = async (layoutName) => {
    await loadLayout(layoutName)
  }

  /**
   * Get available layouts from theme manifest
   */
  const getAvailableLayouts = () => {
    const { activeTheme } = useTheme()
    const manifest = activeTheme.value?.manifest || {}
    return Object.keys(manifest.components?.layouts || {})
  }

  // Auto-load default layout when theme changes
  watch(
    () => themeSettings.value,
    () => {
      if (!currentLayout.value) {
        loadLayout()
      }
    },
    { immediate: true }
  )

  return {
    // State
    currentLayout,
    layoutLoading,
    layoutError,
    defaultLayout,
    
    // Methods
    loadLayout,
    switchLayout,
    getAvailableLayouts,
  }
}

