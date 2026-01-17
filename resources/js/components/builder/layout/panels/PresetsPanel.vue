<template>
  <div class="presets-panel">
    <div class="preset-category" v-for="category in localizedCategories" :key="category.id">
      <h4 class="category-title">{{ category.name }}</h4>
      <div class="presets-list">
        <div v-for="preset in category.items" :key="preset.id" class="preset-item">
            <span class="preset-name">{{ preset.name }}</span>
            <button class="apply-btn">{{ t('builder.panels.presets.actions.apply') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const categories = [
  {
    id: 'buttons',
    items: [
        { id: 'btn-1', key: 'primaryRounded' },
        { id: 'btn-2', key: 'outlineSharp' },
        { id: 'btn-3', key: 'gradientPill' }
    ]
  },
  {
    id: 'cards',
    items: [
        { id: 'card-1', key: 'shadowHover' },
        { id: 'card-2', key: 'borderMinimal' },
        { id: 'card-3', key: 'glassmorphism' }
    ]
  }
]

const localizedCategories = computed(() => {
  return categories.map(cat => ({
    ...cat,
    name: t(`builder.panels.presets.categories.${cat.id}`),
    items: cat.items.map(item => ({
      ...item,
      name: t(`builder.panels.presets.items.${item.key}`)
    }))
  }))
})
</script>

<style scoped>
.presets-panel {
  padding: var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

.category-title {
  font-size: var(--font-size-xs);
  letter-spacing: 0.5px;
  color: var(--builder-text-muted);
  margin: 0 0 var(--spacing-sm) 0;
}

.presets-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.preset-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
}

.preset-item:hover {
  border-color: var(--builder-border);
}

.preset-name {
  font-size: var(--font-size-sm);
  color: var(--builder-text-primary);
}

.apply-btn {
  font-size: 10px;
  padding: 2px 6px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-secondary);
  cursor: pointer;
}

.apply-btn:hover {
  background: var(--builder-accent);
  color: white;
  border-color: var(--builder-accent);
}
</style>
