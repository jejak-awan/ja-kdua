<template>
  <div class="page-settings-panel">
    <!-- Header/Title -->
    <div class="panel-header mb-4">
      <h3 class="text-lg font-semibold">{{ t('builder.panels.pageSettings.title') || 'Page Settings' }}</h3>
    </div>

    <!-- General Section -->
    <section class="settings-section">
      <h4 class="section-title">{{ t('builder.panels.pageSettings.sections.general') || 'General' }}</h4>
      
      <div class="settings-group">
        <label class="setting-label">{{ t('builder.panels.pageSettings.fields.title') }}</label>
        <input type="text" v-model="content.title" class="setting-input" :placeholder="t('builder.panels.pageSettings.placeholders.title')" />
      </div>

      <div class="settings-group">
        <label class="setting-label">{{ t('builder.panels.pageSettings.fields.slug') }}</label>
        <input type="text" v-model="content.slug" class="setting-input" :placeholder="t('builder.panels.pageSettings.placeholders.slug')" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="settings-group">
          <label class="setting-label">{{ t('builder.panels.pageSettings.fields.status') }}</label>
          <select v-model="content.status" class="setting-select">
            <option value="draft">{{ t('builder.panels.pageSettings.status.draft') }}</option>
            <option value="published">{{ t('builder.panels.pageSettings.status.published') }}</option>
            <option value="pending">{{ t('builder.panels.pageSettings.status.pending') || 'Pending' }}</option>
            <option value="archived">{{ t('builder.panels.pageSettings.status.archived') || 'Archived' }}</option>
          </select>
        </div>
        <div class="settings-group">
          <label class="setting-label">{{ t('builder.panels.pageSettings.fields.type') || 'Type' }}</label>
          <select v-model="content.type" class="setting-select">
            <option value="post">Post</option>
            <option value="page">Page</option>
            <option value="custom">Custom</option>
          </select>
        </div>
      </div>
    </section>

    <!-- Taxonomy Section -->
    <section class="settings-section">
      <h4 class="section-title">{{ t('features.content.form.category') || 'Categories' }}</h4>
      <div class="settings-group">
        <select v-model="content.category_id" class="setting-select">
          <option :value="null">None</option>
          <option v-for="cat in builder.categories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </select>
      </div>

      <h4 class="section-title mt-4">{{ t('features.content.form.tags') || 'Tags' }}</h4>
      <div class="settings-group">
        <div class="flex flex-wrap gap-1.5 mb-2">
          <div v-for="tag in content.tags" :key="tag.id || tag.name" class="tag-badge">
            {{ tag.name }}
            <button @click="removeTag(tag)" class="tag-remove">
              <X class="w-3 h-3" />
            </button>
          </div>
        </div>
        <div class="relative">
          <input 
            type="text" 
            v-model="tagQuery" 
            @input="handleTagSearch"
            @keydown.enter.prevent="addCustomTag"
            class="setting-input pl-8" 
            :placeholder="t('features.content.form.addTags') || 'Search or add tag...'" 
          />
          <Search class="absolute left-2.5 top-2.5 w-4 h-4 text-muted-foreground" />
          
          <!-- Tag Suggestions -->
          <div v-if="tagSuggestions.length > 0" class="tag-suggestions">
            <div 
              v-for="tag in tagSuggestions" 
              :key="tag.id"
              @click="addTag(tag)"
              class="suggestion-item"
            >
              {{ tag.name }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Media Section -->
    <section class="settings-section">
      <h4 class="section-title">{{ t('builder.panels.pageSettings.fields.featuredImage') }}</h4>
      <div class="image-preview" @click="openMediaLibrary">
        <template v-if="content.featured_image">
          <img :src="content.featured_image" class="w-full h-full object-cover rounded" />
          <div class="image-overlay">
            <Edit2 class="w-6 h-6 text-white" />
          </div>
        </template>
        <div v-else class="image-placeholder">
          <Image class="w-10 h-10" />
          <span>{{ t('builder.panels.pageSettings.placeholders.selectImage') }}</span>
        </div>
      </div>
      <button v-if="content.featured_image" @click="content.featured_image = null" class="mt-2 text-xs text-red-500 hover:text-red-600 transition-colors">
        {{ t('features.content.form.removeImage') || 'Remove Image' }}
      </button>
    </section>

    <!-- Excerpt Section -->
    <section class="settings-section">
      <h4 class="section-title">{{ t('features.content.form.excerpt') || 'Excerpt' }}</h4>
      <textarea 
        v-model="content.excerpt" 
        rows="3" 
        class="setting-input mt-1" 
        :placeholder="t('features.content.form.excerptPlaceholder') || 'Short summary...'"
      ></textarea>
    </section>

    <!-- SEO Section -->
    <section class="settings-section">
      <h4 class="section-title flex items-center justify-between">
        {{ t('features.content.form.seoSettings') || 'SEO Settings' }}
        <Globe class="w-3.5 h-3.5 opacity-50" />
      </h4>
      
      <div class="space-y-4 pt-2">
        <div class="settings-group">
          <label class="setting-label">{{ t('features.content.form.metaTitle') || 'Meta Title' }}</label>
          <input type="text" v-model="content.meta_title" class="setting-input" />
        </div>
        
        <div class="settings-group">
          <label class="setting-label">{{ t('features.content.form.metaDescription') || 'Meta Description' }}</label>
          <textarea v-model="content.meta_description" rows="2" class="setting-input"></textarea>
        </div>

        <div class="settings-group">
          <label class="setting-label">{{ t('features.content.form.metaKeywords') || 'Meta Keywords' }}</label>
          <input type="text" v-model="content.meta_keywords" class="setting-input" placeholder="keyword1, keyword2" />
        </div>
      </div>
    </section>

    <!-- Advanced Section -->
    <section class="settings-section border-b-0 pb-10">
      <h4 class="section-title">{{ t('builder.panels.pageSettings.sections.advanced') || 'Advanced' }}</h4>
      
      <div class="flex items-center justify-between py-2">
        <span class="text-sm">{{ t('features.content.form.allowComments') || 'Allow Comments' }}</span>
        <button 
          @click="content.comment_status = !content.comment_status"
          class="switch-toggle"
          :class="{ 'switch-toggle--active': content.comment_status }"
        >
          <div class="switch-thumb"></div>
        </button>
      </div>

      <div class="flex items-center justify-between py-2">
        <span class="text-sm">{{ t('features.content.form.featured') || 'Featured Post' }}</span>
        <button 
          @click="content.is_featured = !content.is_featured"
          class="switch-toggle"
          :class="{ 'switch-toggle--active': content.is_featured }"
        >
          <div class="switch-thumb"></div>
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, inject, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { 
  Image, 
  Search, 
  X, 
  Edit2, 
  Globe, 
  Layers,
  Settings2
} from 'lucide-vue-next'

const { t } = useI18n()
const builder = inject('builder')
const content = builder.content

// Tag search logic
const tagQuery = ref('')
const tagSuggestions = computed(() => {
  if (!tagQuery.value || tagQuery.value.length < 2) return []
  const query = tagQuery.value.toLowerCase()
  return builder.availableTags.filter(tag => 
    tag.name.toLowerCase().includes(query) && 
    !content.value.tags.find(t => t.id === tag.id)
  ).slice(0, 5)
})

const addTag = (tag) => {
  if (!content.value.tags.find(t => t.id === tag.id)) {
    content.value.tags.push(tag)
  }
  tagQuery.value = ''
}

const addCustomTag = () => {
  if (!tagQuery.value) return
  if (!content.value.tags.find(t => t.name.toLowerCase() === tagQuery.value.toLowerCase())) {
    content.value.tags.push({ name: tagQuery.value })
  }
  tagQuery.value = ''
}

const removeTag = (tag) => {
  content.value.tags = content.value.tags.filter(t => 
    tag.id ? t.id !== tag.id : t.name !== tag.name
  )
}

const openMediaLibrary = () => {
    // Media library integration placeholder
    // In a real app, this would emit an event to open the library
    // and handle the selection.
    window.dispatchEvent(new CustomEvent('builder:open-media', {
        detail: {
            callback: (url) => {
                content.value.featured_image = url
            }
        }
    }))
}
</script>

<style scoped>
.page-settings-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow-y: auto;
  padding: 16px;
  background: var(--builder-bg-secondary);
}

