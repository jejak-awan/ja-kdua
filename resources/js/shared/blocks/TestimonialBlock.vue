<template>
  <BaseBlock :module="module" :mode="mode" :device="device">
    <template #default="{ styles: wrapperBaseStyles, settings }">
      <div class="testimonial-block" :style="testimonialBlockStyles">
        <!-- Quote Icon Overlay -->
        <QuoteIcon 
          v-if="showQuoteIcon" 
          class="quote-icon-bg"
          :style="{ color: quoteIconStyles.color }"
        />
        
        <!-- Content Wrapper -->
        <div class="testimonial-inner">
          <!-- Quote Icon Small -->
          <QuoteIcon 
            v-if="showQuoteIcon" 
            class="quote-icon"
            :style="quoteIconStyles"
          />
          
          <!-- Content -->
          <div 
            class="testimonial-content" 
            :style="contentStyles"
            :contenteditable="mode === 'edit'"
            @blur="e => updateResponsiveField('content', e.target.innerText)"
          >
            {{ contentValue }}
          </div>
          
          <!-- Author -->
          <div class="testimonial-author">
            <div class="author-image-wrapper">
              <img 
                v-if="settings.authorImage"
                :src="settings.authorImage"
                :alt="authorNameValue"
                class="author-image"
              />
              <div v-else class="author-image-placeholder">
                  <UserIcon :size="32" />
              </div>
            </div>
            <div class="author-info">
              <div 
                class="author-name" 
                :style="authorNameStyles"
                :contenteditable="mode === 'edit'"
                @blur="e => updateResponsiveField('authorName', e.target.innerText)"
              >
                {{ authorNameValue || 'Author Name' }}
              </div>
              <div 
                v-if="authorTitleValue || mode === 'edit'" 
                class="author-title" 
                :style="authorTitleStyles"
                :contenteditable="mode === 'edit'"
                @blur="e => updateResponsiveField('authorTitle', e.target.innerText)"
              >
                {{ authorTitleValue }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import { Quote as QuoteIcon, User as UserIcon } from 'lucide-vue-next'
import BaseBlock from '../components/BaseBlock.vue'
import { getVal, getTypographyStyles } from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' },
  device: { type: String, default: 'desktop' }
})

const builder = inject('builder')
const settings = computed(() => props.module?.settings || {})

const contentValue = computed(() => getVal(settings.value, 'content', props.device))
const authorNameValue = computed(() => getVal(settings.value, 'authorName', props.device))
const authorTitleValue = computed(() => getVal(settings.value, 'authorTitle', props.device))
const showQuoteIcon = computed(() => getVal(settings.value, 'showQuoteIcon', props.device) !== false)

const testimonialBlockStyles = computed(() => {
  return { 
    textAlign: getVal(settings.value, 'alignment', props.device) || 'center',
    width: '100%',
    position: 'relative'
  }
})

const quoteIconStyles = computed(() => {
  const size = getVal(settings.value, 'quoteIconSize', props.device) || 48
  const color = getVal(settings.value, 'quoteIconColor', props.device) || '#e0e0e0'
  return {
    width: `${size}px`,
    height: `${size}px`,
    color: color
  }
})

const contentStyles = computed(() => getTypographyStyles(settings.value, 'content_', props.device))
const authorNameStyles = computed(() => getTypographyStyles(settings.value, 'author_name_', props.device))
const authorTitleStyles = computed(() => getTypographyStyles(settings.value, 'author_title_', props.device))

const updateResponsiveField = (fieldName, value) => {
  if (props.mode !== 'edit') return
  const current = settings.value[fieldName]
  let newValue
  if (typeof current === 'object' && current !== null && !Array.isArray(current)) {
    newValue = { ...current, [props.device]: value }
  } else {
    newValue = { [props.device]: value }
  }
  builder?.updateModuleSettings(props.module.id, { [fieldName]: newValue })
}
</script>

<style scoped>
.testimonial-block { 
  width: 100%; 
  position: relative; 
  background: white; 
  border-radius: 24px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.testimonial-inner {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.quote-icon-bg {
  position: absolute;
  top: -20px;
  right: -20px;
  width: 120px;
  height: 120px;
  opacity: 0.05;
  transform: rotate(180deg);
  z-index: 1;
}

.quote-icon { 
  margin-bottom: 24px; 
  transform: rotate(180deg); 
  opacity: 0.8;
}

.testimonial-content { 
  margin-bottom: 32px; 
  max-width: 800px; 
  line-height: 1.8;
  font-size: 18px;
  font-style: italic;
  font-weight: 500;
  color: #1e293b;
}

.testimonial-author { 
  display: flex; 
  align-items: center; 
  gap: 16px; 
  margin-top: auto;
}

.author-image-wrapper {
  position: relative;
  width: 64px;
  height: 64px;
  flex-shrink: 0;
}

.author-image { 
  width: 100%; 
  height: 100%; 
  border-radius: 50%; 
  object-fit: cover; 
}

.author-image-placeholder {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.author-name {
  font-weight: 700;
  font-size: 16px;
  color: #0f172a;
}

.author-title { 
  margin-top: 2px; 
  font-size: 13px;
  font-weight: 500;
  opacity: 0.6;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
</style>
