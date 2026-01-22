<template>
  <BaseModal 
    :is-open="true"
    :title="$t('builder.modals.responsive.title', { label: label })" 
    width="450px" 
    draggable
    no-backdrop
    placement="right-sidebar" 
    @close="$emit('close')"
  >
    <div class="responsive-editor-content">
      <div 
        v-for="mode in modes" 
        :key="mode.id" 
        :ref="el => setItemRef(el, mode.id)"
        class="responsive-item"
        :class="{ 'is-active': currentDevice === mode.id }"
      >
        <div class="item-header" @click="changeMode(mode.id)">
            <div class="flex items-center gap-2">
                <component :is="mode.icon" :size="14" class="mode-icon" />
                <span class="item-label">{{ mode.label }}</span>
            </div>
            
            <div class="flex items-center gap-2">
                <FieldActions 
                    :label="label"
                    :show-responsive="false"
                    :show-reset="!!getModeValue(mode.id)"
                    :show-dynamic-data="type !== 'gradient'"
                    :show-context-menu="true"
                    :show-info="true"
                    :info-content="getInfoContent(mode.id)"
                    @reset="updateModeValue(mode.id, null)"
                    @reset-field="updateModeValue(mode.id, null)"
                    @toggle-info="toggleInfo(mode.id, $event)"
                    @select-dynamic-data="updateModeValue(mode.id, $event)"
                />
                <div v-if="currentDevice === mode.id" class="active-dot"></div>
            </div>
        </div>

        <!-- Inline Info Panel (Aligned with FieldWrapper) -->
        <transition name="fade-slide">
            <div v-if="visibleInfoItems[mode.id] && getInfoContent(mode.id)" class="field-info-panel">
                {{ getInfoContent(mode.id) }}
            </div>
        </transition>
        
        <div class="item-content">
          <!-- Pattern/Mask Special UI -->
          <div v-if="['pattern', 'mask'].includes(type) && getModeValue(mode.id) && getModeValue(mode.id) !== 'none'" class="pattern-mask-responsive-ui">
              <div class="bg-preview-box mb-2" :style="getCombinedStyle(mode.id)">
                  <div class="preview-actions-overlay">
                      <div class="action-btn-group">
                          <div class="action-icon-btn remove" :title="$t('builder.contextMenu.delete')" @click.stop="updateModeValue(mode.id, 'none')">
                              <Trash2 :size="14" />
                          </div>
                      </div>
                  </div>
              </div>

              <div class="select-field-container">
                <BaseDropdown align="left" width="100%">
                    <template #trigger="{ open: dropdownOpen }">
                        <div class="select-trigger" :class="{ 'is-open': dropdownOpen }">
                            <span class="selected-label">
                                {{ type === 'pattern' ? (getActivePattern(mode.id)?.label || $t('builder.common.none')) : (getActiveMask(mode.id)?.label || $t('builder.common.none')) }}
                            </span>
                            <ChevronDown :size="14" class="select-arrow" />
                        </div>
                    </template>

                    <template #default="{ close }">
                        <div class="dropdown-preview-container">
                            <component 
                                :is="FieldComponent"
                                :field="{ name: baseKey, label: label, options: options }"
                                :value="getModeValue(mode.id)"
                                :placeholder-value="getPlaceholder(mode.id)"
                                :module="module"
                                :hide-label="true"
                                @update:value="updateModeValue(mode.id, $event); close()"
                            />
                        </div>
                    </template>
                </BaseDropdown>
              </div>
          </div>

          <!-- Empty Pattern/Mask UI -->
          <div v-else-if="['pattern', 'mask'].includes(type) && (!getModeValue(mode.id) || getModeValue(mode.id) === 'none')" class="pattern-mask-responsive-ui">
              <div class="bg-preview-box is-empty cursor-pointer" @click="type === 'pattern' ? initDefaultPattern(mode.id) : initDefaultMask(mode.id)">
                  <div class="empty-preview">
                      <Plus :size="20" />
                      <span>{{ type === 'pattern' ? $t('builder.fields.background.pattern.add') : $t('builder.fields.background.mask.add') }}</span>
                  </div>
              </div>
          </div>

          <!-- Default UI for other types or empty pattern/mask -->
          <component 
            v-else
            :is="FieldComponent"
            :field="{ name: baseKey, label: label, options: options }"
            :value="getModeValue(mode.id)"
            :placeholder-value="getPlaceholder(mode.id)"
            :module="module"
            :minimal="['gradient', 'spacing', 'border', 'shadow'].includes(type)"
            :show-large-preview="type === 'color'"
            :hide-preview="type === 'color'"
            :hide-label="true"
            :preview-style="getPreviewStyle(mode.id)"
            :device="mode.id"
            v-bind="type === 'upload' ? options : {}"
            @update:value="updateModeValue(mode.id, $event)"
          />
        </div>

        <!-- Sub Fields (Conditional) -->
        <template v-if="subFields && subFields.length">
            <template v-for="(group, gIndex) in subFields" :key="gIndex">
                <div v-if="!group.match || getModeValue(mode.id) === group.match" class="sub-fields-container">
                    <div v-for="(field, fIndex) in group.fields" :key="fIndex" class="sub-field-item">
                         <div class="sub-field-label">{{ translateFieldLabel(field) }}</div>
                         
                         <!-- Transform Controls Special UI -->
                         <div v-if="field.type === 'transform'" class="transform-controls-row">
                             <div 
                                class="action-icon-btn transform-btn"
                                :class="{ 'is-active': getGenericValue(`${field.name}FlipH`, mode.id) }"
                                :title="$t('builder.fields.background.pattern.flipH')"
                                @click="updateGenericValue(`${field.name}FlipH`, mode.id, !getGenericValue(`${field.name}FlipH`, mode.id))"
                             >
                                 <FlipHorizontal :size="14" />
                             </div>
                             <div 
                                class="action-icon-btn transform-btn"
                                :class="{ 'is-active': getGenericValue(`${field.name}FlipV`, mode.id) }"
                                :title="$t('builder.fields.background.pattern.flipV')"
                                @click="updateGenericValue(`${field.name}FlipV`, mode.id, !getGenericValue(`${field.name}FlipV`, mode.id))"
                             >
                                 <FlipVertical :size="14" />
                             </div>
                             <div 
                                class="action-icon-btn transform-btn"
                                :class="{ 'is-active': getGenericValue(`${field.name}Rotate`, mode.id) }"
                                :title="$t('builder.fields.background.pattern.rotate')"
                                @click="updateGenericValue(`${field.name}Rotate`, mode.id, !getGenericValue(`${field.name}Rotate`, mode.id))"
                             >
                                 <RefreshCw :size="14" />
                             </div>
                             <div 
                                class="action-icon-btn transform-btn"
                                :class="{ 'is-active': getGenericValue(`${field.name}Invert`, mode.id) }"
                                :title="$t('builder.fields.background.pattern.invert')"
                                @click="updateGenericValue(`${field.name}Invert`, mode.id, !getGenericValue(`${field.name}Invert`, mode.id))"
                             >
                                 <Contrast :size="14" />
                             </div>
                         </div>

                         <component 
                            v-else
                            :is="fieldComponents[field.type] || fieldComponents.text"
                            :field="{ name: field.name, label: field.label, options: field.options }"
                            :value="getGenericValue(field.name, mode.id)"
                            :placeholder-value="getGenericPlaceholder(field.name, mode.id)"
                            :module="module"
                            :hide-label="true"
                            @update:value="updateGenericValue(field.name, mode.id, $event)"
                        />
                    </div>
                </div>
            </template>
        </template>
      </div>
    </div>
  </BaseModal>
