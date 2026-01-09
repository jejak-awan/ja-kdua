<template>
  <Popover v-model:open="open">
    <PopoverTrigger asChild>
      <slot name="trigger">
        <Button
          variant="outline"
          role="combobox"
          :aria-expanded="open"
          class="w-full justify-between h-9"
        >
          <div class="flex items-center gap-2">
            <component
              v-if="selectedIcon"
              :is="getIconComponent(selectedIcon)"
              class="w-4 h-4"
            />
            <span v-else class="text-muted-foreground">{{ placeholder }}</span>
            <span v-if="selectedIcon" class="text-sm">{{ selectedIcon }}</span>
          </div>
          <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
        </Button>
      </slot>
    </PopoverTrigger>
    <PopoverContent class="w-[340px] p-0" align="start">
      <div class="p-2 border-b">
        <Input
          v-model="searchQuery"
          placeholder="Search icons..."
          class="h-8 mb-2"
        />
        
        <Tabs v-model="activeTab" class="w-full">
            <TabsList class="grid grid-cols-4 h-auto p-1 gap-1">
                <TabsTrigger value="all" class="text-[10px] h-6 px-1">All</TabsTrigger>
                <TabsTrigger 
                    v-for="(icons, category) in displayCategories" 
                    :key="category" 
                    :value="category"
                    class="text-[10px] h-6 px-1 truncate"
                    :title="category"
                >
                    {{ category }}
                </TabsTrigger>
            </TabsList>
        </Tabs>
      </div>

      <div class="max-h-[300px] overflow-y-auto p-2 scrollbar-thin">
        <div v-if="filteredIcons.length === 0" class="text-center py-6 text-sm text-muted-foreground">
          No icons found
        </div>
        <div class="grid grid-cols-6 gap-1">
          <button
            v-for="icon in filteredIcons"
            :key="icon"
            type="button"
            @click="selectIcon(icon)"
            class="p-2 rounded-md hover:bg-muted transition-colors flex items-center justify-center relative group"
            :class="{ 'bg-primary/10 ring-1 ring-primary': selectedIcon === icon }"
            :title="icon"
          >
            <component :is="getIconComponent(icon)" class="w-5 h-5" />
          </button>
        </div>
      </div>
      <div v-if="modelValue" class="p-2 border-t">
        <Button
          variant="ghost"
          size="sm"
          class="w-full text-destructive hover:text-destructive"
          @click="clearIcon"
        >
          <X class="w-4 h-4 mr-2" />
          Clear Icon
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup>
import { ref, computed, shallowRef, onMounted } from 'vue';
import Button from './button.vue';
import Input from './input.vue';
import Popover from './popover.vue';
import PopoverTrigger from './popover-trigger.vue';
import PopoverContent from './popover-content.vue';
import Tabs from './tabs.vue';
import TabsList from './tabs-list.vue';
import TabsTrigger from './tabs-trigger.vue';
import { ChevronsUpDown, X } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';
import { iconCategories } from '../../config/icon-categories';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Select icon...'
  }
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const searchQuery = ref('');
const activeTab = ref('General');

const selectedIcon = computed(() => props.modelValue);

// Get ALL icon names from Lucide, excluding createLucideIcon internal
const allIconNames = Object.keys(LucideIcons).filter(key => {
    // Basic exclusions
    if (key === 'default' || key === 'createLucideIcon' || key === 'Icon' || !/^[A-Z]/.test(key)) {
        return false;
    }
    
    // Check if it looks like a component (defensive)
    const exp = LucideIcons[key];
    if (typeof exp !== 'object' && typeof exp !== 'function') return false;
    
    return true;
});

// Map categories for display (limiting to first 3 + All in tabs usually, but Grid layout handles more)
// We will show top 7 categories + All
const displayCategories = computed(() => {
    // Only return categories that actually have icons
    return iconCategories; 
});

const filteredIcons = computed(() => {
  let icons = []; // Default to all

  if (activeTab.value === 'all') {
      icons = allIconNames;
  } else {
      // Get icons for specific category
      icons = iconCategories[activeTab.value] || [];
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    // If searching, we might want to search ALL icons even if in a category?
    // User preference usually. Let's strict filter the CURRENT view first.
    // Actually, widespread search is better UX.
    // If strict search in category returns 0, maybe search all?
    // Let's stick to simple: Filter the CURRENT active list.
    // EXCEPT if 'all' is active.
    
    // Improvement: If search is active, maybe auto-switch to "all"?
    // For now: strict filter.
    return icons.filter(icon => icon.toLowerCase().includes(query));
  }

  return icons;
});

const getIconComponent = (iconName) => {
  return LucideIcons[iconName] || LucideIcons.Circle;
};

const selectIcon = (icon) => {
  emit('update:modelValue', icon);
  open.value = false;
  searchQuery.value = '';
};

const clearIcon = () => {
  emit('update:modelValue', '');
  open.value = false;
};
</script>
