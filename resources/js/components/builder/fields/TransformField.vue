<template>
  <div class="transform-field">
    <BaseCollapsible :default-open="true">
      <template #title>
        <BaseLabel :muted="false" class="primary-label">
          <template #prefix><Move :size="14" /></template>
          {{ t('builder.settings.groups.transform.label') }}
        </BaseLabel>
      </template>

      <div class="field-content px-3 pb-2">
         <!-- Tabs for different transform types -->
         <BaseSegmentedControl
            v-model="activeTab"
            :options="translatedTabs"
            :full-width="true"
            class="mb-4"
         />

         <div class="transform-controls flex flex-col gap-4">
            
            <!-- Scale Tab -->
            <div v-if="activeTab === 'scale'" class="control-group">
                <div class="control-row">
                    <BaseLabel>{{ t('builder.settings.fields.scale') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.scale" :min="0" :max="200" unit="%" :placeholder-value="100" />
                </div>
            </div>

            <!-- Translate Tab -->
            <div v-if="activeTab === 'translate'" class="control-group">
                <div class="control-row">
                    <BaseLabel>{{ t('builder.settings.fields.translateX') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.translate_x" :min="-500" :max="500" unit="px" :placeholder-value="0" />
                </div>
                <div class="control-row mt-3">
                    <BaseLabel>{{ t('builder.settings.fields.translateY') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.translate_y" :min="-500" :max="500" unit="px" :placeholder-value="0" />
                </div>
            </div>

            <!-- Rotate Tab -->
            <div v-if="activeTab === 'rotate'" class="control-group">
                <div class="control-row">
                    <BaseLabel>{{ t('builder.settings.fields.rotateX') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.rotate_x" :min="0" :max="360" unit="deg" :placeholder-value="0" />
                </div>
                <div class="control-row mt-3">
                    <BaseLabel>{{ t('builder.settings.fields.rotateY') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.rotate_y" :min="0" :max="360" unit="deg" :placeholder-value="0" />
                </div>
                <div class="control-row mt-3">
                    <BaseLabel>{{ t('builder.settings.fields.rotateZ') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.rotate_z" :min="0" :max="360" unit="deg" :placeholder-value="0" />
                </div>
            </div>

             <!-- Skew Tab -->
            <div v-if="activeTab === 'skew'" class="control-group">
                <div class="control-row">
                    <BaseLabel>{{ t('builder.settings.fields.skewX') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.skew_x" :min="-180" :max="180" unit="deg" :placeholder-value="0" />
                </div>
                <div class="control-row mt-3">
                    <BaseLabel>{{ t('builder.settings.fields.skewY') }}</BaseLabel>
                    <BaseSliderInput v-model.number="localValue.skew_y" :min="-180" :max="180" unit="deg" :placeholder-value="0" />
                </div>
            </div>

             <!-- Origin -->
            <div class="control-row mt-2 border-t pt-3">
                <BaseLabel>{{ t('builder.settings.fields.origin') }}</BaseLabel>
                 <select class="builder-select w-full" v-model="localValue.origin">
                     <option value="center">Center</option>
                     <option value="top left">Top Left</option>
                     <option value="top center">Top Center</option>
                     <option value="top right">Top Right</option>
                     <option value="center left">Center Left</option>
                     <option value="center right">Center Right</option>
                     <option value="bottom left">Bottom Left</option>
                     <option value="bottom center">Bottom Center</option>
                     <option value="bottom right">Bottom Right</option>
                </select>
            </div>

         </div>
      </div>
    </BaseCollapsible>
  </div>
</template>

<script setup>
import { ref, watch, reactive, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Move, Maximize, RefreshCw, Smartphone, GitMerge } from 'lucide-vue-next'
import { BaseLabel, BaseSliderInput, BaseCollapsible, BaseSegmentedControl } from '../ui'

const props = defineProps({
  field: Object,
  value: {
    type: Object,
    default: () => ({
      scale: 100,
      translate_x: 0,
      translate_y: 0,
      rotate_x: 0,
      rotate_y: 0,
      rotate_z: 0,
      skew_x: 0,
      skew_y: 0,
      origin: 'center'
    })
  },
  placeholderValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:value'])
const { t } = useI18n()

const activeTab = ref('scale')

const translatedTabs = computed(() => [
    { label: t('builder.settings.groups.transform.tabs.scale') || 'Scale', value: 'scale' },
    { label: t('builder.settings.groups.transform.tabs.translate') || 'Move', value: 'translate' },
    { label: t('builder.settings.groups.transform.tabs.rotate') || 'Rotate', value: 'rotate' },
    { label: t('builder.settings.groups.transform.tabs.skew') || 'Skew', value: 'skew' }
])

// Local state
const localValue = reactive({ 
    scale: 100,
    translate_x: 0,
    translate_y: 0,
    rotate_x: 0,
    rotate_y: 0,
    rotate_z: 0,
    skew_x: 0,
    skew_y: 0,
    origin: 'center'
})

watch(() => props.value, (newVal) => {
    if (newVal) {
        Object.keys(localValue).forEach(key => {
            if (newVal[key] !== undefined) {
                localValue[key] = newVal[key]
            }
        })
    }
}, { deep: true, immediate: true })

watch(localValue, (newVal) => {
    emit('update:value', { ...newVal })
}, { deep: true })

</script>

<style scoped>
.control-row {
    display: flex;
    flex-direction: column;
}
</style>