</template>

<script setup>
import { computed, defineAsyncComponent, inject, ref, watch, reactive, onMounted } from 'vue'
import { Monitor, Tablet, Smartphone, MousePointer, ChevronDown, Plus, Trash2, FlipHorizontal, FlipVertical, RefreshCw, Contrast } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { BaseModal, BaseButton, BaseDropdown } from '../ui'
import FieldActions from '../fields/FieldActions.vue'
import { getBackgroundStyles } from '../core/styleUtils'
import { BackgroundPatterns, BackgroundMasks } from '../core/AssetLibrary'

const props = defineProps({
  label: { type: String, required: true },
  baseKey: { type: String, required: true },
  type: { type: String, default: 'color' }, // color, upload, text, etc.
  options: { type: [Array, Object], default: () => [] },
  module: { type: Object, required: true },
  settings: { type: Object, required: true },
  subFields: { type: Array, default: () => [] } // [{ match: 'custom', fields: [{name, label, type}] }]
})

const emit = defineEmits(['close', 'update'])
const builder = inject('builder')
const { t, te } = useI18n()

// Helper to translate field labels
const translateFieldLabel = (field) => {
    const moduleType = props.module?.type || 'common'
    const name = field.name

    // 1. Try module-specific: builder.settings.{moduleType}.{name}.label
    if (te(`builder.settings.${moduleType}.${name}.label`)) {
        return t(`builder.settings.${moduleType}.${name}.label`)
    }

    // 2. Try common field: builder.settings.fields.${name}
    if (te(`builder.settings.fields.${name}`)) {
        return t(`builder.settings.fields.${name}`)
    }

    return field.label
}

