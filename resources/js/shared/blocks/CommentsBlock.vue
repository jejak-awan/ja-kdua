<template>
  <BaseBlock 
    :module="module" 
    :mode="mode"
    :settings="settings"
    class="comments-block transition-all duration-300"
    :id="settings.html_id"
    :aria-label="settings.aria_label || 'Comments Section'"
  >
    <div class="comments-container w-full" :style="containerStyles">
      <h3 
        v-if="settings.title || mode === 'edit'" 
        class="comments-main-title mb-16 text-3xl font-black tracking-tighter outline-none" 
        :style="mainTitleStyles"
        :contenteditable="mode === 'edit'"
        @blur="(e: any) => updateField('title', (e.target as HTMLElement).innerText)"
      >
        {{ settings.title || (mode === 'edit' ? 'Conversation' : '') }}
        <span v-if="commentsCount > 0" class="ml-4 opacity-30 text-2xl font-black">/ {{ commentsCount }}</span>
      </h3>
      
      <!-- Comments List -->
      <div class="comments-list space-y-12 mb-20">
        <article v-for="comment in displayComments" :key="comment.id" class="comment-item group flex gap-8 p-10 bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl border border-slate-50 dark:border-slate-800 transition-all duration-500 hover:-translate-y-2">
          <div v-if="settings.showAvatar !== false" class="comment-avatar-wrapper relative flex-shrink-0" :style="avatarStyles">
            <div class="absolute inset-0 bg-primary/10 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity" />
            <div class="relative w-full h-full rounded-2xl bg-slate-100 dark:bg-slate-950 flex items-center justify-center overflow-hidden border-2 border-slate-50 dark:border-slate-800 group-hover:border-primary transition-colors">
                <img v-if="comment.avatar" :src="comment.avatar" :alt="comment.author" class="w-full h-full object-cover" />
                <LucideIcon v-else name="User" class="w-1/2 h-1/2 text-slate-400 group-hover:text-primary transition-colors" />
            </div>
          </div>
          <div class="comment-content flex-1 max-w-3xl">
            <header class="comment-header flex items-center justify-between mb-4">
              <div class="flex flex-col">
                  <span class="comment-author font-black tracking-tight text-slate-900 dark:text-white" :style="nameStyles">{{ comment.author }}</span>
                  <span class="comment-date text-[10px] font-black uppercase tracking-[0.2em] text-primary/60" :style="metaStyles">{{ comment.date }}</span>
              </div>
              <button 
                  v-if="settings.showReplyButton !== false" 
                  class="comment-reply h-10 px-6 rounded-2xl bg-slate-50 dark:bg-slate-950 text-[10px] font-black uppercase tracking-widest transition-all duration-300 hover:bg-primary hover:text-white group/reply"
                  @click="handleReply(comment.id)"
              >
                  Reply
                  <LucideIcon name="CornerUpLeft" class="inline-block ml-2 w-3 h-3 group-hover/reply:-translate-x-1 transition-transform" />
              </button>
            </header>
            <div class="comment-text text-slate-600 dark:text-slate-400 leading-relaxed font-medium" :style="textStyles" v-html="comment.text"></div>
          </div>
        </article>
      </div>
      
      <!-- Comment Form -->
      <div v-if="settings.showForm !== false" class="comment-form-section p-12 bg-white dark:bg-slate-900/50 rounded-[3rem] border border-dashed border-slate-200 dark:border-slate-800 overflow-hidden relative">
        <div class="absolute top-0 right-0 p-8 opacity-5">
            <LucideIcon name="MessageSquare" class="w-32 h-32" />
        </div>
        <h4 class="text-2xl font-black tracking-tighter mb-10 text-slate-900 dark:text-white" :style="mainTitleStyles">
            {{ settings.formTitle || 'Add to the conversation' }}
        </h4>
        <form @submit.prevent class="relative z-10">
            <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div class="field-wrapper">
                  <Input 
                    type="text" 
                    :placeholder="settings.namePlaceholder || 'John Doe'" 
                    class="h-14 px-8 bg-slate-50 dark:bg-slate-950 border-none rounded-[1.5rem] font-medium focus-visible:ring-primary/20"
                    :style="fieldStyles" 
                  />
              </div>
              <div class="field-wrapper">
                  <Input 
                    type="email" 
                    :placeholder="settings.emailPlaceholder || 'hello@modern.agency'" 
                    class="h-14 px-8 bg-slate-50 dark:bg-slate-950 border-none rounded-[1.5rem] font-medium focus-visible:ring-primary/20"
                    :style="fieldStyles" 
                  />
              </div>
            </div>
            <textarea 
                :placeholder="settings.commentPlaceholder || 'Share your insights...'" 
                rows="5" 
                class="w-full p-8 bg-slate-50 dark:bg-slate-950 border-none rounded-[2.5rem] font-medium focus:ring-4 focus:ring-primary/10 outline-none transition-all mb-8 text-slate-900 dark:text-white"
                :style="fieldStyles" 
            />
            <Button 
                class="form-submit h-14 px-12 bg-primary text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs shadow-2xl hover:scale-105 active:scale-95 transition-all duration-500"
                :style="buttonStyles"
            >
                {{ settings.submitText || 'Publish Comment' }}
                <LucideIcon name="Send" class="ml-3 w-4 h-4" />
            </Button>
        </form>
      </div>
    </div>
  </BaseBlock>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Input, Button } from '../ui'
