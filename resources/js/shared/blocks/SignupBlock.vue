<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="signup-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Signup Form'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
      <Card 
        class="signup-form-container mx-auto p-2 overflow-visible border-none bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-lg" 
        :style="containerStyles"
      >
        <CardHeader v-if="titleValue || subtitleValue" class="text-center pb-10">
          <CardTitle v-if="titleValue" class="text-3xl font-bold mb-3 border-none" :style="titleStyles">{{ titleValue }}</CardTitle>
          <CardDescription v-if="subtitleValue" class="opacity-70" :style="subtitleStyles">{{ subtitleValue }}</CardDescription>
        </CardHeader>
        
        <CardContent>
          <form @submit.prevent="handleSignup" class="flex flex-col gap-5">
            <div class="form-grid grid grid-cols-1 gap-5">
                <div v-if="showName" class="form-field">
                  <Input 
                    type="text" 
                    class="h-12 rounded-xl"
                    :placeholder="namePlaceholder" 
                    :style="inputStyles" 
                    required
                  />
                </div>
                <div class="form-field">
                  <Input 
                    type="email" 
                    class="h-12 rounded-xl"
                    :placeholder="emailPlaceholder" 
                    :style="inputStyles" 
                    required
                  />
                </div>
                <div class="form-field">
                  <Input 
                    type="password" 
                    class="h-12 rounded-xl"
                    :placeholder="passwordPlaceholder" 
                    :style="inputStyles" 
                    required
                  />
                </div>
                <div class="form-field">
                  <Input 
                    type="password" 
                    class="h-12 rounded-xl"
                    :placeholder="confirmPasswordPlaceholder" 
                    :style="inputStyles" 
                    required
                  />
                </div>
            </div>
            
            <div v-if="showTerms" class="flex items-center space-x-3 mt-2">
              <Checkbox id="terms" required />
              <Label htmlFor="terms" class="text-sm font-normal opacity-80 cursor-pointer hover:opacity-100 transition-opacity">
                {{ termsText }}
              </Label>
            </div>
            
            <Button 
                type="submit" 
                class="w-full h-14 mt-4 font-bold rounded-xl shadow-xl shadow-primary/20 transition-all hover:-translate-y-1 active:translate-y-0" 
                :style="buttonStyles"
                :disabled="loading"
            >
              <Loader2 v-if="loading" class="w-5 h-5 animate-spin mr-2" />
              <span>{{ loading ? 'Creating Account...' : buttonTextValue }}</span>
            </Button>
            
            <p v-if="showLoginLink" class="login-link text-center mt-6 text-sm opacity-70">
              {{ loginText }}
              <a :href="mode === 'view' ? (blockSettings.loginUrl || '#') : undefined" class="text-primary font-bold hover:underline ml-1" @click="handleLinkClick">{{ loginLinkTextValue }}</a>
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
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Input, Button, Label, Checkbox } from '../ui'
import { Loader2 } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getLayoutStyles,
  getVal,
  getResponsiveValue
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

const loading = ref(false)

const handleSignup = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => { loading.value = false }, 2000)
}

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const titleValue = computed(() => getResponsiveValue(settings.value, 'title', props.device))
const subtitleValue = computed(() => getResponsiveValue(settings.value, 'subtitle', props.device))
const showName = computed(() => getResponsiveValue(settings.value, 'showName', props.device) !== false)
const namePlaceholder = computed(() => getResponsiveValue(settings.value, 'namePlaceholder', props.device) || 'Full Name')
const emailPlaceholder = computed(() => getResponsiveValue(settings.value, 'emailPlaceholder', props.device) || 'Email Address')
const passwordPlaceholder = computed(() => getResponsiveValue(settings.value, 'passwordPlaceholder', props.device) || 'Password')
const confirmPasswordPlaceholder = computed(() => getResponsiveValue(settings.value, 'confirmPasswordPlaceholder', props.device) || 'Confirm Password')
const showTerms = computed(() => getResponsiveValue(settings.value, 'showTerms', props.device) !== false)
const termsText = computed(() => getResponsiveValue(settings.value, 'termsText', props.device) || 'I agree to the Terms of Service')
const showLoginLink = computed(() => getResponsiveValue(settings.value, 'showLoginLink', props.device) !== false)
const loginText = computed(() => getResponsiveValue(settings.value, 'loginText', props.device) || 'Already have an account?')
const loginLinkTextValue = computed(() => getResponsiveValue(settings.value, 'loginLinkText', props.device) || 'Login')
const buttonTextValue = computed(() => getResponsiveValue(settings.value, 'buttonText', props.device) || 'Sign Up')

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
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', props.device))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', props.device)
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', props.device) || ''
  return {
    ...styles, 
    backgroundColor: bgColor,
    color: settings.value.button_textColor || ''
  }
})
</script>

<style scoped>
.signup-block { width: 100%; }
.signup-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.signup-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
</style>
