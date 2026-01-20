<template>
  <div class="login-block" :style="wrapperStyles">
    <div class="login-form" :style="formStyles">
      <!-- Header -->
      <div v-if="settings.title" class="login-header">
        <h2 class="login-title" :style="titleStyles">{{ settings.title }}</h2>
        <p v-if="settings.subtitle" class="login-subtitle" :style="subtitleStyles">{{ settings.subtitle }}</p>
      </div>
      
      <!-- Fields -->
      <div class="login-fields">
        <div class="form-field">
          <label v-if="settings.showLabels !== false" class="field-label" :style="labelStyles">Email</label>
          <input 
            type="email" 
            class="field-input"
            :style="inputStyles"
            :placeholder="settings.usernamePlaceholder || 'Email address'"
          />
        </div>
        <div class="form-field">
          <label v-if="settings.showLabels !== false" class="field-label" :style="labelStyles">Password</label>
          <input 
            type="password" 
            class="field-input"
            :style="inputStyles"
            :placeholder="settings.passwordPlaceholder || 'Password'"
          />
        </div>
      </div>
      
      <!-- Options -->
      <div class="login-options">
        <label v-if="settings.showRememberMe !== false" class="remember-me">
          <input type="checkbox" />
          <span>Remember me</span>
        </label>
        <a 
          v-if="settings.showForgotPassword !== false"
          :href="settings.forgotPasswordUrl || '#'"
          class="forgot-password"
        >
          {{ settings.forgotPasswordText || 'Forgot password?' }}
        </a>
      </div>
      
      <!-- Button -->
      <button class="login-button" :style="buttonStyles">
        {{ settings.buttonText || 'Sign In' }}
      </button>
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
  getResponsiveValue,
  getFilterStyles,
  getTransformStyles
} from '../core/styleUtils'

const props = defineProps({
  module: { type: Object, required: true }
})

const builder = inject('builder')
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

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
    flexDirection: 'column'
  }
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  return styles
})

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const subtitleStyles = computed(() => getTypographyStyles(settings.value, 'subtitle_', device.value))
const labelStyles = computed(() => getTypographyStyles(settings.value, 'label_', device.value))
const inputStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'input_', device.value)
    // Add default input styles that aren't in typography
    return {
        ...styles,
        width: '100%',
        boxSizing: 'border-box'
    }
})

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', device.value)
    return {
        ...styles,
        width: '100%',
        backgroundColor: settings.value.button_backgroundColor || '#2059ea',
        cursor: 'pointer',
        border: 'none',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        transition: 'opacity 0.2s'
    }
})
</script>

<style scoped>
.login-block {
  width: 100%;
}

.login-header {
  text-align: center;
  margin-bottom: 24px;
}

.login-fields {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 16px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-input {
  padding: 12px 16px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  outline: none;
  transition: border-color 0.2s;
}

.field-input:focus {
  border-color: #2059ea;
}

.login-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  font-size: 14px;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.forgot-password {
  color: #2059ea;
  text-decoration: none;
}

.forgot-password:hover {
  text-decoration: underline;
}

.login-button:hover {
  opacity: 0.9;
}
</style>
