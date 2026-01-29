<template>
  <BaseBlock 
    :module="module" 
    :mode="mode" 
    :device="device"
    class="newsletter-block transition-colors duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Newsletter Signup'"
    :style="cardStyles"
  >
    <template #default="{ settings: blockSettings }">
        <div v-if="blockSettings.title || blockSettings.subtitle" class="newsletter-header mb-8 text-center">
          <h3 v-if="blockSettings.title" class="newsletter-title text-2xl font-bold mb-2" :style="titleStyles">{{ blockSettings.title }}</h3>
          <p v-if="blockSettings.subtitle" class="newsletter-subtitle opacity-70" :style="subtitleStyles">{{ blockSettings.subtitle }}</p>
        </div>
        
        <form 
            @submit.prevent="handleSubscribe" 
            class="newsletter-form flex gap-3 max-w-lg mx-auto" 
            :class="[layout === 'stacked' ? 'flex-col' : '']"
            :style="containerStyles"
        >
          <div class="relative flex-1">
            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground z-10" />
            <Input 
                type="email" 
                class="pl-10 h-12 rounded-xl"
                :style="inputStyles"
                :placeholder="blockSettings.placeholder || 'Enter your email'" 
                required
            />
          </div>
          <Button 
            type="submit" 
            class="h-12 px-8 rounded-xl font-bold shadow-lg shadow-primary/20 transition-colors hover:-translate-y-1 active:translate-y-0" 
            :style="buttonStyles"
            :disabled="loading"
          >
            <Loader2 v-if="loading" class="w-5 h-5 animate-spin mr-2" />
            <span>{{ loading ? 'Subscribing...' : (blockSettings.buttonText || 'Subscribe') }}</span>
          </Button>
        </form>
        
        <div v-if="subscribed" class="success-message mt-6 text-green-600 font-medium text-center">
            {{ blockSettings.successMessage || 'Successfully subscribed! Thank you.' }}
        </div>
    </template>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Input, Button } from '../ui'
import Mail from 'lucide-vue-next/dist/esm/icons/mail.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';import { 
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
const subscribed = ref(false)

const handleSubscribe = () => {
    if (props.mode === 'edit') return
    loading.value = true
    setTimeout(() => {
        loading.value = false
        subscribed.value = true
    }, 1500)
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

const inputStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'input_', props.device)
    return {
        ...styles,
        backgroundColor: settings.value.inputBackgroundColor || 'transparent'
    }
})

const buttonStyles = computed(() => {
    const styles = getTypographyStyles(settings.value, 'button_', props.device)
    return {
        ...styles,
        backgroundColor: settings.value.buttonBackgroundColor || '',
        color: settings.value.buttonTextColor || ''
    }
})

const layout = computed(() => getResponsiveValue(settings.value, 'layout', props.device) || 'inline')
</script>

<style scoped>
.newsletter-block { width: 100%; }
.newsletter-block {
    transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.3s ease;
}
.newsletter-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.success-message { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
