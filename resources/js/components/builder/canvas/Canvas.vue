<template>
  <div 
    class="canvas flex flex-col min-h-screen"
    :class="[
      { 'canvas--wireframe': wireframeMode },
      { 'canvas--grid': gridViewMode },
      `theme-${activeTheme}`
    ]"
    @click.self="clearSelection"
  >
    <CanvasGridView v-if="gridViewMode" />

    <template v-else>
      <!-- Header Preview -->
      <ThemePageResolver page="components/Header" class="relative z-10" />

      <!-- Main Content Area -->
      <main class="main-content flex-1 w-full flex flex-col">
        <!-- Empty State -->
        <div v-if="blocks.length === 0" class="canvas-empty flex-1">
          <div class="canvas-empty__content">
            <p class="canvas-empty__text">{{ $t('builder.canvas.empty', 'Start your page by adding a section.') }}</p>
            <button class="canvas-empty__btn" @click="addSection">
              <component :is="icons.Plus" :size="16" />
              {{ $t('builder.actions.addSection', 'Add New Section') }}
            </button>
          </div>
        </div>

        <!-- Modules -->
        <draggable
          v-else
          v-model="blocks"
          item-key="id"
          group="section"
          class="canvas-blocks flex-1"
          ghost-class="ja-builder-ghost"
        >
          <template #item="{ element: block, index }">
            <ModuleWrapper
              :module="block"
              :index="index"
            />
          </template>
        </draggable>
      </main>

      <!-- Footer Preview -->
      <div class="mt-auto relative z-10">
        <ThemePageResolver page="components/Footer" />
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, inject, watch, onMounted } from 'vue'
import { Plus } from 'lucide-vue-next'
import draggable from 'vuedraggable'
import ModuleWrapper from './ModuleWrapper.vue'
import CanvasGridView from './CanvasGridView.vue'
import AddModuleButton from './AddModuleButton.vue'
import ThemePageResolver from '@/components/ThemePageResolver.vue'

const icons = { Plus }

// Inject builder
const builder = inject('builder')

// Computed
const blocks = computed({
  get: () => builder?.blocks || [],
  set: (val) => { if (builder) builder.blocks = val }
})
const wireframeMode = computed(() => builder?.wireframeMode || false)
const gridViewMode = computed(() => builder?.gridViewMode || false)
const activeTheme = computed(() => builder?.activeTheme || 'janari')

// Theme Style Injection
const hexToHsl = (hex) => {
  if (!hex || typeof hex !== 'string' || !hex.startsWith('#')) return null
  let r = 0, g = 0, b = 0
  if (hex.length === 4) {
    r = parseInt('0x' + hex[1] + hex[1])
    g = parseInt('0x' + hex[2] + hex[2])
    b = parseInt('0x' + hex[3] + hex[3])
  } else if (hex.length === 7) {
    r = parseInt('0x' + hex[1] + hex[2])
    g = parseInt('0x' + hex[3] + hex[4])
    b = parseInt('0x' + hex[5] + hex[6])
  }
  r /= 255; g /= 255; b /= 255
  const cmin = Math.min(r, g, b), cmax = Math.max(r, g, b), delta = cmax - cmin
  let h = 0, s = 0, l = 0
  if (delta === 0) h = 0
  else if (cmax === r) h = ((g - b) / delta) % 6
  else if (cmax === g) h = (b - r) / delta + 2
  else h = (r - g) / delta + 4
  h = Math.round(h * 60)
  if (h < 0) h += 360
  l = (cmax + cmin) / 2
  s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1))
  s = +(s * 100).toFixed(1); l = +(l * 100).toFixed(1)
  return `${h} ${s}% ${l}%`
}

