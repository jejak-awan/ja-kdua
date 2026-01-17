<template>
  <div class="pages-panel">
    <div class="pages-search">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="t('builder.panels.pages.searchPlaceholder')"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
      </BaseInput>
    </div>

    <!-- List -->
    <div class="pages-list">
      <div 
        v-for="page in filteredPages" 
        :key="page.id" 
        class="page-item" 
        :class="{ 'page-item--active': currentPageId === page.id }"
        @click="selectPage(page.id)"
      >
        <div class="page-info">
          <span class="page-title">{{ page.title }}</span>
          <span class="page-slug">/{{ page.slug }}</span>
        </div>
        <div class="page-actions">
          <button class="action-btn" :title="t('builder.panels.pages.actions.edit')">
            <Edit2 :size="14" />
          </button>
          <button class="action-btn" :title="t('builder.panels.pages.actions.delete')">
            <Trash2 :size="14" />
          </button>
        </div>
      </div>
      
      <div v-if="filteredPages.length === 0" class="empty-results">
        {{ t('builder.panels.pages.empty') }}
      </div>
    </div>
    
    <!-- Add Button -->
    <div class="panel-footer">
        <button class="add-page-btn" @click="handleCreate">
          <Plus :size="16" />
          <span>{{ t('builder.panels.pages.addNew') }}</span>
        </button>
    </div>
  </div>
</template>

<script setup>
import { ref, inject, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Edit2, Trash2, Plus, Search } from 'lucide-vue-next'
import { BaseInput } from '../../ui'

const { t } = useI18n()
const builder = inject('builder')
const pages = computed(() => builder?.pages || [])
const currentPageId = computed(() => builder?.currentPageId)

const searchQuery = ref('')

const filteredPages = computed(() => {
  if (!searchQuery.value) return pages.value
  const query = searchQuery.value.toLowerCase()
  return pages.value.filter(p => 
    p.title.toLowerCase().includes(query) || 
    p.slug.toLowerCase().includes(query)
  )
})

const selectPage = (id) => {
  builder?.setCurrentPage(id)
}

const handleCreate = () => {
  const title = prompt(t('builder.panels.pages.promptTitle'))
  if (title) {
    builder?.addPage(title)
  }
}
</script>

<style scoped>
.pages-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.pages-search {
  padding: var(--spacing-sm);
  position: relative;
  z-index: 2;
  background: var(--builder-bg-primary);
}

.pages-list {
  flex: 1;
  overflow-y: auto;
  padding: 0 var(--spacing-sm) var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.page-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: var(--border-radius-sm);
  cursor: pointer;
  transition: background 0.2s;
  border-left: 3px solid transparent;
}

.page-item:hover {
  background: var(--builder-bg-secondary);
}

.page-item--active {
  background: var(--builder-bg-secondary);
  border-left-color: var(--builder-accent);
}

.page-info {
  display: flex;
  flex-direction: column;
}

.page-title {
  font-size: var(--font-size-sm);
  font-weight: 500;
  color: var(--builder-text-primary);
}

.page-slug {
  font-size: 11px;
  color: var(--builder-text-muted);
}

.page-actions {
  display: flex;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.2s;
}

.page-item:hover .page-actions {
  opacity: 1;
}

.action-btn {
  padding: 4px;
  background: transparent;
  border: none;
  color: var(--builder-text-muted);
  cursor: pointer;
  border-radius: var(--border-radius-sm);
}

.action-btn:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.empty-results {
  padding: var(--spacing-md);
  text-align: center;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}

.panel-footer {
    padding: var(--spacing-sm);
    border-top: 1px solid var(--builder-border);
}

.add-page-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px;
  width: 100%;
  background: var(--builder-bg-primary);
  border: 1px dashed var(--builder-border);
  border-radius: var(--border-radius-sm);
  color: var(--builder-text-secondary);
  font-size: var(--font-size-sm);
  cursor: pointer;
  transition: all 0.2s;
}

.add-page-btn:hover {
  border-color: var(--builder-accent);
  color: var(--builder-accent);
  background: var(--builder-bg-primary);
}
</style>
