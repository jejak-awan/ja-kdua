<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="form-picker-block"
  >
    <template #default="{ settings }">
      <div 
        class="form-picker-container mx-auto w-full" 
        :style="getLayoutStyles(settings, device)"
      >
        <div v-if="mode === 'edit' && !settings.form_slug" class="p-8 border-2 border-dashed border-border rounded-lg text-center bg-muted/30">
          <FormIcon class="w-10 h-10 mx-auto mb-3 text-muted-foreground opacity-50" />
          <h3 class="text-sm font-medium text-muted-foreground">Select a form from the settings</h3>
        </div>

        <div v-else-if="settings.form_slug">
          <div v-if="settings.show_title || settings.show_description" class="mb-8 pl-1">
             <h2 v-if="settings.show_title && formName" class="text-2xl font-bold mb-2">{{ formName }}</h2>
             <p v-if="settings.show_description && formDescription" class="text-muted-foreground">{{ formDescription }}</p>
          </div>
          
          <FormRenderer 
            :slug="settings.form_slug" 
            :key="settings.form_slug"
            :preview-mode="mode === 'edit'"
          />
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import FormRenderer from '@/components/forms/FormRenderer.vue'
import FormIcon from 'lucide-vue-next/dist/esm/icons/form.js';
import { getLayoutStyles } from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'
import api from '@/services/api'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const formName = ref('')
const formDescription = ref('')

const fetchFormInfo = async () => {
  const slug = props.module.settings?.form_slug
  if (!slug) return

  try {
    const response = await api.get(`/cms/forms/${slug}`)
    formName.value = response.data.name
    formDescription.value = response.data.description
  } catch (err) {
    console.error('Failed to fetch form info:', err)
  }
}

watch(() => props.module.settings?.form_slug, fetchFormInfo)
onMounted(fetchFormInfo)
</script>

<style scoped>
.form-picker-block {
  width: 100%;
}
</style>
