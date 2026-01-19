<template>
  <div class="presets-panel">
    <!-- Header/Filter -->
    <div class="panel-info" v-if="loading">
        <div class="spinner-xs"></div>
        <span>{{ $t('builder.messages.loading') }}</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="presets.length === 0" class="empty-state">
        <component :is="icons.Star" :size="32" />
        <p>{{ $t('builder.panels.presets.empty') }}</p>
    </div>

    <!-- Presets List -->
    <div v-else class="presets-container-scroll">
      <div class="presets-content">
        <div v-for="type in groupedTypes" :key="type" class="preset-group">
            <div class="category-header" @click="toggleType(type)">
                <component 
                    :is="isExpanded(type) ? icons.ChevronDown : icons.ChevronRight" 
                    :size="14" 
                    class="chevron"
                />
                <h4 class="category-title">{{ type }}</h4>
                <span class="count-badge">{{ getPresetsByType(type).length }}</span>
            </div>
            
            <transition name="accordion">
                <div v-if="isExpanded(type)" class="presets-list">
                    <div 
                        v-for="preset in getPresetsByType(type)" 
                        :key="preset.id" 
                        class="preset-item is-clickable"
                        :class="{ 
                            'is-system': preset.is_system,
                            'can-apply': canApplyToSelection(preset)
                        }"
                        @click="handlePresetClick(preset)"
                    >
                        <div class="preset-info">
                            <span class="preset-name">{{ preset.name }}</span>
                            <span v-if="preset.is_system" class="system-badge">System</span>
                        </div>
                        
                        <div class="preset-actions">
                            <button 
                                class="action-btn apply" 
                                :title="canApplyToSelection(preset) ? t('builder.panels.presets.actions.apply') : t('builder.actions.add', { name: preset.name })"
                                @click.stop="handlePresetClick(preset)"
                            >
                                <component :is="canApplyToSelection(preset) ? icons.Check : icons.Plus" :size="12" />
                            </button>
                            <button 
                                v-if="!preset.is_system"
                                class="action-btn delete" 
                                :title="t('builder.panels.presets.actions.delete')"
                                @click.stop="deletePreset(preset.id)"
                            >
                                <component :is="icons.Trash" :size="12" />
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, inject, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { Star, Check, Trash, ChevronDown, ChevronRight, Plus } from 'lucide-vue-next'

const { t } = useI18n()
const builder = inject('builder')
const icons = { Star, Check, Trash, ChevronDown, ChevronRight, Plus }

// builder.presets is already a reactive Proxy, not a ref - don't use .value
const presets = computed(() => builder?.presets || [])
const loading = computed(() => builder?.loadingPresets || false)

// Accordion state - single open behavior
const activeType = ref(null)
const isLoaded = ref(false)

watch(presets, (newPresets) => {
    if (newPresets.length > 0 && !isLoaded.value) {
        const types = Array.from(new Set(newPresets.map(p => p.type))).sort()
        if (types.length > 0) {
            activeType.value = types[0] // Open the first category by default
        }
        isLoaded.value = true
    }
}, { immediate: true })

const toggleType = (type) => {
    activeType.value = activeType.value === type ? null : type
}

const isExpanded = (type) => activeType.value === type

const groupedTypes = computed(() => {
    const types = new Set(presets.value.map(p => p.type))
    return Array.from(types).sort()
})

const getPresetsByType = (type) => {
    return presets.value.filter(p => p.type === type)
}

const canApplyToSelection = (preset) => {
    if (!builder?.selectedModule) return false
    return builder.selectedModule.type === preset.type
}

const handlePresetClick = (preset) => {
    if (canApplyToSelection(preset)) {
        // Apply styles to selected module
        builder.applyPreset(builder.selectedModule.id, preset)
    } else {
        // Insert as new module
        builder.insertFromPreset(preset)
    }
}

const deletePreset = (id) => {
    if (id && confirm(t('builder.messages.confirmDelete'))) {
        builder?.deletePreset(id)
    }
}

onMounted(() => {
    builder?.fetchPresets()
})
</script>

<style scoped>
.presets-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.presets-container-scroll {
    flex: 1;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--builder-border) transparent;
}

.presets-container-scroll::-webkit-scrollbar {
    width: 4px;
}

.presets-container-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.presets-container-scroll::-webkit-scrollbar-thumb {
    background: var(--builder-border);
    border-radius: 10px;
}

.presets-content {
    padding: var(--spacing-sm);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.panel-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 40px;
    color: var(--builder-text-muted);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: var(--builder-text-muted);
    text-align: center;
    gap: 12px;
}

.empty-state p {
    font-size: 13px;
}

.category-header {
  display: flex;
  align-items: center;
  padding: 4px 8px;
  margin-bottom: 4px;
  cursor: pointer;
  border-radius: 4px;
  transition: background 0.2s;
  user-select: none;
}

.category-header:hover {
    background: var(--builder-bg-secondary);
}

.category-header .chevron {
    margin-right: 6px;
    color: var(--builder-text-muted);
}

.category-title {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--builder-text-muted);
  margin: 0;
  font-weight: 700;
  flex: 1;
}

.count-badge {
    font-size: 10px;
    color: var(--builder-text-muted);
    background: var(--builder-border);
    padding: 1px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
}

.presets-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 4px;
}

/* Accordion Transition */
.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.2s ease-in-out;
  max-height: 500px;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px);
}

.preset-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-md);
  transition: all 0.2s;
}

.preset-item.is-clickable {
    cursor: pointer;
}

.preset-item.is-clickable:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.preset-item:not(.is-clickable) {
    opacity: 0.7;
}

.preset-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.preset-name {
  font-size: var(--font-size-sm);
  color: var(--builder-text-primary);
  font-weight: 500;
}

.system-badge {
    font-size: 9px;
    background: var(--builder-accent);
    color: white;
    padding: 1px 4px;
    border-radius: 3px;
    width: fit-content;
    text-transform: uppercase;
    font-weight: 700;
}

.preset-actions {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: 1px solid var(--builder-border);
    background: var(--builder-bg-secondary);
    color: var(--builder-text-muted);
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover:not(:disabled) {
    color: white;
}

.action-btn.apply:hover:not(:disabled) {
    background: var(--builder-success);
    border-color: var(--builder-success);
}

.action-btn.delete:hover:not(:disabled) {
    background: var(--builder-error);
    border-color: var(--builder-error);
}

.action-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.spinner-xs {
    width: 14px;
    height: 14px;
    border: 2px solid var(--builder-border);
    border-top-color: var(--builder-accent);
    border-radius: 50%;
    animation: rotate 0.8s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
