<template>
  <BaseModal
    :is-open="true"
    :title="$t('builder.insertSectionModal.title')"
    :width="650"
    draggable
    placement="center"
    @close="$emit('close')"
  >
    <template #header>
      <div class="modal-tabs">
        <button 
          v-for="tab in tabs"
          :key="tab.id"
          class="modal-tab"
          :class="{ 'modal-tab--active': activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          {{ te('builder.insertModal.tabs.' + tab.id) ? $t('builder.insertModal.tabs.' + tab.id) : (te('builder.insertSectionModal.tabs.' + tab.id) ? $t('builder.insertSectionModal.tabs.' + tab.id) : tab.label) }}
        </button>
      </div>
    </template>

    <div class="modal-body-content">
      <div class="modal-content">
        <div v-if="activeTab === 'section'" class="layout-wrapper">
          
          <div v-for="group in allGroups" :key="group.id" class="layout-group">
            <h4 class="group-title">
              <span :class="['category-badge', `category-badge--${group.type}`]">
                {{ group.type === 'flex' ? t('builder.fields.layoutTypes.flex') : t('builder.fields.layoutTypes.grid') }}
              </span>
              {{ te('builder.insertModal.layoutGroups.' + group.id) ? $t('builder.insertModal.layoutGroups.' + group.id) : group.title }}
            </h4>
            
            <div class="layout-grid">
              <button 
                v-for="(preset, i) in group.items" 
                :key="group.id + '-' + i"
                class="layout-card"
                @click="selectLayout(preset)"
                :title="preset.name"
              >
                <!-- Specialty/Nested Preview -->
                <div v-if="preset.cols" class="layout-preview specialty-preview">
                  <div v-for="(col, cIdx) in preset.cols" :key="cIdx" class="preview-specialty-col" :style="{ flex: col.width }">
                    <template v-if="col.rows">
                       <div v-for="(r, rIdx) in col.rows" :key="rIdx" class="preview-specialty-row">
                          <div v-for="(w, wIdx) in r.widths" :key="wIdx" class="preview-col" :style="{ flex: w }"></div>
                       </div>
                    </template>
                    <div v-else class="preview-col full-height"></div>
                  </div>
                </div>

                <!-- Multi-Row Preview -->
                <div v-else-if="preset.rows" class="layout-preview-stacked">
                  <div v-for="(row, rIdx) in preset.rows" :key="rIdx" class="preview-row">
                      <div 
                        v-for="(width, cIdx) in row.widths" 
                        :key="cIdx" 
                        class="preview-col"
                        :style="{ flex: width }"
                      ></div>
                  </div>
                </div>

                <!-- Standard/Equal Preview -->
                <div v-else class="layout-preview">
                  <div 
                    v-for="(width, idx) in preset.widths" 
                    :key="idx" 
                    class="preview-col"
                    :style="{ flex: width }"
                  ></div>
                </div>
              </button>
            </div>
          </div>

        </div>
        
        <!-- Library Tab - Section Templates -->
        <div v-else class="library-wrapper">
          <div v-for="group in templateGroups" :key="group.id" class="template-group">
            <h4 class="group-title">
              <span class="category-badge category-badge--template">
                {{ group.icon }}
              </span>
              {{ group.title }}
            </h4>
            
            <div class="template-grid">
              <button 
                v-for="template in group.templates" 
                :key="template.id"
                class="template-card"
                @click="insertTemplate(template)"
                :title="template.description"
              >
                <div class="template-preview" :class="`preview-${template.thumbnail}`">
                  <div class="preview-placeholder">
                    <component :is="getPreviewIcon(template.category)" class="preview-icon" />
                  </div>
                </div>
                <div class="template-info">
                  <h5 class="template-name">{{ template.name }}</h5>
                  <p class="template-desc">{{ template.description }}</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { BaseModal } from '../ui'
