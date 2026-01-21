<template>
  <BaseBlock :module="module" :settings="settings" tag="figure" class="featured-image-block">
    <div class="image-container" :style="containerStyles">
      <img v-if="postFeaturedImage" :src="postFeaturedImage" :alt="settings.caption || 'Featured Image'" class="featured-image" />
      <ImageIcon v-else class="placeholder-icon" />
    </div>
    <figcaption 
      v-if="settings.showCaption !== false && (settings.caption || mode === 'edit')" 
      class="image-caption" 
      :style="captionStyles"
      :contenteditable="mode === 'edit'"
      @blur="updateCaption"
    >
      {{ settings.caption || (mode === 'edit' ? 'Featured image caption' : '') }}
    </figcaption>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Image as ImageIcon } from 'lucide-vue-next'
import { 
  getTypographyStyles
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Dynamic data injection
const postFeaturedImage = inject('postFeaturedImage', null)

const aspectRatios = { '16:9': '56.25%', '4:3': '75%', '3:2': '66.67%', '1:1': '100%', 'original': 'auto' }

const containerStyles = computed(() => {
  const styles = { 
    position: 'relative', 
    paddingTop: aspectRatios[settings.value.aspectRatio] || '56.25%',
    backgroundColor: '#f0f0f0', 
    display: 'flex', 
    alignItems: 'center', 
    justifyContent: 'center', 
    overflow: 'hidden' 
  }
  return styles
})

const captionStyles = computed(() => getTypographyStyles(settings.value, 'caption_', device.value))

const updateCaption = (event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { caption: event.target.innerText })
}
</script>

<style scoped>
.featured-image-block { width: 100%; margin: 0; }
.image-container { width: 100%; }
.featured-image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.placeholder-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 48px; height: 48px; color: #ccc; }
.image-caption { margin-top: 8px; outline: none; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>
