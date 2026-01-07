<template>
  <Popover v-model:open="open">
    <PopoverTrigger asChild>
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
    </PopoverTrigger>
    <PopoverContent class="w-[300px] p-0" align="start">
      <div class="p-2 border-b">
        <Input
          v-model="searchQuery"
          placeholder="Search icons..."
          class="h-8"
        />
      </div>
      <div class="max-h-[300px] overflow-y-auto p-2">
        <div v-if="filteredIcons.length === 0" class="text-center py-6 text-sm text-muted-foreground">
          No icons found
        </div>
        <div class="grid grid-cols-6 gap-1">
          <button
            v-for="icon in filteredIcons"
            :key="icon"
            type="button"
            @click="selectIcon(icon)"
            class="p-2 rounded-md hover:bg-muted transition-colors flex items-center justify-center"
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
import { ref, computed, shallowRef } from 'vue';
import Button from './button.vue';
import Input from './input.vue';
import Popover from './popover.vue';
import PopoverTrigger from './popover-trigger.vue';
import PopoverContent from './popover-content.vue';
import { ChevronsUpDown, X } from 'lucide-vue-next';
import * as LucideIcons from 'lucide-vue-next';

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

const selectedIcon = computed(() => props.modelValue);

// Common menu icons - curated subset
const commonIcons = [
  'Home', 'Menu', 'Search', 'Settings', 'User', 'Users',
  'Mail', 'Phone', 'MapPin', 'Calendar', 'Clock', 'Bell',
  'Heart', 'Star', 'Bookmark', 'Tag', 'Flag', 'Gift',
  'ShoppingCart', 'ShoppingBag', 'CreditCard', 'Wallet', 'DollarSign', 'Percent',
  'FileText', 'File', 'Folder', 'Image', 'Video', 'Music',
  'Camera', 'Mic', 'Headphones', 'Monitor', 'Smartphone', 'Tablet',
  'Globe', 'Compass', 'Map', 'Navigation', 'Send', 'Share',
  'Link', 'ExternalLink', 'Download', 'Upload', 'Cloud', 'Database',
  'Lock', 'Unlock', 'Shield', 'Key', 'Eye', 'EyeOff',
  'CheckCircle', 'XCircle', 'AlertCircle', 'Info', 'HelpCircle', 'AlertTriangle',
  'Zap', 'Sparkles', 'Flame', 'Lightbulb', 'Award', 'Trophy',
  'Rocket', 'Target', 'TrendingUp', 'BarChart', 'PieChart', 'Activity',
  'Code', 'Terminal', 'Cpu', 'Server', 'Wifi', 'Bluetooth',
  'MessageSquare', 'MessageCircle', 'AtSign', 'Hash', 'Rss', 'Radio',
  'Building', 'Building2', 'Store', 'Briefcase', 'Newspaper', 'BookOpen',
  'GraduationCap', 'Pen', 'Pencil', 'Edit', 'Trash', 'Plus',
  'Minus', 'X', 'Check', 'ChevronRight', 'ChevronDown', 'ArrowRight',
  'Play', 'Pause', 'SkipForward', 'SkipBack', 'Volume2', 'VolumeX',
  'Sun', 'Moon', 'Cloud', 'CloudRain', 'Umbrella', 'Thermometer',
  'Coffee', 'Utensils', 'Pizza', 'Wine', 'Beer', 'Cake',
];

const filteredIcons = computed(() => {
  if (!searchQuery.value) return commonIcons;
  const query = searchQuery.value.toLowerCase();
  return commonIcons.filter(icon => 
    icon.toLowerCase().includes(query)
  );
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