import ModuleRegistry from '../core/ModuleRegistry'
import { 
    equalLayouts, 
    offsetLayouts, 
    flexMultiRowPresets, 
    flexMultiColumnPresets, 
    gridMultiRowPresets,
    masonryPresets,
    sidebarPresets
} from '../constants/layouts.js'
import { sectionTemplates } from '../templates/SectionTemplates.js'
import { Sparkles, Layout, Users, MessageSquare, FileText, Megaphone } from 'lucide-vue-next'

const props = defineProps({
  targetIndex: {
    type: Number,
    default: -1
  }
})

const emit = defineEmits(['close', 'inserted'])
const builder = inject('builder')
const activeTab = ref('section')
const { t, te } = useI18n()

const tabs = [
  { id: 'section', label: 'New Section' },
  { id: 'library', label: 'Add From Library' }
]

const allGroups = computed(() => [
  { id: 'equal', type: 'flex', title: 'Equal Columns', items: equalLayouts },
  { id: 'offset', type: 'flex', title: 'Offset Columns', items: offsetLayouts },
  { id: 'multiRow', type: 'flex', title: 'Multi-Row', items: flexMultiRowPresets },
  { id: 'multiCol', type: 'flex', title: 'Multi-Column', items: flexMultiColumnPresets },
  { id: 'grid-multi-row', type: 'grid', title: 'Multi-Row', items: gridMultiRowPresets },
  { id: 'masonry', type: 'grid', title: 'Masonry', items: masonryPresets },
  { id: 'sidebar', type: 'grid', title: 'Sidebar', items: sidebarPresets }
])

// Template groups for library tab
const templateGroups = computed(() => {
  const categories = {
    hero: { id: 'hero', title: 'Hero Sections', icon: '🚀', templates: [] },
    features: { id: 'features', title: 'Features', icon: '✨', templates: [] },
    content: { id: 'content', title: 'Content', icon: '📝', templates: [] },
    cta: { id: 'cta', title: 'Call to Action', icon: '📢', templates: [] },
    team: { id: 'team', title: 'Team', icon: '👥', templates: [] },
    header: { id: 'header', title: 'Headers', icon: '📄', templates: [] }
  }
  
  sectionTemplates.forEach(tpl => {
    const cat = categories[tpl.category] || categories.content
    cat.templates.push(tpl)
  })
  
  return Object.values(categories).filter(c => c.templates.length > 0)
})

// Get preview icon based on category
const getPreviewIcon = (category) => {
  const iconMap = {
    hero: Sparkles,
    features: Layout,
    content: FileText,
    cta: Megaphone,
    team: Users,
    header: FileText
  }
  return iconMap[category] || Layout
}

// Insert a template into the builder
const insertTemplate = (template) => {
  if (!template.factory) return
  
  // Generate the section with all its children
  const sectionData = template.factory()
  
  // Deep clone to ensure unique IDs
  const clonedSection = JSON.parse(JSON.stringify(sectionData))
  
  // Regenerate all IDs to be unique
  const regenerateIds = (block) => {
    block.id = ModuleRegistry.generateId()
    if (block.children) {
      block.children.forEach(regenerateIds)
    }
  }
  regenerateIds(clonedSection)
  
  // Insert into builder
  if (!builder.blocks) builder.blocks = []
  
  const targetIndex = props.targetIndex >= 0 ? props.targetIndex : builder.blocks.length
  builder.blocks.splice(targetIndex, 0, clonedSection)
  
  builder.takeSnapshot()
  builder.selectModule(clonedSection.id)
  
  emit('inserted')
  emit('close')
}

