<template>
  <BaseBlock :module="module" :settings="settings" class="newsletter-block">
    <div v-if="settings.title || settings.subtitle" class="newsletter-header mb-8">
      <h3 v-if="settings.title" class="newsletter-title text-2xl font-bold mb-2" :style="titleStyles">{{ settings.title }}</h3>
      <p v-if="settings.subtitle" class="newsletter-subtitle opacity-70" :style="subtitleStyles">{{ settings.subtitle }}</p>
    </div>
    
    <form @submit.prevent="handleSubscribe" class="newsletter-form flex gap-3 max-w-lg mx-auto" :class="`newsletter-form--${layoutClass}`">
      <div class="relative flex-1">
        <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <input 
            type="email" 
            class="newsletter-input w-full pl-10 pr-4 py-3.5 rounded-xl border bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
            :style="inputStyles" 
            :placeholder="settings.placeholder || 'Enter your email'" 
            required
        />
      </div>
      <button 
        type="submit" 
        class="newsletter-button px-8 py-3.5 rounded-xl font-bold transition-all hover:brightness-110 active:scale-[0.98] shadow-lg shadow-blue-500/20 whitespace-nowrap flex items-center justify-center gap-2" 
        :style="buttonStyles"
        :disabled="loading"
      >
        <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
        <span v-else>{{ settings.buttonText || 'Subscribe' }}</span>
      </button>
    </form>
    
    <div v-if="subscribed" class="success-message mt-6 text-green-600 font-medium">
        {{ settings.successMessage || 'Successfully subscribed! Thank you.' }}
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Mail, Loader2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

const loading = ref(false)
const subscribed = ref(false)

const handleSubscribe = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => {
        loading.value = false
        subscribed.value = true
    }, 1500)
}

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))

const inputStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'input_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.inputBackgroundColor || 'transparent'
    }
})

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '#3b82f6',
        color: settings.value.buttonTextColor || '#ffffff'
    }
})

const layoutClass = computed(() => getResponsiveValue(settings.value, 'layout', device.value) || 'inline')
</script>

<style scoped>
.newsletter-block { width: 100%; text-align: center; }
.newsletter-form--stacked { flex-direction: column; }
.newsletter-form--stacked .newsletter-button { width: 100%; }
.success-message { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
