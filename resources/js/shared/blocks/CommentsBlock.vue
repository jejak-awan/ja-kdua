<template>
  <BaseBlock :module="module" :settings="settings" class="comments-block">
    <div class="container mx-auto">
      <h3 
        v-if="settings.title" 
        class="comments-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateText('title', $event)"
      >{{ settings.title }} <span v-if="commentsCount > 0">({{ commentsCount }})</span></h3>
      
      <!-- Comments List -->
      <div class="comments-list">
        <div v-for="comment in displayComments" :key="comment.id" class="comment-item">
          <div v-if="settings.showAvatar !== false" class="comment-avatar" :style="avatarStyles">
            <img v-if="comment.avatar" :src="comment.avatar" :alt="comment.author" />
            <User v-else class="avatar-icon" />
          </div>
          <div class="comment-content">
            <div class="comment-header">
              <span class="comment-author" :style="nameStyles">{{ comment.author }}</span>
              <span class="comment-date" :style="metaStyles">{{ comment.date }}</span>
            </div>
            <div class="comment-text" :style="textStyles" v-html="comment.text"></div>
            <button 
                v-if="settings.showReplyButton !== false" 
                class="comment-reply text-sm opacity-70 hover:opacity-100 transition-opacity"
                @click="handleReply(comment.id)"
            >Reply</button>
          </div>
        </div>
      </div>
      
      <!-- Comment Form -->
      <div v-if="settings.showForm !== false" class="comment-form mt-12 p-8 bg-gray-50 rounded-lg dark:bg-gray-800/50">
        <h4 class="mb-6 font-semibold" :style="titleStyles">{{ settings.formTitle || 'Leave a Comment' }}</h4>
        <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <input 
            type="text" 
            :placeholder="settings.namePlaceholder || 'Your Name'" 
            class="p-3 border rounded-md dark:bg-gray-900/50 dark:border-gray-700"
            :style="fieldStyles" 
          />
          <input 
            type="email" 
            :placeholder="settings.emailPlaceholder || 'Your Email'" 
            class="p-3 border rounded-md dark:bg-gray-900/50 dark:border-gray-700"
            :style="fieldStyles" 
          />
        </div>
        <textarea 
            :placeholder="settings.commentPlaceholder || 'Write a comment...'" 
            rows="4" 
            class="w-full p-3 border rounded-md dark:bg-gray-900/50 dark:border-gray-700 mb-6"
            :style="fieldStyles" 
        />
        <button 
            class="form-submit px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            :style="buttonStyles"
        >{{ settings.submitText || 'Post Comment' }}</button>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { User } from 'lucide-vue-next'
import { 
  getTypographyStyles,
  getResponsiveValue
} from '../utils/styleUtils'

const props = defineProps({
  module: { type: Object, required: true },
  mode: { type: String, default: 'view' }
})

const builder = inject('builder', null)
const settings = computed(() => props.module.settings || {})
const device = computed(() => builder?.device?.value || 'desktop')

// Dynamic data injection
const injectedComments = inject('postComments', [
  { id: 1, author: 'John Doe', authorUrl: '#', avatar: null, date: 'Jan 10, 2026', text: 'Great article! Thanks for sharing.' },
  { id: 2, author: 'Jane Smith', authorUrl: '#', avatar: null, date: 'Jan 9, 2026', text: 'Really helpful information. Keep up the good work!' },
  { id: 3, author: 'Bob Wilson', authorUrl: '#', avatar: null, date: 'Jan 8, 2026', text: 'I have a question about this topic...' }
])

const commentsCount = computed(() => injectedComments.length)
const displayComments = computed(() => injectedComments)

const updateText = (key, event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { [key]: event.target.innerText })
}

const handleReply = (commentId) => {
    if (props.mode === 'edit') return
    console.log('Reply to comment:', commentId)
}

const avatarStyles = computed(() => ({
    width: `${getResponsiveValue(settings.value, 'avatarSize', device.value) || 48}px`,
    height: `${getResponsiveValue(settings.value, 'avatarSize', device.value) || 48}px`
}))

const titleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const textStyles = computed(() => getTypographyStyles(settings.value, 'text_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const fieldStyles = computed(() => getTypographyStyles(settings.value, 'field_', device.value))
const buttonStyles = computed(() => getTypographyStyles(settings.value, 'button_', device.value))
</script>

<style scoped>
.comments-block { width: 100%; }
.comment-item { display: flex; gap: 16px; padding: 24px 0; border-bottom: 1px solid rgba(0,0,0,0.1); }
.comment-item:last-child { border-bottom: none; }
.comment-avatar { border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.avatar-icon { width: 50%; height: 50%; color: #999; }
.comment-content { flex: 1; }
.comment-header { display: flex; gap: 12px; align-items: center; margin-bottom: 8px; }
.comment-text { margin-bottom: 12px; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>
