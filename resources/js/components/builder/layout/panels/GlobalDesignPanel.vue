<template>
  <div class="design-panel">
    <!-- Colors -->
    <div class="design-section">
      <h4 class="section-title">{{ t('builder.panels.globalDesign.colors') }}</h4>
      <div class="color-grid">
        <div v-for="color in colors" :key="color.id" class="color-item">
          <div class="color-preview" :style="{ backgroundColor: color.value }"></div>
          <div class="color-info">
            <span class="color-name">{{ color.name }}</span>
            <span class="color-value">{{ color.value }}</span>
          </div>
        </div>
        <button class="add-color-btn">
          <Plus :size="16" />
        </button>
      </div>
    </div>

    <div class="design-divider"></div>

    <!-- Typography -->
    <div class="design-section">
      <h4 class="section-title">{{ t('builder.panels.globalDesign.typography') }}</h4>
      <div class="typography-list">
        <div class="type-item">
          <label>{{ t('builder.panels.globalDesign.fields.headingFont') }}</label>
          <select class="type-select">
            <option>Inter</option>
            <option>Roboto</option>
            <option>Open Sans</option>
          </select>
        </div>
        <div class="type-item">
          <label>{{ t('builder.panels.globalDesign.fields.bodyFont') }}</label>
          <select class="type-select">
            <option>Inter</option>
            <option>Roboto</option>
            <option>Lato</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Plus } from 'lucide-vue-next'

const { t } = useI18n()

const colors = computed(() => [
  { id: 1, name: t('builder.panels.globalDesign.presets.primary'), value: '#2059ea' },
  { id: 2, name: t('builder.panels.globalDesign.presets.secondary'), value: '#18b793' },
  { id: 3, name: t('builder.panels.globalDesign.presets.dark'), value: '#1a1e25' },
  { id: 4, name: t('builder.panels.globalDesign.presets.light'), value: '#ffffff' }
])
</script>

<style scoped>
.design-panel {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
  padding: var(--spacing-sm);
}

.section-title {
  font-size: var(--font-size-sm);
  font-weight: 600;
  color: var(--builder-text-primary);
  margin: 0 0 var(--spacing-md) 0;
}

.color-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-sm);
}

.color-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  cursor: pointer;
}

.color-item:hover {
  border-color: var(--builder-border);
}

.color-preview {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 1px solid rgba(255,255,255,0.1);
}

.color-info {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.color-name {
  font-size: 11px;
  font-weight: 500;
  color: var(--builder-text-primary);
}

.color-value {
  font-size: 10px;
  color: var(--builder-text-muted);
}

.add-color-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 38px;
  background: transparent;
  border: 1px dashed var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-muted);
  cursor: pointer;
}

.add-color-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
}

.design-divider {
  height: 1px;
  background: var(--builder-border);
}

.type-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: var(--spacing-sm);
}

.type-item label {
  font-size: 11px;
  color: var(--builder-text-secondary);
}

.type-select {
  padding: 6px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-primary);
  font-size: var(--font-size-sm);
}
</style>
