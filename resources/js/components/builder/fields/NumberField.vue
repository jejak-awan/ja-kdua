<template>
  <div class="number-field">
    <BaseInput
      type="number"
      v-model="internalValue"
      :min="field.min"
      :max="field.max"
      :step="field.step || 1"
      :placeholder="value ? '' : (placeholderValue !== null ? String(placeholderValue) : '')"
      class="number-input-base"
    >
      <template #suffix v-if="showUnit">
        <select
          class="unit-select-minimal"
          :value="unit"
          @change="$emit('update:unit', $event.target.value)"
        >
          <option v-for="u in units" :key="u" :value="u">{{ u }}</option>
        </select>
      </template>
    </BaseInput>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CSS_UNITS } from '../core/constants'
import { BaseInput } from '../ui'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: Number,
    default: 0
  },
  unit: {
    type: String,
    default: 'px'
  },
  placeholderValue: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:value', 'update:unit'])

const internalValue = computed({
  get: () => props.value,
  set: (val) => emit('update:value', Number(val))
})

const showUnit = computed(() => props.field.unit !== false)
const units = computed(() => props.field.units || CSS_UNITS)
</script>

<style scoped>
.number-field {
  width: 100%;
}

.unit-select-minimal {
  background: transparent;
  border: none;
  color: var(--builder-text-secondary);
  font-size: 11px;
  cursor: pointer;
  outline: none;
  padding: 0 4px;
}

.unit-select-minimal:hover {
  color: var(--builder-text-primary);
}

/* Hide number arrows */
:deep(.base-input::-webkit-outer-spin-button),
:deep(.base-input::-webkit-inner-spin-button) {
  -webkit-appearance: none;
  margin: 0;
}

:deep(.base-input[type=number]) {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>
