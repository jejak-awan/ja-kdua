<template>
  <div class="comments-block" :style="wrapperStyles">
    <h3 
      v-if="settings.title" 
      class="comments-title" 
      :style="titleStyles"
      contenteditable="true"
      @blur="e => builder.updateModuleSetting(module.id, 'title', e.target.innerText)"
    >{{ settings.title }} (3)</h3>
    
    <!-- Comments List -->
    <div class="comments-list">
      <div v-for="comment in mockComments" :key="comment.id" class="comment-item">
        <div v-if="settings.showAvatar !== false" class="comment-avatar"><User class="avatar-icon" /></div>
        <div class="comment-content">
          <div class="comment-header">
            <span class="comment-author" :style="titleStyles">{{ comment.author }}</span>
            <span class="comment-date" :style="metaStyles">{{ comment.date }}</span>
          </div>
          <p class="comment-text" :style="textStyles">{{ comment.text }}</p>
          <button v-if="settings.showReplyButton !== false" class="comment-reply">Reply</button>
        </div>
      </div>
    </div>
    
    <!-- Comment Form -->
    <div v-if="settings.showForm !== false" class="comment-form">
      <h4 :style="titleStyles">Leave a Comment</h4>
      <div class="form-row">
        <input type="text" :placeholder="settings.namePlaceholder || 'Your Name'" :style="fieldStyles" />
        <input type="email" :placeholder="settings.emailPlaceholder || 'Your Email'" :style="fieldStyles" />
      </div>
      <textarea :placeholder="settings.commentPlaceholder || 'Write a comment...'" rows="4" :style="fieldStyles" />
      <button class="form-submit" :style="buttonStyles">{{ settings.submitText || 'Post Comment' }}</button>
    </div>
  </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import { User } from 'lucide-vue-next'
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

const props = defineProps({ module: { type: Object, required: true } })
const settings = computed(() => props.module.settings || {})

const builder = inject('builder')
const device = computed(() => builder?.device?.value || 'desktop')

const mockComments = [
  { id: 1, author: 'John Doe', date: 'Jan 10, 2026', text: 'Great article! Thanks for sharing.' },
  { id: 2, author: 'Jane Smith', date: 'Jan 9, 2026', text: 'Really helpful information. Keep up the good work!' },
  { id: 3, author: 'Bob Wilson', date: 'Jan 8, 2026', text: 'I have a question about this topic...' }
]

const wrapperStyles = computed(() => {
  const styles = { width: '100%' }
  
  Object.assign(styles, getBackgroundStyles(settings.value))
  Object.assign(styles, getSpacingStyles(settings.value, 'padding', device.value, 'padding'))
  Object.assign(styles, getSpacingStyles(settings.value, 'margin', device.value, 'margin'))
  Object.assign(styles, getBorderStyles(settings.value, 'border', device.value))
  Object.assign(styles, getBoxShadowStyles(settings.value, 'boxShadow', device.value))
  Object.assign(styles, getSizingStyles(settings.value, device.value))
  Object.assign(styles, getFilterStyles(settings.value, device.value))
  Object.assign(styles, getTransformStyles(settings.value, device.value))
  
  return styles
})

const titleStyles = computed(() => {
  return getTypographyStyles(settings.value, 'title_', device.value)
})

const textStyles = computed(() => {
  return getTypographyStyles(settings.value, 'text_', device.value)
})

const metaStyles = computed(() => {
  return getTypographyStyles(settings.value, 'meta_', device.value)
})

const fieldStyles = computed(() => {
  return getTypographyStyles(settings.value, 'field_', device.value)
})

const buttonStyles = computed(() => {
  return getTypographyStyles(settings.value, 'button_', device.value)
})
</script>

<style scoped>
.comments-block { width: 100%; }
.comments-title { margin: 0 0 24px; }
.comments-list { margin-bottom: 32px; }
.comment-item { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid #e0e0e0; }
.comment-item:last-child { border-bottom: none; }
.comment-avatar { width: v-bind('settings.avatarSize ? settings.avatarSize + "px" : "48px"'); height: v-bind('settings.avatarSize ? settings.avatarSize + "px" : "48px"'); border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.avatar-icon { width: 24px; height: 24px; color: #999; }
.comment-content { flex: 1; }
.comment-header { display: flex; gap: 12px; align-items: center; margin-bottom: 8px; }
.comment-text { margin: 0 0 8px; }
.comment-reply { background: none; border: none; font-size: 13px; cursor: pointer; padding: 0; }
.comment-form h4 { margin: 0 0 16px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.comment-form input, .comment-form textarea { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; box-sizing: border-box; }
.form-submit { padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; margin-top: 16px; }
</style>
