<template>
  <div class="add-btn-wrapper" :class="{ 'add-btn-wrapper--floating': floating }">
    <button 
      class="add-module-btn"
      :class="[`add-module-btn--${type}`]"
      @click.stop="$emit('click')"
      :title="$t('builder.actions.add', { name: label })"
    >
      <component :is="icons.Plus" :size="type === 'module' ? 14 : 16" />
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Plus } from 'lucide-vue-next'

const icons = { Plus }

const props = defineProps({
  type: {
    type: String,
    default: 'module',
    validator: (v) => ['section', 'row', 'column', 'module'].includes(v)
  },
  floating: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

const { t } = useI18n()

const label = computed(() => {
  const key = props.type === 'module' ? 'builder.module' : `builder.modules.${props.type}`
  return t(key)
})
</script>

<style scoped>
.add-btn-wrapper {
  display: flex;
  justify-content: center;
  width: 100%;
  pointer-events: none;
}

/* Floating mode for Section/Row (attached to bottom center) */
.add-btn-wrapper--floating {
  position: absolute;
  bottom: -14px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 20;
  width: auto;
}

.add-module-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  pointer-events: auto; /* Re-enable clicks */
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
  color: white;
}

.add-module-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Section: Blue */
.add-module-btn--section {
  background-color: var(--builder-section);
}

/* Row: Green */
.add-module-btn--row {
  background-color: var(--builder-row);
}

/* Column/Module: Gray/Standard */
.add-module-btn--column,
.add-module-btn--module {
  background-color: var(--builder-text-secondary);
  width: 100%; /* Module button is full width bar in wireframe usually, or keep button? */
  border-radius: 4px; /* Module is rectangle */
  height: 24px;
}

.add-module-btn--module {
    background-color: var(--builder-bg-tertiary);
    color: var(--builder-text-secondary);
    box-shadow: none;
    border: 1px dashed var(--builder-border);
}

.add-module-btn--module:hover {
    background-color: var(--builder-bg-secondary);
    color: var(--builder-text-primary);
    border-color: var(--builder-accent);
    transform: none;
}
</style>
