<template>
  <div class="signup-block" :style="wrapperStyles">
    <div class="signup-form" :style="formStyles">
      <div v-if="titleValue" class="form-header">
        <h2 class="form-title" :style="titleStyles">{{ titleValue }}</h2>
        <p v-if="subtitleValue" class="form-subtitle" :style="subtitleStyles">{{ subtitleValue }}</p>
      </div>
      
      <div class="form-fields">
        <div v-if="showName" class="form-field">
          <input type="text" class="field-input" :placeholder="namePlaceholder" :style="inputStyles" />
        </div>
        <div class="form-field">
          <input type="email" class="field-input" :placeholder="emailPlaceholder" :style="inputStyles" />
        </div>
        <div class="form-field">
          <input type="password" class="field-input" :placeholder="passwordPlaceholder" :style="inputStyles" />
        </div>
        <div class="form-field">
          <input type="password" class="field-input" :placeholder="confirmPasswordPlaceholder" :style="inputStyles" />
        </div>
      </div>
      
      <label v-if="showTerms" class="terms-checkbox">
        <input type="checkbox" />
        <span>{{ termsText }}</span>
      </label>
      
      <button class="form-button" :style="buttonStyles">{{ buttonTextValue }}</button>
      
      <p v-if="showLoginLink" class="login-link">
        {{ loginText }}
        <a :href="settings.loginUrl || '#'">{{ loginLinkTextValue }}</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { 
  getBackgroundStyles, 
  getSpacingStyles, 
  getBorderStyles, 
  getBoxShadowStyles, 
  getSizingStyles, 
  getTypographyStyles,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({ module: { type: Object, required: true } })

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device || 'desktop')

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

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  return styles
})

const formStyles = computed(() => {
  const styles = { 
    display: 'flex', 
    flexDirection: 'column',
    overflow: 'hidden'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const inputStyles = computed(() => getTypographyStyles(settings.value, 'input_', device.value))

const buttonStyles = computed(() => {
  const styles = getTypographyStyles(settings.value, 'button_', device.value)
  const bgColor = getResponsiveValue(settings.value, 'buttonBackgroundColor', device.value) || '#2059ea'
  return {
    ...styles, 
    width: '100%', 
    padding: '14px 24px', 
    backgroundColor: bgColor,
    border: 'none', 
    borderRadius: '6px', 
    cursor: 'pointer'
  }
})
</script>

<style scoped>
.signup-block { width: 100%; }
.form-header { text-align: center; margin-bottom: 24px; }
.form-title { margin: 0 0 8px; }
.form-subtitle { margin: 0; }
.form-fields { display: flex; flex-direction: column; gap: 16px; margin-bottom: 16px; }
.field-input { width: 100%; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 6px; outline: none; box-sizing: border-box; }
.field-input:focus { border-color: #2059ea; }
.terms-checkbox { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; font-size: 14px; cursor: pointer; }
.login-link { text-align: center; margin-top: 16px; font-size: 14px; color: #666; }
.login-link a { color: #2059ea; text-decoration: none; }
</style>
