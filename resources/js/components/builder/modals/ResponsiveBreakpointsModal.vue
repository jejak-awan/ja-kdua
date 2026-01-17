<template>
  <BaseModal
    :is-open="isOpen"
    :title="t('builder.modals.breakpoints.title')"
    :width="400"
    draggable
    placement="center"
    @close="close"
  >
    <div class="popover-body-content">
      <div 
        v-for="bp in breakpointsData" 
        :key="bp.id"
        class="breakpoint-row"
        :class="{ 'is-base': bp.isBase, 'is-disabled': !bp.enabled }"
      >
        <div class="bp-icon">
          <component :is="bp.icon" :size="16" />
        </div>
        <span class="bp-name">{{ getBreakpointName(bp.id) }}</span>
        <span class="bp-operator">{{ bp.operator }}</span>
        
        <div class="bp-control">
          <BaseInput 
            v-if="!bp.isBase"
            v-model="bp.value" 
            :disabled="!bp.enabled"
            class="bp-value-input"
          />
          <span v-else class="bp-base-label">{{ t('builder.modals.breakpoints.base') }}</span>
        </div>

        <BaseToggle 
          v-if="!bp.isBase" 
          v-model="bp.enabled" 
        />
      </div>
    </div>
    
    <template #footer>
      <BaseButton variant="secondary" @click="close">{{ t('builder.modals.breakpoints.cancel') }}</BaseButton>
      <BaseButton variant="primary" @click="save">{{ t('builder.modals.breakpoints.save') }}</BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Smartphone, Tablet, Monitor } from 'lucide-vue-next'
import { BaseModal, BaseToggle, BaseButton, BaseInput } from '../ui'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'save'])
const { t } = useI18n()

const breakpointsData = ref([
  { id: 'phone', operator: '<', value: '767px', enabled: true, icon: Smartphone, isBase: false },
  { id: 'phone-wide', operator: '<', value: '860px', enabled: false, icon: Smartphone, isBase: false },
  { id: 'tablet', operator: '<', value: '980px', enabled: true, icon: Tablet, isBase: false },
  { id: 'tablet-wide', operator: '<', value: '1024px', enabled: false, icon: Tablet, isBase: false },
  { id: 'desktop', operator: '', value: '', enabled: true, icon: Monitor, isBase: true },
  { id: 'widescreen', operator: '>', value: '1280px', enabled: false, icon: Monitor, isBase: false },
  { id: 'ultra-wide', operator: '>', value: '1440px', enabled: false, icon: Monitor, isBase: false }
])

const getBreakpointName = (id) => {
    const map = {
        'phone': 'mobile',
        'phone-wide': 'phoneWide',
        'tablet': 'tablet',
        'tablet-wide': 'tabletWide',
        'desktop': 'desktop',
        'widescreen': 'widescreen',
        'ultra-wide': 'ultraWide'
    }
    return t(`builder.breakpoints.${map[id] || id}`)
}

const close = () => {
  emit('close')
}

const save = () => {
  emit('save', breakpointsData.value)
  close()
}
</script>

<style scoped>
.popover-body-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.breakpoint-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 0;
}

.breakpoint-row.is-disabled {
  opacity: 0.5;
}

.bp-icon {
  color: var(--builder-text-muted, #6b7280);
  width: 20px;
  display: flex;
  justify-content: center;
  flex-shrink: 0;
}

.breakpoint-row:not(.is-disabled) .bp-icon {
  color: var(--builder-text-secondary, #9ca3af);
}

.bp-name {
  flex: 1;
  font-size: 12px;
  color: var(--builder-text-secondary, #9ca3af);
  font-weight: 500;
}

.breakpoint-row:not(.is-disabled):not(.is-base) .bp-name,
.breakpoint-row.is-base .bp-name {
  color: var(--builder-text-primary, #e5e7eb);
}

.bp-operator {
  color: var(--builder-text-muted, #6b7280);
  font-size: 11px;
  width: 14px;
  text-align: center;
}

.bp-control {
  width: 80px;
  display: flex;
  justify-content: center;
}

.bp-value-input {
  text-align: center;
}

.bp-base-label {
  font-size: 11px;
  color: var(--builder-text-muted);
  font-style: italic;
}
</style>
