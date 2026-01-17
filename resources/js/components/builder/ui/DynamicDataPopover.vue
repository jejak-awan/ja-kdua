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
    </div>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue'
  import { Search } from 'lucide-vue-next'

  const props = defineProps({
    source: {
      type: String,
      default: 'all' // all | page | loop | user
    }
  })

  const emit = defineEmits(['select'])

  const searchQuery = ref('')

  // Mock data sources - In real app, these would come from the backend or builder state
  const dataGroups = computed(() => {
    const groups = {
      page: {
        label: 'Page / Post',
        items: [
          { id: 'post_title', label: 'Post Title', tag: 'post_title' },
          { id: 'post_excerpt', label: 'Post Excerpt', tag: 'post_excerpt' },
          { id: 'post_date', label: 'Post Date', tag: 'post_date' },
          { id: 'post_author', label: 'Author Name', tag: 'post_author' },
          { id: 'post_featured_image', label: 'Featured Image', tag: 'post_featured_image' },
          { id: 'post_url', label: 'Post URL', tag: 'post_url' }
        ]
      }
    }

    // Add Loop Context if applicable
    if (props.source === 'loop') {
      groups.loop = {
        label: 'Current Loop Item',
        items: [
          { id: 'loop_title', label: 'Item Title', tag: 'loop_title' },
          { id: 'loop_excerpt', label: 'Item Excerpt', tag: 'loop_excerpt' },
          { id: 'loop_date', label: 'Item Date', tag: 'loop_date' },
          { id: 'loop_author', label: 'Item Author', tag: 'loop_author' },
          { id: 'loop_thumbnail', label: 'Item Featured Image', tag: 'loop_thumbnail' },
          { id: 'loop_url', label: 'Item Link', tag: 'loop_url' }
        ]
      }
    }

    groups.site = {
      label: 'Site',
      items: [
        { id: 'site_title', label: 'Site Title', tag: 'site_title' },
        { id: 'site_tagline', label: 'Site Tagline', tag: 'site_tagline' },
        { id: 'current_date', label: 'Current Date', tag: 'current_date' }
      ]
    }

    groups.archive = {
      label: 'Archive',
      items: [
        { id: 'archive_title', label: 'Archive Title', tag: 'archive_title' },
        { id: 'archive_description', label: 'Archive Description', tag: 'archive_description' }
      ]
    }

    return groups
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
