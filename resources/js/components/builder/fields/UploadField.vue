<template>
  <div class="upload-field">
    <!-- Image/Video Preview -->
    <div v-if="!hidePreview && (value || placeholderValue) && isValidMedia" class="preview-container" :class="{ 'is-inherited': !value && placeholderValue }">
      <video v-if="isVideo" :src="value || placeholderValue" class="preview-image" :style="previewStyle" muted playsinline></video>
      <div v-else-if="isImage" class="preview-image" :style="{ backgroundImage: `url(${value || placeholderValue})`, ...previewStyle }"></div>
      <div v-else class="preview-file-icon">
        <File :size="32" />
      </div>
      
      <div v-if="!value && placeholderValue" class="inherited-badge">{{ $t('builder.fields.inherited') }}</div>
      <button v-if="value" class="remove-btn" @click="clearValue" :title="$t('builder.fields.actions.remove')">
        <X :size="14" />
      </button>
    </div>

    <!-- Input & Upload Button -->
    <div class="input-row flex gap-2">
      <BaseInput 
        :model-value="value" 
        @update:model-value="handleManualInput"
        :placeholder="placeholderValue || 'https://'"
        class="flex-1"
      />
      
      <MediaPicker @selected="handleSelection" :constraints="{ allowedExtensions, maxSize }">
        <template #trigger="{ open }">
          <IconButton 
            :icon="Upload" 
            @click="open" 
            :title="$t('builder.fields.actions.select')"
            variant="secondary"
          />
        </template>
      </MediaPicker>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { X, Upload, File, Video } from 'lucide-vue-next'
import MediaPicker from '../../MediaPicker.vue'
import { BaseInput, IconButton } from '../ui'
import { useToast } from '../../../composables/useToast'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: String,
    default: ''
  },
  placeholderValue: {
    type: String,
    default: ''
  },
  hidePreview: {
    type: Boolean,
    default: false
  },
  allowedExtensions: {
    type: Array,
    default: () => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
  },
  maxSize: {
    type: Number,
    default: null
  },
  previewStyle: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:value'])
const toast = useToast()

// Computed
const isImage = computed(() => {
  const val = props.value || props.placeholderValue
  if (!val) return false
  const parts = val.split('.')
  if (parts.length < 2) return val.startsWith('data:image')
  const ext = parts.pop().toLowerCase().split('?')[0]
  
  // Must be in image extensions list AND allowed for this field (if constraints exist)
  const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']
  const isImg = imageExts.includes(ext) || val.startsWith('data:image')
  
  if (props.allowedExtensions.length > 0) {
    return isImg && props.allowedExtensions.includes(ext)
  }
  return isImg
})

const isVideo = computed(() => {
  const val = props.value || props.placeholderValue
  if (!val) return false
  const parts = val.split('.')
  if (parts.length < 2) return val.startsWith('data:video')
  const ext = parts.pop().toLowerCase().split('?')[0]
  
  const videoExts = ['mp4', 'webm', 'ogv', 'mov', 'm4v']
  const isVid = videoExts.includes(ext) || val.startsWith('data:video')
  
  if (props.allowedExtensions.length > 0) {
    return isVid && props.allowedExtensions.includes(ext)
  }
  return isVid
})

const isValidMedia = computed(() => {
  const val = props.value || props.placeholderValue
  if (!val) return true // Empty is technically "valid" as in it doesn't show an error
  
  const parts = val.split('.')
  if (parts.length < 2) return true 
  
  const ext = parts.pop().toLowerCase().split('?')[0]
  if (props.allowedExtensions.length > 0) {
    return props.allowedExtensions.includes(ext)
  }
  return true
})

// Methods
const handleManualInput = (val) => {
  emit('update:value', val)
}

const handleSelection = (media) => {
  if (media && media.url) {
    const ext = media.extension || media.url.split('.').pop().toLowerCase().split('?')[0]
    if (props.allowedExtensions.length > 0 && !props.allowedExtensions.includes(ext)) {
      toast.error.validation(`File type .${ext} is not allowed for this field.`)
      return
    }
    emit('update:value', media.url)
  }
}

const clearValue = () => {
  emit('update:value', '')
}
</script>

<style scoped>
.upload-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.preview-container {
  position: relative;
  width: 100%;
  height: 120px;
  background: var(--builder-bg-tertiary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

div.preview-image {
  width: 100%;
  height: 100%;
}

.preview-container.is-inherited .preview-image {
  opacity: 0.4;
  filter: grayscale(0.5);
}

.inherited-badge {
  position: absolute;
  bottom: 8px;
  left: 8px;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  pointer-events: none;
}

.remove-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.remove-btn:hover {
  background: rgba(0, 0, 0, 0.8);
}

.preview-file-icon {
  color: var(--builder-text-muted);
}
</style>
