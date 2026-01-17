<template>
  <div class="layouts-panel">
    <div class="layouts-grid">
      <div v-for="layout in localizedLayouts" :key="layout.id" class="layout-card">
        <div class="layout-preview">
            <!-- Mock preview -->
            <div class="preview-box"></div>
        </div>
        <div class="layout-info">
          <span class="layout-name">{{ layout.name }}</span>
          <button class="layout-add-btn">
            <component :is="icons.Plus" :size="14" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Plus } from 'lucide-vue-next'

const { t } = useI18n()
const icons = { Plus }

const layouts = [
  { id: 1, key: 'hero' },
  { id: 2, key: 'features' },
  { id: 3, key: 'contact' },
  { id: 4, key: 'pricing' },
  { id: 5, key: 'testimonials' },
  { id: 6, key: 'footer' }
]

const localizedLayouts = computed(() => {
  return layouts.map(l => ({
    ...l,
    name: t(`builder.panels.layouts.presets.${l.key}`)
  }))
})
</script>

<style scoped>
.layouts-panel {
  padding: var(--spacing-sm);
}

.layouts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-sm);
}

.layout-card {
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  overflow: hidden;
  transition: border-color 0.2s;
}

.layout-card:hover {
  border-color: var(--builder-accent);
}

.layout-preview {
  height: 80px;
  background: var(--builder-bg-secondary);
  padding: 8px;
}

.preview-box {
  width: 100%;
  height: 100%;
  background: #e0e0e0;
  opacity: 0.1;
  border-radius: 2px;
}

.layout-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px;
  border-top: 1px solid var(--builder-border);
}

.layout-name {
  font-size: 11px;
  font-weight: 500;
  color: var(--builder-text-secondary);
}

.layout-add-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  background: var(--builder-accent);
  border: none;
  border-radius: var(--border-radius-sm);
  color: white;
  cursor: pointer;
  opacity: 0;
  transform: scale(0.8);
  transition: all 0.2s;
}

.layout-card:hover .layout-add-btn {
  opacity: 1;
  transform: scale(1);
}
</style>
