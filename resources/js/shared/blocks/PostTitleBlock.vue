<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :device="device"
    class="post-title-block transition-[width] duration-500"
    :id="(settings.html_id as string)"
    :aria-label="(settings.aria_label as string) || 'Post Title'"
    :style="(cardStyles as any)"
  >
    <div class="w-full" :style="(containerStyles as any)">
      <component 
        :is="tag" 
        class="post-title transition-[width] duration-500 font-serif leading-tight outline-none" 
        :class="alignmentClass"
        :style="(titleStyles as any)"
        :contenteditable="mode === 'edit'"
        @blur="updateTitle"
      >
{{ displayTitle }}
</component>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance, BuilderInstance, ModuleSettings } from '@/types/builder'

const props = withDefaults(defineProps<{
  module: BlockInstance;
  mode?: 'view' | 'edit';
  device?: 'desktop' | 'tablet' | 'mobile';
}>(), {
  mode: 'view',
  device: 'desktop'
})

const builder = inject<BuilderInstance | null>('builder', null)
const settings = computed(() => (props.module.settings || {}) as ModuleSettings)

// Dynamic data injection
const postTitle = inject<string>('postTitle', 'Dynamic Post Title')

const displayTitle = computed(() => {
    if (props.mode === 'edit') return (settings.value.title as string) || postTitle
    return postTitle
})

const tag = computed(() => getVal<string>(settings.value, 'tag', props.device) || 'h1')

const cardStyles = computed(() => {
    const styles: Record<string, string | number> = {}
    const hoverScale = getVal<number>(settings.value, 'hover_scale', props.device) || 1
    const hoverBrightness = getVal<number>(settings.value, 'hover_brightness', props.device) || 100
    
    styles['--hover-scale'] = hoverScale
    styles['--hover-brightness'] = `${hoverBrightness}%`
    
    return styles
})

const containerStyles = computed(() => {
    return getLayoutStyles(settings.value, props.device)
})

const alignmentClass = computed(() => {
    const align = getVal<string>(settings.value, 'alignment', props.device) || 'left'
    return `text-${align}`
})

const updateTitle = (event: FocusEvent) => {
    if (props.mode !== 'edit' || !builder) return
    const text = (event.target as HTMLElement).innerText
    builder.updateModuleSettings(props.module.id, { title: text })
}

const titleStyles = computed(() => {
  return getTypographyStyles(settings.value, '', props.device)
})
</script>

<style scoped>
.post-title-block {
    width: 100%;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), filter 0.4s ease;
}
.post-title-block:hover {
    transform: scale(var(--hover-scale, 1));
    filter: brightness(var(--hover-brightness, 100%));
}
.post-title { margin: 0; word-wrap: break-word; }
</style>