.settings-section {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--builder-border);
}

.section-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--builder-text-muted);
  margin-bottom: 12px;
}

.settings-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 12px;
}

.setting-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--builder-text-secondary);
}

.setting-input,
.setting-select {
  width: 100%;
  padding: 8px 12px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  color: var(--builder-text-primary);
  font-size: 13px;
  transition: all 0.2s;
}

.setting-input:focus,
.setting-select:focus {
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px var(--builder-accent-alpha-10);
  outline: none;
}

/* Tag Styles */
.tag-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 99px;
  font-size: 11px;
  color: var(--builder-text-primary);
}

.tag-remove {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.5;
  transition: opacity 0.2s;
}

.tag-remove:hover {
  opacity: 1;
  color: var(--red-500);
}

.tag-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 10;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  margin-top: 4px;
  box-shadow: var(--shadow-lg);
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-item {
  padding: 8px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: background 0.2s;
}

.suggestion-item:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-accent);
}

/* Media Styles */
.image-preview {
  position: relative;
  height: 160px;
  background: var(--builder-bg-primary);
  border: 2px dashed var(--builder-border);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.2s;
}

.image-preview:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
}

.image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: var(--builder-text-muted);
  font-size: 12px;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}

.image-preview:hover .image-overlay {
  opacity: 1;
}

/* Toggle Switch Styles */
.switch-toggle {
  width: 36px;
  height: 20px;
  background: var(--builder-border);
  border-radius: 99px;
  position: relative;
  transition: all 0.2s;
}

.switch-toggle--active {
  background: var(--builder-accent);
}

.switch-thumb {
  width: 16px;
  height: 16px;
  background: white;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.switch-toggle--active .switch-thumb {
  left: 18px;
}
</style>
