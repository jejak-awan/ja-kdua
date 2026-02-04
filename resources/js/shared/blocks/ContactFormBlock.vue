<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="contact-form-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Contact Form'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <div 
        class="contact-form-container mx-auto" 
        :style="(containerStyles as any)"
      >
        <!-- Header -->
        <div v-if="blockSettings.title || blockSettings.description" class="form-header text-center mb-10">
          <h2 v-if="blockSettings.title" class="form-title text-3xl font-bold mb-3" :style="(titleStyles as any)">{{ blockSettings.title }}</h2>
          <p v-if="blockSettings.description" class="form-description opacity-70" :style="(descriptionStyles as any)">{{ blockSettings.description }}</p>
        </div>
        
        <!-- Fields Container -->
        <form @submit.prevent="handleSubmit" :class="['form-grid grid gap-6 mb-8', device === 'desktop' ? 'grid-cols-2' : 'grid-cols-1']">
          <!-- Builder Mode -->
          <template v-if="mode === 'edit'">
              <slot />
          </template>
          
          <!-- Renderer Mode -->
          <template v-else>
              <ContactFieldBlock 
                v-for="child in nestedBlocks" 
                :key="child.id"
                :module="child"
                mode="view"
              />
          </template>
          
          <!-- Submit Button -->
          <div class="form-footer col-span-full text-center mt-6">
            <Button 
              type="submit" 
              class="h-14 px-12 rounded-full font-bold shadow-xl shadow-primary/20 transition-colors hover:-translate-y-1 active:translate-y-0"
              :style="(buttonStyles as any)"
              :disabled="isSubmitting"
            >
              <Loader2 v-if="isSubmitting" class="w-5 h-5 animate-spin mr-2" />
              <Send v-else class="w-5 h-5 mr-2" />
              <span>{{ isSubmitting ? (blockSettings.submittingText || 'Sending...') : (blockSettings.buttonText || 'Send Message') }}</span>
            </Button>
            
            <div v-if="submitted" class="success-message mt-6 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800">
              {{ blockSettings.successMessage || 'Thank you! Your message has been sent.' }}
            </div>
          </div>
        </form>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import ContactFieldBlock from './ContactFieldBlock.vue'
import { Button } from '../ui'
import Send from 'lucide-vue-next/dist/esm/icons/send.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { 
  getTypographyStyles,
  getLayoutStyles,
  getVal
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
  nestedBlocks?: BlockInstance[]
}>(), {
  mode: 'view',
  device: 'desktop',
  nestedBlocks: () => []
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const isSubmitting = ref(false)
const submitted = ref(false)

const handleSubmit = () => {
    if (props.mode === 'edit') return
    isSubmitting.value = true
    setTimeout(() => {
        isSubmitting.value = false
        submitted.value = true
    }, 1500)
}

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', props.device))

const buttonStyles = computed(() => {
    const styles = (getTypographyStyles(settings.value, 'button_', props.device) || {}) as Record<string, string | number>
    return {
        ...styles,
        backgroundColor: (settings.value.buttonBackgroundColor as string) || '',
        color: (settings.value.buttonTextColor as string) || ''
    }
})
</script>

<style scoped>
.contact-form-block { width: 100%; }
.contact-form-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.contact-form-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.success-message { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
