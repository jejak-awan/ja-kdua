<template>
  <BaseBlock :module="module" :settings="settings" class="login-block">
    <Card class="login-form-container mx-auto p-2 overflow-visible border-none bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md" :style="formStyles">
      <!-- Header -->
      <CardHeader v-if="settings.title || settings.subtitle" class="text-center pb-8">
        <CardTitle v-if="settings.title" class="text-2xl font-bold mb-2 border-none" :style="titleStyles">{{ settings.title }}</CardTitle>
        <CardDescription v-if="settings.subtitle" class="opacity-70 text-sm" :style="subtitleStyles">{{ settings.subtitle }}</CardDescription>
      </CardHeader>
      
      <CardContent>
        <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
          <div class="form-field flex flex-col gap-2">
            <Label v-if="settings.showLabels !== false" class="text-sm font-medium" :style="labelStyles">{{ settings.usernameLabel || 'Email' }}</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground z-10"><User class="w-4 h-4" /></span>
              <Input 
                type="email" 
                class="pl-10 h-11"
                :style="inputStyles"
                :placeholder="settings.usernamePlaceholder || 'Email address'"
                required
              />
            </div>
          </div>

          <div class="form-field flex flex-col gap-2">
            <Label v-if="settings.showLabels !== false" class="text-sm font-medium" :style="labelStyles">{{ settings.passwordLabel || 'Password' }}</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground z-10"><Lock class="w-4 h-4" /></span>
              <Input 
                :type="showPassword ? 'text' : 'password'" 
                class="pl-10 pr-12 h-11"
                :style="inputStyles"
                :placeholder="settings.passwordPlaceholder || 'Password'"
                required
              />
              <button 
                  type="button" 
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground z-10"
                  @click="showPassword = !showPassword"
              >
                  <Eye v-if="!showPassword" class="w-4 h-4" />
                  <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <!-- Options -->
          <div class="login-options flex justify-between items-center text-xs mt-1">
            <div v-if="settings.showRememberMe !== false" class="flex items-center space-x-2">
              <Checkbox id="remember" />
              <Label htmlFor="remember" class="text-xs font-normal opacity-80 cursor-pointer">Remember me</Label>
            </div>
            <a 
              v-if="settings.showForgotPassword !== false"
              :href="mode === 'view' ? (settings.forgotPasswordUrl || '#') : null"
              class="forgot-password text-primary hover:underline font-medium"
              @click="handleLinkClick"
            >
              {{ settings.forgotPasswordText || 'Forgot password?' }}
            </a>
          </div>
          
          <!-- Button -->
          <Button 
              type="submit" 
              class="w-full h-12 mt-2 font-bold shadow-lg shadow-primary/20" 
              :style="buttonStyles"
              :disabled="loading"
          >
            <Loader2 v-if="loading" class="w-5 h-5 animate-spin mr-2" />
            <span>{{ loading ? 'Signing in...' : (settings.buttonText || 'Sign In') }}</span>
          </Button>

          <p v-if="settings.showSignupLink !== false" class="text-center text-sm mt-4 opacity-70">
              Don't have an account? 
              <a :href="mode === 'view' ? (settings.signupUrl || '#') : null" class="text-primary font-semibold hover:underline" @click="handleLinkClick">Sign Up</a>
          </p>
        </form>
      </CardContent>
    </Card>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Label, Input, Button, Checkbox } from '../ui'
import { User, Lock, Eye, EyeOff, Loader2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || props.device)

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
        backgroundColor: settings.value.button_backgroundColor || '',
        color: settings.value.button_textColor || ''
    }
})
</script>

<style scoped>
.login-block { width: 100%; }
</style>
