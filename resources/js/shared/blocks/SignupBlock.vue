<template>
  <BaseBlock :module="module" :settings="settings" class="signup-block">
    <div class="signup-form-container mx-auto p-10 bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 max-w-lg" :style="formStyles">
      <div v-if="titleValue || subtitleValue" class="form-header text-center mb-10">
        <h2 v-if="titleValue" class="form-title text-3xl font-bold mb-3" :style="titleStyles">{{ titleValue }}</h2>
        <p v-if="subtitleValue" class="form-subtitle opacity-70" :style="subtitleStyles">{{ subtitleValue }}</p>
      </div>
      
      <form @submit.prevent="handleSignup" class="flex flex-col gap-5">
        <div class="form-grid grid grid-cols-1 gap-5">
            <div v-if="showName" class="form-field">
              <input 
                type="text" 
                class="field-input w-full px-4 py-3.5 rounded-xl border bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                :placeholder="namePlaceholder" 
                :style="inputStyles" 
                required
              />
            </div>
            <div class="form-field">
              <input 
                type="email" 
                class="field-input w-full px-4 py-3.5 rounded-xl border bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                :placeholder="emailPlaceholder" 
                :style="inputStyles" 
                required
              />
            </div>
            <div class="form-field">
              <input 
                type="password" 
                class="field-input w-full px-4 py-3.5 rounded-xl border bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                :placeholder="passwordPlaceholder" 
                :style="inputStyles" 
                required
              />
            </div>
            <div class="form-field">
              <input 
                type="password" 
                class="field-input w-full px-4 py-3.5 rounded-xl border bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" 
                :placeholder="confirmPasswordPlaceholder" 
                :style="inputStyles" 
                required
              />
            </div>
        </div>
        
        <label v-if="showTerms" class="terms-checkbox flex items-center gap-3 mt-2 cursor-pointer group">
          <input type="checkbox" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" required />
          <span class="text-sm opacity-80 group-hover:opacity-100 transition-opacity">{{ termsText }}</span>
        </label>
        
        <button 
            type="submit" 
            class="form-button w-full py-4 mt-4 rounded-xl font-bold transition-all hover:brightness-110 active:scale-[0.98] flex items-center justify-center gap-2 shadow-xl shadow-blue-500/20" 
            :style="buttonStyles"
            :disabled="loading"
        >
          <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
          <span>{{ loading ? 'Creating Account...' : buttonTextValue }}</span>
        </button>
        
        <p v-if="showLoginLink" class="login-link text-center mt-6 text-sm opacity-70">
          {{ loginText }}
          <a :href="mode === 'view' ? (settings.loginUrl || '#') : null" class="text-blue-600 font-bold hover:underline ml-1" @click="handleLinkClick">{{ loginLinkTextValue }}</a>
        </p>
      </form>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Loader2 } from 'lucide-vue-next'
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

const handleSignup = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => { loading.value = false }, 2000)
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const titleValue = computed(() => getResponsiveValue(settings.value, 'title', device.value))
const subtitleValue = computed(() => getResponsiveValue(settings.value, 'subtitle', device.value))
const showName = computed(() => getResponsiveValue(settings.value, 'showName', device.value) !== false)
const namePlaceholder = computed(() => getResponsiveValue(settings.value, 'namePlaceholder', device.value) || 'Full Name')
const emailPlaceholder = computed(() => getResponsiveValue(settings.value, 'emailPlaceholder', device.value) || 'Email Address')
const passwordPlaceholder = computed(() => getResponsiveValue(settings.value, 'passwordPlaceholder', device.value) || 'Password')
const confirmPasswordPlaceholder = computed(() => getResponsiveValue(settings.value, 'confirmPasswordPlaceholder', device.value) || 'Confirm Password')
const showTerms = computed(() => getResponsiveValue(settings.value, 'showTerms', device.value) !== false)
const termsText = computed(() => getResponsiveValue(settings.value, 'termsText', device.value) || 'I agree to the Terms of Service')
const showLoginLink = computed(() => getResponsiveValue(settings.value, 'showLoginLink', device.value) !== false)
const loginText = computed(() => getResponsiveValue(settings.value, 'loginText', device.value) || 'Already have an account?')
const loginLinkTextValue = computed(() => getResponsiveValue(settings.value, 'loginLinkText', device.value) || 'Login')
const buttonTextValue = computed(() => getResponsiveValue(settings.value, 'buttonText', device.value) || 'Sign Up')

const formStyles = computed(() => ({}))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', device.value))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', device.value) || '#3b82f6'
  return {
    ...styles, 
    backgroundColor: bgColor,
    color: settings.value.button_textColor || '#ffffff'
  }
})
</script>

<style scoped>
.signup-block { width: 100%; }
</style>
