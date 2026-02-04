<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="signup-block transition-colors duration-300"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Signup Form'"
    :style="(cardStyles as any)"
  >
    <template #default="{ settings: blockSettings }">
      <Card 
        class="signup-form-container mx-auto p-2 overflow-visible border-none bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-lg" 
        :style="(containerStyles as any)"
      >
        <CardHeader v-if="titleValue || subtitleValue" class="text-center pb-10">
          <CardTitle v-if="titleValue" class="text-3xl font-bold mb-3 border-none" :style="(titleStyles as any)">{{ titleValue }}</CardTitle>
          <CardDescription v-if="subtitleValue" class="opacity-70" :style="(subtitleStyles as any)">{{ subtitleValue }}</CardDescription>
        </CardHeader>
        
        <CardContent>
          <form @submit.prevent="handleSignup" class="flex flex-col gap-5">
            <div class="form-grid grid grid-cols-1 gap-5">
                <div v-if="showName" class="form-field">
                  <Input 
                    type="text" 
                    class="h-12 rounded-xl"
                    :placeholder="namePlaceholder" 
                    :style="(inputStyles as any)" 
                    required
                  />
                </div>
                <div class="form-field">
                  <Input 
                    type="email" 
                    class="h-12 rounded-xl"
                    :placeholder="emailPlaceholder" 
                    :style="(inputStyles as any)" 
                    required
                  />
                </div>
                <div class="form-field">
                  <Input 
                    type="password" 
                    class="h-12 rounded-xl"
                    :placeholder="passwordPlaceholder" 
                    :style="(inputStyles as any)" 
                    required
                  />
                </div>
                <div class="form-field">
                  <Input 
                    type="password" 
                    class="h-12 rounded-xl"
                    :placeholder="confirmPasswordPlaceholder" 
                    :style="(inputStyles as any)" 
                    required
                  />
                </div>
            </div>
            
            <div v-if="showTerms" class="flex items-center space-x-3 mt-2">
              <Checkbox id="terms" required />
              <Label html-for="terms" class="text-sm font-normal opacity-80 cursor-pointer hover:opacity-100 transition-opacity">
                {{ termsText }}
              </Label>
            </div>
            
            <Button 
                type="submit" 
                class="w-full h-14 mt-4 font-bold rounded-xl shadow-xl shadow-primary/20 transition-colors hover:-translate-y-1 active:translate-y-0" 
                :style="(buttonStyles as any)"
                :disabled="loading"
            >
              <Loader2 v-if="loading" class="w-5 h-5 animate-spin mr-2" />
              <span>{{ loading ? 'Creating Account...' : buttonTextValue }}</span>
            </Button>
            
            <p v-if="showLoginLink" class="login-link text-center mt-6 text-sm opacity-70">
              {{ loginText }}
              <a :href="mode === 'view' ? ((blockSettings.loginUrl as string) || '#') : undefined" class="text-primary font-bold hover:underline ml-1" @click="handleLinkClick">{{ loginLinkTextValue }}</a>
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
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { 
  getTypographyStyles,
  getLayoutStyles,
  getVal,
  getResponsiveValue
} from '../utils/styleUtils'
import type { BlockInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance
  mode?: 'view' | 'edit'
  device?: 'desktop' | 'tablet' | 'mobile'
}>(), {
  mode: 'view',
  device: 'desktop'
})

const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

const loading = ref(false)

const handleSignup = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => { loading.value = false }, 2000)
}

const handleLinkClick = (event: MouseEvent) => {
    if (props.mode === 'edit') event.preventDefault()
}

const titleValue = computed(() => getResponsiveValue<string>(settings.value, 'title', props.device))
const subtitleValue = computed(() => getResponsiveValue<string>(settings.value, 'subtitle', props.device))
const showName = computed(() => getResponsiveValue<boolean>(settings.value, 'showName', props.device) !== false)
const namePlaceholder = computed(() => getResponsiveValue<string>(settings.value, 'namePlaceholder', props.device) || 'Full Name')
const emailPlaceholder = computed(() => getResponsiveValue<string>(settings.value, 'emailPlaceholder', props.device) || 'Email Address')
const passwordPlaceholder = computed(() => getResponsiveValue<string>(settings.value, 'passwordPlaceholder', props.device) || 'Password')
const confirmPasswordPlaceholder = computed(() => getResponsiveValue<string>(settings.value, 'confirmPasswordPlaceholder', props.device) || 'Confirm Password')
const showTerms = computed(() => getResponsiveValue<boolean>(settings.value, 'showTerms', props.device) !== false)
const termsText = computed(() => getResponsiveValue<string>(settings.value, 'termsText', props.device) || 'I agree to the Terms of Service')
const showLoginLink = computed(() => getResponsiveValue<boolean>(settings.value, 'showLoginLink', props.device) !== false)
const loginText = computed(() => getResponsiveValue<string>(settings.value, 'loginText', props.device) || 'Already have an account?')
const loginLinkTextValue = computed(() => getResponsiveValue<string>(settings.value, 'loginLinkText', props.device) || 'Login')
const buttonTextValue = computed(() => getResponsiveValue<string>(settings.value, 'buttonText', props.device) || 'Sign Up')

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return (getLayoutStyles(settings.value, props.device) || {}) as Record<string, string | number>
})

const titleStyles = computed(() => (getTypographyStyles(settings.value, 'title_', props.device) || {}) as Record<string, string | number>)
const subtitleStyles = computed(() => (getTypographyStyles(settings.value, 'subtitle_', props.device) || {}) as Record<string, string | number>)
const inputStyles = computed(() => (getTypographyStyles(settings.value, 'input_', props.device) || {}) as Record<string, string | number>)

const buttonStyles = computed(() => {
  const typography = (getTypographyStyles(settings.value, 'button_', props.device) || {}) as Record<string, string | number>
  const bgColor = getResponsiveValue<string>(settings.value, 'buttonBackgroundColor', props.device) || ''
  return {
    ...typography, 
    backgroundColor: bgColor,
    color: (settings.value.button_textColor as string) || ''
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
