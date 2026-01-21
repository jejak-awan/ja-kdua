<template>
  <BaseBlock :module="module" :settings="settings" class="login-block">
    <div class="login-form-container mx-auto p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 max-w-md" :style="formStyles">
      <!-- Header -->
      <div v-if="settings.title || settings.subtitle" class="login-header text-center mb-8">
        <h2 v-if="settings.title" class="login-title text-2xl font-bold mb-2" :style="titleStyles">{{ settings.title }}</h2>
        <p v-if="settings.subtitle" class="login-subtitle opacity-70 text-sm" :style="subtitleStyles">{{ settings.subtitle }}</p>
      </div>
      
      <!-- Form Controls -->
      <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
        <div class="form-field flex flex-col gap-2">
          <label v-if="settings.showLabels !== false" class="field-label text-sm font-medium" :style="labelStyles">{{ settings.usernameLabel || 'Email' }}</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><User class="w-4 h-4" /></span>
            <input 
              type="email" 
              class="field-input w-full pl-10 pr-4 py-3 rounded-lg border bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
              :style="inputStyles"
              :placeholder="settings.usernamePlaceholder || 'Email address'"
              required
            />
          </div>
        </div>

        <div class="form-field flex flex-col gap-2">
          <label v-if="settings.showLabels !== false" class="field-label text-sm font-medium" :style="labelStyles">{{ settings.passwordLabel || 'Password' }}</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><Lock class="w-4 h-4" /></span>
            <input 
              :type="showPassword ? 'text' : 'password'" 
              class="field-input w-full pl-10 pr-12 py-3 rounded-lg border bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
              :style="inputStyles"
              :placeholder="settings.passwordPlaceholder || 'Password'"
              required
            />
            <button 
                type="button" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                @click="showPassword = !showPassword"
            >
                <Eye v-if="!showPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
            </button>
          </div>
        </div>
        
        <!-- Options -->
        <div class="login-options flex justify-between items-center text-xs mt-1">
          <label v-if="settings.showRememberMe !== false" class="remember-me flex items-center gap-2 cursor-pointer">
            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
            <span class="opacity-80">Remember me</span>
          </label>
          <a 
            v-if="settings.showForgotPassword !== false"
            :href="mode === 'view' ? (settings.forgotPasswordUrl || '#') : null"
            class="forgot-password text-blue-600 hover:underline font-medium"
            @click="handleLinkClick"
          >
            {{ settings.forgotPasswordText || 'Forgot password?' }}
          </a>
        </div>
        
        <!-- Button -->
        <button 
            type="submit" 
            class="login-button w-full py-4 mt-2 rounded-xl font-bold transition-all hover:brightness-110 active:scale-[0.98] flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20" 
            :style="buttonStyles"
            :disabled="loading"
        >
          <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
          <span>{{ loading ? 'Signing in...' : (settings.buttonText || 'Sign In') }}</span>
        </button>

        <p v-if="settings.showSignupLink !== false" class="text-center text-sm mt-4 opacity-70">
            Don't have an account? 
            <a :href="mode === 'view' ? (settings.signupUrl || '#') : null" class="text-blue-600 font-semibold hover:underline" @click="handleLinkClick">Sign Up</a>
        </p>
      </form>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { User, Lock, Eye, EyeOff, Loader2 } from 'lucide-vue-next'
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

const showPassword = ref(false)
const loading = ref(false)

const handleLogin = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => { loading.value = false }, 1500)
}

const handleLinkClick = (event) => {
    if (props.mode === 'edit') event.preventDefault()
}

const formStyles = computed(() => ({}))
const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', device.value))
const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        backgroundColor: settings.value.button_backgroundColor || '#3b82f6',
        color: settings.value.button_textColor || '#ffffff'
    }
})
</script>

<style scoped>
.login-block { width: 100%; }
</style>
