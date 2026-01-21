<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ mode: blockMode, settings, device: blockDevice }">
      <div 
        class="button-block-container w-full" 
        :class="alignmentClass(settings, blockDevice)"
      >
        <a 
          :href="getVal(settings, 'url') || '#'" 
          :target="getVal(settings, 'openNewTab') ? '_blank' : '_self'"
          class="button-element inline-flex items-center justify-center gap-2 transition-all duration-300 relative overflow-hidden group"
          :class="[
            sizeClass(settings, blockDevice),
            !getVal(settings, 'useCustomColors') ? variantClass(settings) : '',
            hoverEffectClass(settings),
            radiusClass(settings),
            shadowClass(settings)
          ]"
          :style="buttonStyles(settings, blockDevice)"
          @click="onButtonClick"
        >
          <!-- Background FX (e.g. shine) -->
          <div 
            v-if="getVal(settings, 'hoverEffect') === 'shine'"
            class="absolute inset-0 -translate-x-full group-hover:animate-shine bg-gradient-to-r from-transparent via-white/20 to-transparent z-0"
          ></div>

          <!-- Icon Left -->
          <LucideIcon 
            v-if="getVal(settings, 'iconName') && getVal(settings, 'iconPosition') === 'left'" 
            :name="getVal(settings, 'iconName')" 
            class="button-icon z-10 transition-transform duration-300 shrink-0"
            :style="iconStyles(settings, 'left')"
          />

          <!-- Text content -->
          <div 
            v-if="blockMode === 'edit'"
            ref="editableRef"
            contenteditable="true"
            class="button-text z-10 outline-none"
            @blur="onTextBlur($event, settings)"
            @click.stop
            v-html="getVal(settings, 'text') || 'Click Here'"
          ></div>
          <span v-else class="button-text z-10">{{ getVal(settings, 'text') || 'Click Here' }}</span>

          <!-- Icon Right -->
          <LucideIcon 
            v-if="getVal(settings, 'iconName') && getVal(settings, 'iconPosition') === 'right'" 
            :name="getVal(settings, 'iconName')" 
            class="button-icon z-10 transition-transform duration-300 shrink-0"
            :style="iconStyles(settings, 'right')"
          />
        </a>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { getTypographyStyles, getVal, getResponsiveValue } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder', null)
const editableRef = ref(null)

const alignmentClass = (settings, device) => {
  const align = getVal(settings, 'alignment', device) || 'left'
  return {
    'flex justify-start': align === 'left',
    'flex justify-center': align === 'center',
    'flex justify-end': align === 'right'
  }
}

const sizeClass = (settings, device) => {
  const size = getVal(settings, 'size', device) || 'medium'
  return {
    'px-3 py-1.5 text-xs h-7': size === 'xs',
    'px-4 py-2 text-sm h-9': size === 'small',
    'px-6 py-3 text-base h-11': size === 'medium',
    'px-8 py-4 text-lg h-14': size === 'large',
    'px-10 py-5 text-xl h-16': size === 'xl'
  }
}

const variantClass = (settings) => {
  const variant = getVal(settings, 'variant') || 'primary'
  return {
    'bg-primary text-primary-foreground border-2 border-primary hover:bg-primary/90': variant === 'primary',
    'bg-secondary text-secondary-foreground border-2 border-secondary hover:bg-secondary/80': variant === 'secondary',
    'border-2 border-primary bg-transparent text-primary hover:bg-primary hover:text-primary-foreground': variant === 'outline',
    'bg-transparent text-primary hover:bg-primary/10 border-2 border-transparent': variant === 'ghost',
    'bg-transparent text-primary underline-offset-4 hover:underline border-none p-0 h-auto': variant === 'link'
  }
}

const hoverEffectClass = (settings) => {
  const effect = getVal(settings, 'hoverEffect') || 'none'
  return {
    'hover:-translate-y-1 hover:shadow-lg': effect === 'lift',
    'hover:scale-105': effect === 'scale',
    'hover:shadow-[0_0_20px_rgba(59,130,246,0.5)]': effect === 'glow'
  }
}

const radiusClass = (settings) => {
  const radius = getVal(settings, 'radius') || 'rounded-lg'
  return radius
}

const shadowClass = (settings) => {
  const shadow = getVal(settings, 'shadow')
  return shadow && shadow !== 'none' ? shadow : ''
}

const buttonStyles = (settings, device) => {
  const styles = {
    fontWeight: getVal(settings, 'fontWeight') || '600',
    textTransform: getVal(settings, 'textTransform') || 'none',
    width: getVal(settings, 'fullWidth', device) ? '100%' : 'auto'
  }

  if (getVal(settings, 'useCustomColors')) {
    styles.backgroundColor = getVal(settings, 'bgColor', device)
    styles.color = getVal(settings, 'textColor', device)
    styles.borderColor = getVal(settings, 'borderColor', device)
    styles.borderWidth = '2px'
    styles.borderStyle = 'solid'
  }

  // Merge typography if needed for more complex scenarios
  const typography = getTypographyStyles(settings, '', device)
  if (typography.fontFamily) styles.fontFamily = typography.fontFamily

  return styles
}

const iconStyles = (settings, pos) => {
  const size = getVal(settings, 'iconSize') || 16
  return {
    width: `${size}px`,
    height: `${size}px`
  }
}

const onButtonClick = (e) => {
  if (props.mode === 'edit') e.preventDefault()
}

const onTextBlur = (e, settings) => {
  if (props.mode !== 'edit' || !builder) return
  const newText = e.target.innerText
  if (newText !== getVal(settings, 'text')) {
    builder.updateModule(props.module.id, {
      settings: { ...settings, text: newText }
    })
  }
}
</script>

<style scoped>
.button-element {
  text-decoration: none;
  cursor: pointer;
}
@keyframes shine {
  100% {
    transform: translateX(100%);
  }
}
.group-hover\:animate-shine {
  animation: shine 0.7s forwards;
}
</style>