// Current builder device for highlighting
const currentDevice = computed(() => builder?.device || 'desktop')

// Live settings from builder store (reactive source of truth)
const liveSettings = computed(() => {
    // Explicitly track blocks to ensure reactivity
    if (builder?.blocks) { const _ = builder?.blocks }

    // We fetch it from builder.findModule to ensure we are tracking the actual state in blocks array
    const moduleItem = builder?.findModule?.(props.module?.id)
    
    // Fallback hierarchy: 
    // 1. Live module from store (Reactivity source)
    // 2. Props module (Snapshot when opened)
    // 3. Props settings (Values passed directly)
    return moduleItem?.settings || props.module?.settings || props.settings || {}
})

const changeMode = (id) => {
    if (id === 'hover') return // Hover is not a device mode
    if (builder && builder.device) {
        builder.device = id
    }
}

// Info Panel State
const visibleInfoItems = reactive({
    desktop: false,
    tablet: false,
    mobile: false,
    hover: false
})

const toggleInfo = (id, show) => {
    visibleInfoItems[id] = show
}

const getInfoContent = (id) => {
    const moduleType = props.module?.type || 'common'
    const key = props.baseKey.includes('.') ? props.baseKey.split('.').pop() : props.baseKey
    
    // Key paths to try in order
    const paths = [
        `builder.info.${moduleType}.${key}`,
        `builder.info.common.${key}`
    ]

    for (const path of paths) {
        const text = t(path)
        if (text && text !== path) {
            return text
        }
    }
    
    return null
}

const backgroundKeys = [
    'backgroundColor', 'backgroundGradient', 'backgroundGradients', 'backgroundGradientShowAboveImage',
    'backgroundImage', 'backgroundImageSize', 'backgroundImageWidth', 'backgroundImageHeight',
    'backgroundImageRepeat', 'backgroundImagePosition', 'backgroundImageBlendMode',
    'backgroundPattern', 'backgroundPatternColor', 'backgroundPatternRotate', 'backgroundPatternInvert',
    'backgroundPatternFlipH', 'backgroundPatternFlipV', 'backgroundPatternSize',
    'backgroundPatternWidth', 'backgroundPatternHeight', 'backgroundPatternRepeat',
    'backgroundPatternRepeatOrigin', 'backgroundPatternBlendMode',
    'backgroundMask', 'backgroundMaskColor', 'backgroundMaskRotate', 'backgroundMaskInvert',
    'backgroundMaskFlipH', 'backgroundMaskFlipV', 'backgroundMaskAspectRatio',
    'backgroundMaskSize', 'backgroundMaskWidth', 'backgroundMaskHeight',
    'backgroundMaskRepeat', 'backgroundMaskRepeatOrigin', 'backgroundMaskBlendMode'
]

const getCombinedStyle = (modeId) => {
    const resolvedSettings = {}
    backgroundKeys.forEach(key => {
        resolvedSettings[key] = getGenericValue(key, modeId)
    })
    return getBackgroundStyles(resolvedSettings)
}

const getActivePattern = (modeId) => {
    const val = getModeValue(modeId)
    if (!val || val === 'none') return null
    return BackgroundPatterns.find(p => p.id === val)
}

const getActiveMask = (modeId) => {
    const val = getModeValue(modeId)
    if (!val || val === 'none') return null
    return BackgroundMasks.find(m => m.id === val)
}

const initDefaultPattern = (modeId) => {
    const firstPattern = BackgroundPatterns[0]?.id || 'polka-dots'
    updateModeValue(modeId, firstPattern)
}

const initDefaultMask = (modeId) => {
    const firstMask = BackgroundMasks[0]?.id || 'circle'
    updateModeValue(modeId, firstMask)
}

