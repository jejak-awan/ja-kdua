<template>
  <BaseModal
    :is-open="isOpen"
    :title="title"
    :width="400"
    :show-close="false"
    :close-on-backdrop="false"
  >
    <div class="confirm-modal-content">
      <div class="confirm-icon" :class="iconClass">
        <component :is="iconComponent" :size="28" />
      </div>
      <p class="confirm-message">{{ message }}</p>
    </div>

    <template #footer>
      <BaseButton variant="secondary" @click="handleCancel">
        {{ cancelText }}
      </BaseButton>
      <BaseButton :variant="confirmVariant" @click="handleConfirm">
        {{ confirmText }}
      </BaseButton>
    </template>
  </BaseModal>
</template>

<script setup>
import { computed } from 'vue'
import { AlertTriangle, AlertCircle, Info, Trash2, HelpCircle } from 'lucide-vue-next'
import BaseModal from '../ui/BaseModal.vue'
import { BaseButton } from '../ui'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm'
  },
  message: {
    type: String,
    default: 'Are you sure?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  type: {
    type: String,
    default: 'warning', // danger, warning, info, delete
    validator: (val) => ['danger', 'warning', 'info', 'delete'].includes(val)
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const iconComponent = computed(() => {
  switch (props.type) {
    case 'danger':
      return AlertCircle
    case 'delete':
      return Trash2
    case 'info':
      return Info
    case 'warning':
    default:
      return AlertTriangle
  }
})

const iconClass = computed(() => `icon-${props.type}`)

const confirmVariant = computed(() => {
  return props.type === 'danger' || props.type === 'delete' ? 'danger' : 'primary'
})

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<style scoped>
.confirm-modal-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
}

.confirm-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
}

.icon-danger,
.icon-delete {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.icon-warning {
  background: rgba(245, 158, 11, 0.15);
  color: #f59e0b;
}

.icon-info {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.confirm-message {
  margin: 0;
  font-size: 14px;
  line-height: 1.6;
  color: var(--builder-text-secondary);
  max-width: 300px;
}
</style>