const selectLayout = (layout) => {
    // 1. Create Section
    const section = builder.insertModule('section', null, props.targetIndex)
    if (!section) return

    // Helper to create row
    const createRow = (config, parent = section) => {
        const row = ModuleRegistry.createInstance('row')
        row.id = ModuleRegistry.generateId()
        if (!parent.children) parent.children = []
        parent.children.push(row)
        
        if (!row.children) row.children = []
        
        if (config.cols) {
            config.cols.forEach(colConfig => {
                const col = ModuleRegistry.createInstance('column')
                col.id = ModuleRegistry.generateId()
                col.settings.flexGrow = colConfig.width
                row.children.push(col)
                
                if (colConfig.rows) {
                    colConfig.rows.forEach(nestedRowConfig => {
                        createRow(nestedRowConfig, col)
                    })
                }
            })
        } else {
            const widths = config.widths || [1]
            widths.forEach(width => {
                const col = ModuleRegistry.createInstance('column')
                col.id = ModuleRegistry.generateId()
                col.settings.flexGrow = width
                row.children.push(col)
            })
        }
    }

    if (layout.rows) {
        layout.rows.forEach(rowConfig => createRow(rowConfig))
    } else {
        createRow(layout)
    }
    
    builder.takeSnapshot()
    builder.selectModule(section.id)
    
    emit('inserted')
    emit('close')
}
</script>

<style scoped>
.modal-tabs {
  display: flex;
  margin-left: 20px;
}

.modal-tab {
  background: none;
  border: none;
  padding: 12px 16px;
  color: var(--builder-text-secondary);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  font-weight: 500;
  font-size: 14px;
}

.modal-tab:hover {
  color: var(--builder-text-primary);
}

.modal-tab--active {
  color: var(--builder-accent);
  border-bottom-color: var(--builder-accent);
}
/* .modal-body-content removed - BaseModal handles scroll and container size */

.category-badge {
  display: inline-block;
  padding: 2px 6px;
  font-size: 10px;
  font-weight: 600;
  border-radius: 3px;
  letter-spacing: 0.5px;
  margin-right: 6px;
}

.category-badge--flex {
  color: var(--builder-accent);
  background: rgba(var(--builder-accent-rgb, 32, 89, 234), 0.1);
  border: 1px solid var(--builder-accent);
}

.category-badge--grid {
  color: var(--builder-success);
  background: rgba(24, 183, 147, 0.1);
  border: 1px solid var(--builder-success);
}

.group-title {
  margin: 0 0 12px 0;
  font-size: 12px;
  color: var(--builder-text-primary);
  display: flex;
  align-items: center;
  font-weight: 600;
}

.group-title::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--builder-border);
  margin-left: 10px;
  opacity: 0.5;
}

.layout-group {
    margin-bottom: 24px;
}

.layout-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.layout-card {
  padding: 12px;
  background: transparent;
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.layout-card:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
}

.layout-preview {
  display: flex;
  height: 30px;
  gap: 4px;
}

.preview-col {
  background: var(--builder-text-muted);
  opacity: 0.4;
  border-radius: 2px;
  flex: 1;
}

.preview-col.full-height {
  height: 100%;
}

.layout-card:hover .preview-col {
    background: var(--builder-accent);
    opacity: 0.8;
}

.layout-preview-stacked {
  display: flex;
  flex-direction: column;
  height: 30px;
  gap: 3px;
  width: 100%;
}

.preview-row {
    display: flex;
    flex: 1;
    gap: 3px;
}

.specialty-preview {
  gap: 4px;
}

.preview-specialty-col {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.preview-specialty-row {
  flex: 1;
  display: flex;
  gap: 3px;
}

.library-wrapper {
    padding: 0;
}

.template-group {
    margin-bottom: 24px;
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.template-card {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 0;
    background: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
}

.template-card:hover {
    border-color: var(--builder-accent);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.template-preview {
    height: 80px;
    background: linear-gradient(135deg, var(--builder-accent-light, rgba(32, 89, 234, 0.1)) 0%, var(--builder-bg-tertiary) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid var(--builder-border);
}

.preview-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--builder-bg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-icon {
    width: 20px;
    height: 20px;
    color: var(--builder-accent);
}

.template-card:hover .preview-icon {
    color: var(--builder-accent);
}

.template-info {
    padding: 12px;
}

.template-name {
    margin: 0 0 4px 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--builder-text-primary);
}

.template-desc {
    margin: 0;
    font-size: 11px;
    color: var(--builder-text-muted);
    line-height: 1.4;
}

.category-badge--template {
    font-size: 14px;
    background: transparent;
    border: none;
    padding: 0;
    margin-right: 8px;
}
</style>