// Dynamic field components
const fieldComponents = {
  color: defineAsyncComponent(() => import('../fields/ColorField.vue')),
  upload: defineAsyncComponent(() => import('../fields/UploadField.vue')),
  text: defineAsyncComponent(() => import('../fields/TextField.vue')),
  textarea: defineAsyncComponent(() => import('../fields/TextareaField.vue')),
  select: defineAsyncComponent(() => import('../fields/SelectField.vue')),
  toggle: defineAsyncComponent(() => import('../fields/ToggleField.vue')),
  range: defineAsyncComponent(() => import('../fields/RangeField.vue')),
  spacing: defineAsyncComponent(() => import('../fields/SpacingField.vue')),
  border: defineAsyncComponent(() => import('../fields/BorderField.vue')),
  shadow: defineAsyncComponent(() => import('../fields/ShadowField.vue')),
  buttonGroup: defineAsyncComponent(() => import('../fields/ButtonGroupField.vue')),
  gradient: defineAsyncComponent(() => import('../fields/GradientField.vue')),
  dimension: defineAsyncComponent(() => import('../fields/DimensionField.vue')),
  pattern: defineAsyncComponent(() => import('../fields/PatternField.vue')),
  mask: defineAsyncComponent(() => import('../fields/MaskField.vue')),
  advanced_number: defineAsyncComponent(() => import('../fields/AdvancedNumberField.vue')),
  icon: defineAsyncComponent(() => import('../fields/IconField.vue')),
  font: defineAsyncComponent(() => import('../fields/FontFamilyField.vue')),
  filters: defineAsyncComponent(() => import('../fields/FilterField.vue')),
  transform: defineAsyncComponent(() => import('../fields/TransformField.vue')),
  animation: defineAsyncComponent(() => import('../fields/AnimationField.vue')),
  number: defineAsyncComponent(() => import('../fields/NumberField.vue')),
  richtext: defineAsyncComponent(() => import('../fields/RichtextField.vue')),
  background: defineAsyncComponent(() => import('../fields/BackgroundField.vue'))
}

const FieldComponent = computed(() => {
  return fieldComponents[props.type] || fieldComponents.text
})

const modes = computed(() => [
  { id: 'desktop', label: t('builder.breakpoints.desktop'), icon: Monitor },
  { id: 'tablet', label: t('builder.breakpoints.tablet'), icon: Tablet },
  { id: 'mobile', label: t('builder.breakpoints.mobile'), icon: Smartphone },
  { id: 'hover', label: t('builder.breakpoints.hover'), icon: MousePointer }
])

const getModeValue = (id) => {
  const [parentPath, childKey] = props.baseKey.includes('.') ? props.baseKey.split('.') : [null, props.baseKey]
  
  const getVal = (deviceId) => {
    const suffix = deviceId === 'desktop' ? '' : (deviceId === 'mobile' ? '_mobile' : `_${deviceId}`)
    const fullParentKey = parentPath ? (parentPath + suffix) : (childKey + suffix)
    const settingsObj = liveSettings.value[fullParentKey]
    
    if (parentPath) {
      return settingsObj?.[childKey] ?? null
    }
    return settingsObj ?? null
  }

  return getVal(id)
}

// Generic helper for sub-fields
const getGenericValue = (key, id) => {
  const [parentPath, childKey] = key.includes('.') ? key.split('.') : [null, key]
  
  const getVal = (deviceId) => {
    const suffix = deviceId === 'desktop' ? '' : (deviceId === 'mobile' ? '_mobile' : `_${deviceId}`)
    const fullParentKey = parentPath ? (parentPath + suffix) : (childKey + suffix)
    const settingsObj = liveSettings.value[fullParentKey]
    
    if (parentPath) {
      return settingsObj?.[childKey] ?? null
    }
    return settingsObj ?? null
  }

  return getVal(id)
}

const getGenericPlaceholder = (key, id) => {
  const desktopValue = getGenericValue(key, 'desktop')
  const tabletValue = getGenericValue(key, 'tablet')
  
  if (id === 'tablet') return desktopValue || null
  if (id === 'mobile') {
    if (tabletValue !== null && tabletValue !== undefined && tabletValue !== '') {
      return tabletValue
    }
    return desktopValue || null
  }
  if (id === 'hover') return desktopValue || null
  return null
}

const getPlaceholder = (id) => {
  const desktopValue = getModeValue('desktop')
  const tabletValue = getModeValue('tablet')
  
  if (id === 'tablet') return desktopValue || null
  if (id === 'mobile') {
    // If tablet has a value, use it. Otherwise, use desktop.
    if (tabletValue !== null && tabletValue !== undefined && tabletValue !== '') {
      return tabletValue
    }
    return desktopValue || null
  }
  if (id === 'hover') return desktopValue || null
  return null
}