import LucideIcon from '../../components/ui/LucideIcon.vue'
import { 
  getVal,
  getLayoutStyles,
  getTypographyStyles
} from '../utils/styleUtils'
import type { BlockInstance } from '@/types/builder'

const props = defineProps<{
  module: BlockInstance
  mode: 'view' | 'edit'
}>()

const builder = inject<any>('builder', null)
const device = computed(() => builder?.device?.value || 'desktop')
const settings = computed(() => (props.module.settings || {}) as Record<string, any>)

// Mock dynamic data
const injectedComments = inject<any[]>('postComments', [
  { id: 1, author: 'Alex Rivera', avatar: 'https://i.pravatar.cc/150?u=1', date: 'JAN 25, 2026', text: 'The attention to detail in these dynamic modules is spectacular. The accessibility integrations are exactly what modern agencies need.' },
  { id: 2, author: 'Elena Vance', avatar: 'https://i.pravatar.cc/150?u=2', date: 'JAN 22, 2026', text: 'I love how the layout controls are baked right into the common settings. This makes maintaining brand consistency so much easier across large projects.' },
  { id: 3, author: 'Marcus Thorne', avatar: 'https://i.pravatar.cc/150?u=3', date: 'JAN 20, 2026', text: 'Are there plans to support custom Gravatar integration for the comment section? That would be a great addition.' }
])

const commentsCount = computed(() => injectedComments.length)
const displayComments = computed(() => injectedComments)

const updateField = (key: string, value: string) => {
    if (props.mode !== 'edit' || !builder) return
    builder.updateModuleSettings(props.module.id, { [key]: value })
}

const handleReply = (commentId: number) => {
    if (props.mode === 'edit') return
    console.log('Reply to comment:', commentId)
}

const containerStyles = computed(() => getLayoutStyles(settings.value, device.value))

const avatarStyles = computed(() => {
    const s = getVal(settings.value, 'avatarSize', device.value) || 64
    return {
        width: `${s}px`,
        height: `${s}px`
    }
})

const mainTitleStyles = computed(() => getTypographyStyles(settings.value, 'title_', device.value))
const nameStyles = computed(() => getTypographyStyles(settings.value, 'name_', device.value))
const textStyles = computed(() => getTypographyStyles(settings.value, 'text_', device.value))
const metaStyles = computed(() => getTypographyStyles(settings.value, 'meta_', device.value))
const fieldStyles = computed(() => getTypographyStyles(settings.value, 'field_', device.value))
const buttonStyles = computed(() => getTypographyStyles(settings.value, 'button_', device.value))
</script>

<style scoped>
.comments-block { width: 100%; }
.comments-main-title { outline: none; }
</style>