const injectThemeStyles = () => {
    if (!builder?.themeData) return

    const variables = []
    const builderOverrides = []
    const settings = builder.themeSettings || {}
    const manifest = builder.themeData.manifest || {}
    const themeSlug = builder.activeTheme || 'janari'

    if (manifest.settings_schema) {
        Object.keys(manifest.settings_schema).forEach(key => {
            const schema = manifest.settings_schema[key]
            const value = settings[key] !== undefined ? settings[key] : schema.default
            
            if (value === undefined || value === null) return

            const cssKey = '--theme-' + key.replace(/_/g, '-')
            
            if (schema.type === 'color') {
                variables.push(`${cssKey}: ${value};`)
                const hslValue = hexToHsl(value)
                if (hslValue) {
                    variables.push(`${cssKey}-hsl: ${hslValue};`)
                    
                    // Map to Shadcn/Tailwind standard variable names if they match
                    if (key === 'color_primary' || key === 'primary_color') {
                        variables.push(`--primary: ${hslValue};`)
                        builderOverrides.push(`--builder-section: ${value};`)
                    }
                    if (key === 'color_background' || key === 'background_color') {
                        variables.push(`--background: ${hslValue};`)
                    }
                }
            } else if (schema.type === 'font' || schema.type === 'typography' || schema.type === 'select') {
                const fontValue = String(value).includes(' ') ? `"${value}"` : value
                variables.push(`${cssKey}: ${fontValue};`)
            } else {
                variables.push(`${cssKey}: ${value};`)
            }
        })
    }

    // Default fallbacks for common Tailwind variables if not set
    if (!variables.some(v => v.startsWith('--primary:'))) {
        variables.push('--primary: 221.2 83.2% 53.3%;') // Default blue
    }

    // Inject CSS variables scoped to .canvas.theme-[slug]
    const styleId = `builder-theme-styles`
    let styleTag = document.getElementById(styleId)
    if (!styleTag) {
        styleTag = document.createElement('style')
        styleTag.id = styleId
        document.head.appendChild(styleTag)
    }

    const cssContent = `
        .canvas.theme-${themeSlug} {
            ${variables.join('\n            ')}
            ${builderOverrides.join('\n            ')}
        }
        /* Debug indicator if theme is active */
        .canvas.theme-${themeSlug}::after {
            content: "Theme: ${themeSlug}";
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 10px;
            color: rgba(0,0,0,0.2);
            pointer-events: none;
            z-index: 1000;
        }
    `
    styleTag.textContent = cssContent

    // Inject Theme Assets (CSS Files)
    if (builder.themeData.assets && builder.themeData.assets.css) {
        builder.themeData.assets.css.forEach((cssFile, index) => {
            const linkId = `builder-theme-asset-${index}`
            if (!document.getElementById(linkId)) {
                const link = document.createElement('link')
                link.id = linkId
                link.rel = 'stylesheet'
                link.href = cssFile.startsWith('http') || cssFile.startsWith('/') ? cssFile : `/${cssFile}`
                document.head.appendChild(link)
            }
        })
    }
}

// Watchers
watch(() => builder?.themeData, (data) => {
    if (data) {
        injectThemeStyles()
    }
}, { immediate: true })

watch(() => builder?.themeSettings, injectThemeStyles, { deep: true })
watch(() => builder?.activeTheme, injectThemeStyles)

// Methods
const clearSelection = () => {
  builder?.clearSelection()
}

const addSection = () => {
  builder?.insertModule('section')
}

// Initialize default section if empty
onMounted(() => {
    if (builder && builder.blocks.length === 0) {
        addSection()
    }
    if (builder?.themeData) {
        injectThemeStyles()
    }
})
</script>

<style scoped>
.canvas {
  min-height: 100%;
  padding: var(--spacing-lg);

  /* Force Light Mode Context for Content */
  --builder-text-primary: #0f172a;
  --builder-text-secondary: #475569;
  --builder-text-muted: #64748b;
  --builder-border: #e2e8f0;

  /* Apply defaults or theme overrides */
  background-color: var(--theme-color-background, #ffffff);
  color: var(--theme-color-text, var(--builder-text-primary));
  transition: background-color 0.3s ease, color 0.3s ease;
}

.canvas--wireframe {
  background: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(0,0,0,0.05) 10px,
    rgba(0,0,0,0.05) 20px
  );
}

.canvas--grid {
  /* Grid View Styles - mostly handled in ModuleWrapper but adding container styles here */
  background-color: #f8fafc;
}

/* Empty State */
.canvas-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  border: 2px dashed var(--builder-border);
  border-radius: var(--border-radius-md);
  background: rgba(0,0,0,0.02);
}

.canvas-empty__content {
  text-align: center;
}

.canvas-empty__icon {
  color: var(--builder-text-muted);
  margin-bottom: var(--spacing-md);
}

.canvas-empty__text {
  color: var(--builder-text-muted);
  margin-bottom: var(--spacing-lg);
}

.canvas-empty__btn {
  display: inline-flex;
  align-items: center;
  gap: var(--spacing-xs);
  padding: var(--spacing-sm) var(--spacing-lg);
  background: var(--builder-accent);
  border: none;
  border-radius: var(--border-radius-sm);
  color: white;
  font-size: var(--font-size-sm);
  font-weight: 500;
  cursor: pointer;
  transition: background var(--transition-fast);
}

.canvas-empty__btn:hover {
  background: var(--builder-accent-hover);
}
</style>
