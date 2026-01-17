<template>
  <div class="dynamic-data-popover">
    <div class="popover-search">
      <Search :size="14" />
      <input 
        v-model="searchQuery" 
        type="text" 
        :placeholder="$t('builder.dynamic.search', 'Search dynamic data...')"
        autofocus
      >
    </div>

    <div class="popover-content custom-scrollbar">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <span>{{ $t('builder.dynamic.loading', 'Loading...') }}</span>
      </div>

      <!-- Content -->
      <template v-else>
        <div v-for="(group, groupName) in filteredGroups" :key="groupName" class="data-group">
          <h4 class="group-title">{{ group.label }}</h4>
          <div class="group-items">
            <button 
              v-for="item in group.items" 
              :key="item.id"
              class="data-item"
              @click="selectItem(item)"
            >
              <span class="item-label">{{ item.label }}</span>
              <span class="item-tag">{{ item.tag }}</span>
            </button>
          </div>
        </div>
        
        <div v-if="Object.keys(filteredGroups).length === 0" class="no-results">
          {{ $t('builder.dynamic.noResults', 'No dynamic data found') }}
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import { Search } from 'lucide-vue-next'
  import api from '@/services/api'

  const props = defineProps({
    source: {
      type: String,
      default: 'all' // all | page | loop | site | archive | user
    },
    contentId: {
      type: [Number, String],
      default: null
    }
  })

  const emit = defineEmits(['select'])
  const builder = inject('builder', null)

  const searchQuery = ref('')
  const dataGroups = ref({})
  const loading = ref(false)
  const error = ref(null)

  // Fetch dynamic sources from backend
  const fetchDynamicSources = async () => {
    loading.value = true
    error.value = null
    
    try {
      const params = {
        context: props.source,
        content_id: props.contentId || builder?.contentId
      }
      
      const response = await api.get('/admin/ja/builder/dynamic-sources', { params })
      dataGroups.value = response.data?.data || response.data || {}
    } catch (err) {
      console.error('Failed to fetch dynamic sources:', err)
      error.value = 'Failed to load dynamic data sources'
      // Fallback to default sources
      dataGroups.value = getDefaultSources()
    } finally {
      loading.value = false
    }
  }

  // Default sources as fallback
  const getDefaultSources = () => ({
    page: {
      label: 'Page / Post',
      items: [
        { id: 'post_title', label: 'Post Title', tag: '{{post_title}}' },
        { id: 'post_excerpt', label: 'Post Excerpt', tag: '{{post_excerpt}}' },
        { id: 'post_date', label: 'Post Date', tag: '{{post_date}}' },
        { id: 'post_author', label: 'Author Name', tag: '{{post_author}}' },
        { id: 'post_featured_image', label: 'Featured Image', tag: '{{post_featured_image}}' },
        { id: 'post_url', label: 'Post URL', tag: '{{post_url}}' }
      ]
    },
    site: {
      label: 'Site Settings',
      items: [
        { id: 'site_title', label: 'Site Title', tag: '{{site_title}}' },
        { id: 'site_tagline', label: 'Site Tagline', tag: '{{site_tagline}}' },
        { id: 'current_date', label: 'Current Date', tag: '{{current_date}}' }
      ]
    }
  })

  const filteredGroups = computed(() => {
    const query = searchQuery.value.toLowerCase()
    const result = {}

    Object.entries(dataGroups.value).forEach(([key, group]) => {
      const items = group.items.filter(item => 
        item.label.toLowerCase().includes(query) || 
        item.tag.toLowerCase().includes(query)
      )

      if (items.length > 0) {
        result[key] = {
          ...group,
          items
        }
      }
    })

    return result
  })

  const selectItem = (item) => {
    emit('select', item)
  }

  onMounted(() => {
    fetchDynamicSources()
  })
</script>

<style scoped>
.dynamic-data-popover {
  width: 280px;
  max-height: 400px;
  display: flex;
  flex-direction: column;
  background: var(--builder-bg-primary);
  color: var(--builder-text-primary);
}

.popover-search {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-md);
  border-bottom: 1px solid var(--builder-border);
  color: var(--builder-text-muted);
}

.popover-search input {
  flex: 1;
  background: transparent;
  border: none;
  font-size: var(--font-size-sm);
  color: var(--builder-text-primary);
  outline: none;
}

.popover-content {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-xs) 0;
}

.data-group {
  padding: var(--spacing-xs) 0;
}

.group-title {
  padding: var(--spacing-xs) var(--spacing-md);
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--builder-text-muted);
  letter-spacing: 0.5px;
}

.group-items {
  display: flex;
  flex-direction: column;
}

.data-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm) var(--spacing-md);
  background: transparent;
  border: none;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s;
}

.data-item:hover {
  background: var(--builder-bg-secondary);
}

.item-label {
  font-size: var(--font-size-sm);
  font-weight: 500;
}

.item-tag {
  font-size: 10px;
  color: var(--builder-text-muted);
  background: var(--builder-bg-tertiary);
  padding: 2px 6px;
  border-radius: 4px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-xl);
  gap: var(--spacing-sm);
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}

.loading-spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--builder-border);
  border-top-color: var(--builder-accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-results {
  padding: var(--spacing-lg);
  text-align: center;
  color: var(--builder-text-muted);
  font-size: var(--font-size-sm);
}

/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--builder-border);
  border-radius: 10px;
}
</style>