const getPreviewStyle = (id) => {
    if (props.type !== 'upload') return {}
    
    const isVideo = props.baseKey === 'backgroundVideoMp4' || props.baseKey === 'backgroundVideoWebm'
    const isImage = props.baseKey === 'backgroundImage'
    
    if (!isVideo && !isImage) return {}

    const styles = {}
    
    // Resolve helper
    const getVal = (base, modeId) => {
        const suffix = modeId === 'desktop' ? '' : (modeId === 'mobile' ? '_mobile' : `_${modeId}`)
        const val = liveSettings.value[base + suffix]
        
        if ((val === undefined || val === null || val === '') && modeId !== 'desktop') {
            const desktop = liveSettings.value[base]
            const tablet = liveSettings.value[base + '_tablet']
            return modeId === 'tablet' ? desktop : (tablet ?? desktop)
        }
        return val
    }

    if (isImage) {
        // Image logic: we return background properties
        const s = {
            width: getVal('backgroundImageWidth', id),
            height: getVal('backgroundImageHeight', id),
            size: getVal('backgroundImageSize', id),
            repeat: getVal('backgroundImageRepeat', id),
            position: getVal('backgroundImagePosition', id)
        }

        styles.width = '100%'
        styles.height = '100%'
        styles.backgroundRepeat = s.repeat || 'no-repeat'
        styles.backgroundPosition = s.position || 'center'

        let size = s.size || 'cover'
        if (size === 'custom') {
            size = `${s.width || 'auto'} ${s.height || 'auto'}`
        } else if (size === 'stretch') {
            size = '100% 100%'
        } else if (size === 'fit') {
            size = 'contain'
        }
        styles.backgroundSize = size
        
        // Signal to UploadField that this is a "div" style preview
        styles.useDiv = true 
    } else {
        // Video logic: direct element styling
        const w = getVal('backgroundVideoWidth', id)
        const h = getVal('backgroundVideoHeight', id)

        if (w) styles.width = w
        if (h) styles.height = h
        
        styles.maxWidth = '100%'
        styles.maxHeight = '100%'

        if (w || h) {
            styles.objectFit = 'contain'
        } else {
            styles.objectFit = 'cover'
            styles.width = '100%'
            styles.height = '100%'
        }
    }

    return styles
}

const updateModeValue = (id, value) => {
  const suffix = id === 'desktop' ? '' : (id === 'mobile' ? '_mobile' : `_${id}`)
  const [parentPath, childKey] = props.baseKey.includes('.') ? props.baseKey.split('.') : [null, props.baseKey]
  
  // Use null for reset
  const valToSet = (value === '' || value === undefined) ? null : value

  if (parentPath) {
  } else {
    const fullKey = childKey + suffix
    emit('update', { [fullKey]: valToSet })
  }
}

const updateGenericValue = (key, id, value) => {
  const suffix = id === 'desktop' ? '' : (id === 'mobile' ? '_mobile' : `_${id}`)
  const [parentPath, childKey] = key.includes('.') ? key.split('.') : [null, key]
  
  // Use null for reset
  const valToSet = (value === '' || value === undefined) ? null : value

  if (parentPath) {
    const fullParentKey = parentPath + suffix
    emit('update', { [`${fullParentKey}.${childKey}`]: valToSet })
  } else {
    const fullKey = childKey + suffix
    emit('update', { [fullKey]: valToSet })
  }
}

// Auto-scroll to active device
const itemRefs = ref({})
const setItemRef = (el, id) => {
  if (el) itemRefs.value[id] = el
}

onMounted(() => {
   // Wait for render
   setTimeout(() => {
       const activeId = currentDevice.value
       const el = itemRefs.value[activeId]
       if (el) {
           el.scrollIntoView({ behavior: 'smooth', block: 'center' })
       }
   }, 100)
})

watch(currentDevice, (newId) => {
    const el = itemRefs.value[newId]
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }
})
</script>

<style scoped>
.responsive-editor-content {
  display: flex;
  flex-direction: column;
  padding: 0;
}

.responsive-item {
  display: flex;
  flex-direction: column;
  padding: 16px;
  border: 1px solid var(--builder-border);
  background: transparent;
  transition: all 0.2s;
  border-radius: 6px;
  margin-bottom: 12px;
}

.responsive-item:last-child {
  margin-bottom: 0;
}

.responsive-item.is-active {
    background-color: transparent;
    border-color: var(--builder-accent);
    z-index: 10;
}

