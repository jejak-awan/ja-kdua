<template>
  <BaseBlock :module="module" :settings="settings" class="post-title-block">
    <div class="container mx-auto">
      <!-- Meta (Optional integration) -->
      <div v-if="hasMeta" class="post-meta-mini mb-6 flex flex-wrap gap-4 text-sm opacity-70">
          <span v-if="settings.show_category" class="flex items-center gap-1">
              <Tag class="w-4 h-4" />
              {{ category }}
          </span>
          <span v-if="settings.show_date" class="flex items-center gap-1">
              <Calendar class="w-4 h-4" />
              {{ date }}
          </span>
          <span v-if="settings.show_author" class="flex items-center gap-1">
              <User class="w-4 h-4" />
              {{ author }}
          </span>
      </div>

      <component 
        :is="settings.tag || 'h1'" 
        class="post-title" 
        :style="titleStyles"
        :contenteditable="mode === 'edit'"
        @blur="updateTitle"
      >
        {{ displayTitle }}
      </component>
    </div>
  </BaseBlock>
</template>

<script setup>
import { computed, inject } from 'vue'
import BaseBlock from '../components/BaseBlock.vue'
import { Calendar, User, Tag } from 'lucide-vue-next'
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
const postTitle = inject('postTitle', 'Sample Post Title')
const postDate = inject('postDate', 'January 10, 2026')
const postAuthor = inject('postAuthor', 'Author Name')
const postCategory = inject('postCategory', 'Category')

const displayTitle = computed(() => {
    if (props.mode === 'edit') return settings.value.title || postTitle
    return postTitle
})

const date = computed(() => postDate)
const author = computed(() => postAuthor)
const category = computed(() => postCategory)

const hasMeta = computed(() => settings.value.show_date || settings.value.show_author || settings.value.show_category)

const updateTitle = (event) => {
    if (props.mode !== 'edit') return
    builder?.updateModuleSettings(props.module.id, { title: event.target.innerText })
}

const titleStyles = computed(() => {
  return getTypographyStyles(settings.value, '', device.value)
})
</script>

<style scoped>
.post-title-block { width: 100%; }
.post-title { margin: 0; outline: none; }
[contenteditable]:focus {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}
</style>
