<template>
  <BaseBlock :module="module" :settings="settings" class="signup-block">
    <Card class="signup-form-container mx-auto p-2 overflow-visible border-none bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-lg" :style="formStyles">
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
              class="w-full h-14 mt-4 font-bold rounded-xl shadow-xl shadow-primary/20" 
              :style="buttonStyles"
              :disabled="loading"
          >
            <Loader2 v-if="loading" class="w-5 h-5 animate-spin mr-2" />
            <span>{{ loading ? 'Creating Account...' : buttonTextValue }}</span>
          </Button>
          
          <p v-if="showLoginLink" class="login-link text-center mt-6 text-sm opacity-70">
            {{ loginText }}
            <a :href="mode === 'view' ? (settings.loginUrl || '#') : null" class="text-primary font-bold hover:underline ml-1" @click="handleLinkClick">{{ loginLinkTextValue }}</a>
          </p>
        </form>
      </CardContent>
    </Card>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Input, Button, Label, Checkbox } from '../ui'
import { Loader2 } from 'lucide-vue-next'
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
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', device.value) || ''
  return {
    ...styles, 
    backgroundColor: bgColor,
    color: settings.value.button_textColor || ''
  }
})
</script>

<style scoped>
.signup-block { width: 100%; }
</style>
