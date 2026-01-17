<template>
  <div class="base-input-wrapper" :class="{ 'has-prefix': prefix, 'has-suffix': suffix, 'is-focused': isFocused, 'has-error': error, 'is-textarea': type === 'textarea' }">
    <div v-if="prefix && type !== 'textarea'" class="input-prefix">
      <slot name="prefix">{{ prefix }}</slot>
    </div>
    
    <textarea
      v-if="type === 'textarea'"
      :value="modelValue"
      class="base-input"
      :placeholder="placeholder"
      :disabled="disabled"
      @input="onInput"
      @focus="isFocused = true"
      @blur="onBlur"
      v-bind="$attrs"
    ></textarea>
    
    <input
      v-else
      :type="type"
      :value="modelValue"
      class="base-input"
      :placeholder="placeholder"
      :disabled="disabled"
      @input="onInput"
      @focus="isFocused = true"
      @blur="onBlur"
      v-bind="$attrs"
    />
    
    <div v-if="suffix && type !== 'textarea'" class="input-suffix">
      <slot name="suffix">{{ suffix }}</slot>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
    type: String,
    default: ''
  },
  error: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const isFocused = ref(false)

const onInput = (e) => {
  emit('update:modelValue', e.target.value)
}

const onBlur = (e) => {
  isFocused.value = false
  emit('blur', e)
}
</script>

<style scoped>
.base-input-wrapper {
  display: flex;
  align-items: center;
  background-color: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-md);
  overflow: hidden;
  transition: var(--transition-fast);
  width: 100%;
}

.base-input-wrapper.is-focused {
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px rgba(32, 89, 234, 0.1);
}

.base-input-wrapper.has-error {
  border-color: var(--builder-error);
}

.base-input-wrapper.is-textarea {
  align-items: stretch;
}

.input-prefix,
.input-suffix {
  padding: 0 10px;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
  background-color: transparent;
  display: flex;
  align-items: center;
  height: 100%;
}

.base-input {
  flex: 1;
  width: 100%;
  padding: 8px 12px;
  background: transparent;
  border: none;
  color: var(--builder-text-primary);
  font-size: var(--font-size-md);
  outline: none;
}

textarea.base-input {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.base-input::placeholder {
  color: var(--builder-text-muted);
  opacity: 0.6;
}

.base-input:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}
</style>