.item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
  cursor: pointer;
}

.mode-icon {
  color: var(--builder-text-muted);
}

.is-active .mode-icon {
    color: var(--builder-accent);
}

.item-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--builder-text-secondary);
}

.is-active .item-label {
    color: var(--builder-text-primary);
    font-weight: 600;
}

.active-dot {
    width: 6px;
    height: 6px;
    background-color: var(--builder-accent);
    border-radius: 50%;
}

.item-content {
  width: 100%;
}

/* Pattern/Mask Responsive UI Styles */
.pattern-mask-responsive-ui {
    display: flex;
    flex-direction: column;
}

.transform-controls-row {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.transform-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    color: var(--builder-text-secondary);
    cursor: pointer;
    transition: all 0.2s;
}

.transform-btn:hover {
    background: var(--builder-bg-secondary);
    border-color: var(--builder-accent);
    color: var(--builder-text-primary);
}

.transform-btn.is-active {
    background: var(--builder-accent);
    border-color: var(--builder-accent);
    color: white;
}

.bg-preview-box {
    position: relative;
    width: 100%;
    min-height: 80px;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.bg-preview-box:hover {
    border-color: var(--builder-accent);
}

/* Action buttons overlay */
.preview-actions-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
    z-index: 10;
}

.bg-preview-box:hover .preview-actions-overlay {
    opacity: 1;
}

.action-btn-group {
    display: flex;
    gap: 8px;
}

.action-icon-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--builder-bg-primary);
    border-radius: 6px;
    color: var(--builder-text-primary);
    cursor: pointer;
    transition: all 0.2s;
}

.action-icon-btn:hover {
    background: var(--builder-accent);
    color: white;
}

.action-icon-btn.remove:hover {
    background: var(--builder-danger, #ef4444);
    color: white;
}

.select-field-container {
    width: 100%;
}

.select-field-container :deep(.base-dropdown-wrapper) {
    display: block;
    width: 100%;
}

.select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 38px;
    padding: 0 12px;
    background-color: var(--builder-bg-primary);
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    color: var(--builder-text-primary);
}

.select-trigger:hover, .select-trigger.is-open {
    border-color: var(--builder-accent, #2563eb);
    background-color: var(--builder-bg-secondary);
}

.select-arrow {
    color: var(--builder-text-muted);
    transition: transform 0.2s;
}

.is-open .select-arrow {
    transform: rotate(180deg);
}

.selected-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.field-info-panel {
    background-color: var(--builder-bg-secondary);
    border-left: 3px solid var(--builder-accent);
    padding: 10px 14px;
    margin-bottom: 12px;
    font-size: 11px;
    line-height: 1.6;
    color: var(--builder-text-secondary);
    border-radius: 0 4px 4px 0;
}

.sub-fields-container {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed var(--builder-border);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.sub-field-label {
    font-size: 11px;
    color: var(--builder-text-secondary);
    margin-bottom: 4px;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}
</style>

<!-- Non-scoped styles for teleported dropdown content -->
<style>
.dropdown-preview-container {
    background: var(--builder-bg-popover);
    width: 220px;
    padding: 2px !important;
    box-sizing: border-box;
    overflow-x: hidden;
}

/* Ensure Pattern and Mask grid styles reach into teleported Popover */
.dropdown-preview-container .pattern-field,
.dropdown-preview-container .mask-field {
    padding: 0 !important;
    width: 100% !important;
}

.dropdown-preview-container .patterns-grid,
.dropdown-preview-container .masks-grid {
    display: grid !important;
    grid-template-columns: repeat(4, 1fr) !important;
    gap: 4px !important;
    width: 100% !important;
    padding: 4px !important;
    margin: 0 !important;
    box-sizing: border-box !important;
}

.dropdown-preview-container .pattern-item,
.dropdown-preview-container .mask-item {
    aspect-ratio: 1 / 1 !important;
    width: 100% !important;
    height: auto !important;
    padding: 0 !important;
    border-radius: 4px !important;
    box-sizing: border-box !important;
    overflow: hidden;
    border: 1px solid var(--builder-border) !important;
}

.dropdown-preview-container .pattern-preview,
.dropdown-preview-container .mask-preview {
    width: 100% !important;
    height: 100% !important;
    padding: 2px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-sizing: border-box !important;
}

.dropdown-preview-container .pattern-svg,
.dropdown-preview-container .mask-svg,
.dropdown-preview-container svg {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
    display: block;
}
</style>
