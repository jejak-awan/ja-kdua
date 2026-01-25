<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="login-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Login Form'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <Card 
        class="login-form-container mx-auto p-2 overflow-visible border-none bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md" 
        :style="containerStyles"
      >
        <!-- Header -->
        <CardHeader v-if="blockSettings.title || blockSettings.subtitle" class="text-center pb-8">
          <CardTitle v-if="blockSettings.title" class="text-2xl font-bold mb-2 border-none" :style="titleStyles">{{ blockSettings.title }}</CardTitle>
          <CardDescription v-if="blockSettings.subtitle" class="opacity-70 text-sm" :style="subtitleStyles">{{ blockSettings.subtitle }}</CardDescription>
        </CardHeader>
        
        <CardContent>
          <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
            <div class="form-field flex flex-col gap-2">
              <Label v-if="blockSettings.showLabels !== false" class="text-sm font-medium" :style="labelStyles">{{ blockSettings.usernameLabel || 'Email' }}</Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground z-10"><User class="w-4 h-4" /></span>
                <Input 
                  type="email" 
                  class="pl-10 h-11"
                  :style="inputStyles"
                  :placeholder="blockSettings.usernamePlaceholder || 'Email address'"
                  required
                />
              </div>
            </div>

            <div class="form-field flex flex-col gap-2">
              <Label v-if="blockSettings.showLabels !== false" class="text-sm font-medium" :style="labelStyles">{{ blockSettings.passwordLabel || 'Password' }}</Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground z-10"><Lock class="w-4 h-4" /></span>
                <Input 
                  :type="showPassword ? 'text' : 'password'" 
                  class="pl-10 pr-12 h-11"
                  :style="inputStyles"
                  :placeholder="blockSettings.passwordPlaceholder || 'Password'"
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
              <div v-if="blockSettings.showRememberMe !== false" class="flex items-center space-x-2">
                <Checkbox id="remember" />
                <Label htmlFor="remember" class="text-xs font-normal opacity-80 cursor-pointer">Remember me</Label>
              </div>
              <a 
                v-if="blockSettings.showForgotPassword !== false"
                :href="mode === 'view' ? (blockSettings.forgotPasswordUrl || '#') : undefined"
                class="forgot-password text-primary hover:underline font-medium"
                @click="handleLinkClick"
              >
                {{ blockSettings.forgotPasswordText || 'Forgot password?' }}
              </a>
            </div>
            
            <!-- Button -->
            <Button 
                type="submit" 
                class="w-full h-12 mt-2 font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-1 active:translate-y-0" 
                :style="buttonStyles"
                :disabled="loading"
            >
              <Loader2 v-if="loading" class="w-5 h-5 animate-spin mr-2" />
              <span>{{ loading ? 'Signing in...' : (blockSettings.buttonText || 'Sign In') }}</span>
            </Button>

            <p v-if="blockSettings.showSignupLink !== false" class="text-center text-sm mt-4 opacity-70">
                Don't have an account? 
                <a :href="mode === 'view' ? (blockSettings.signupUrl || '#') : undefined" class="text-primary font-semibold hover:underline" @click="handleLinkClick">Sign Up</a>
            </p>
          </form>
        </CardContent>
      </Card>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Label, Input, Button, Checkbox } from '../ui'
import { User, Lock, Eye, EyeOff, Loader2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getLayoutStyles,
  getVal
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

const showPassword = ref(false)
const loading = ref(false)

const handleLogin = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => { loading.value = false }, 1500)
}

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const cardStyles = computed(() => {
    const styles: Record<string, any> = {}
    const hoverScale = getVal(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', props.device))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', props.device))
const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', props.device))
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', props.device))
const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device)
    return {
        ...styles,
        backgroundColor: settings.value.button_backgroundColor || '',
        color: settings.value.button_textColor || ''
    }
})
</script>

<style scoped>
.login-block { width: 100%; }
.login-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.login-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
