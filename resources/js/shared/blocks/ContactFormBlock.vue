<template>
  <BaseBlock :module="module" :settings="settings" class="contact-form-block">
    <div class="contact-form-container mx-auto" :style="formStyles">
      <!-- Header -->
      <div v-if="settings.title || settings.description" class="form-header text-center mb-10">
        <h2 v-if="settings.title" class="form-title text-3xl font-bold mb-3" :style="titleStyles">{{ settings.title }}</h2>
        <p v-if="settings.description" class="form-description opacity-70" :style="descriptionStyles">{{ settings.description }}</p>
      </div>
      
      <!-- Fields Container -->
      <form @submit.prevent="handleSubmit" class="form-grid grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
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
          <button 
            type="submit" 
            class="form-button inline-flex items-center justify-center gap-2 px-10 py-4 rounded-full font-bold transition-all hover:scale-105 active:scale-95 disabled:opacity-50"
            :style="buttonStyles"
            :disabled="isSubmitting"
          >
            <Loader2 v-if="isSubmitting" class="w-5 h-5 animate-spin" />
            <Send v-else class="w-5 h-5" />
            <span>{{ isSubmitting ? (settings.submittingText || 'Sending...') : (settings.buttonText || 'Send Message') }}</span>
          </button>
          
          <div v-if="submitted" class="success-message mt-6 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800">
            {{ settings.successMessage || 'Thank you! Your message has been sent.' }}
          </div>
        </div>
      </form>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import ContactFieldBlock from './ContactFieldBlock.vue'
import { Send, Loader2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  nestedBlocks: { type: Array, default: () => [] }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

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

const formStyles = computed(() => {
    const styles = {}
    return styles
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const descriptionStyles = computed(() => getTypographyStyles(settings.value, 'description_', device.value))

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#3b82f6',
        color: settings.value.buttonTextColor || '#ffffff'
    }
})
</script>

<style scoped>
.contact-form-block { width: 100%; }
.success-message { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
