<template>
  <div 
    v-if="totalItems > 0"
    :class="cn(
      'flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 bg-card border border-border rounded-lg',
      props.class
    )"
  >
    <!-- Left: Showing info and rows per page -->
    <div class="flex items-center gap-4 text-xs text-muted-foreground">
      <p>
        {{ $t('common.pagination.showing', { 
          from: from, 
          to: to, 
          total: totalItems 
        }) }}
      </p>
      <div v-if="showPerPage" class="flex items-center gap-2">
        <span>{{ $t('common.pagination.rowsPerPage') }}</span>
        <Select :model-value="String(perPage)" @update:model-value="handlePerPageChange">
          <SelectTrigger class="w-[70px] h-8 text-xs">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="option in perPageOptions" :key="option" :value="String(option)">
              {{ option }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>
    
    <!-- Right: Navigation buttons -->
    <div class="flex items-center gap-2">
      <!-- First page button (optional) -->
      <Button
        v-if="showFirstLast && totalPages > 2"
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        @click="goToPage(1)"
        class="h-8 w-8 p-0 transition-none"
      >
        <ChevronsLeft class="w-4 h-4" />
      </Button>
      
      <!-- Previous button -->
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        @click="goToPage(currentPage - 1)"
        class="transition-none"
      >
        <ChevronLeft class="w-4 h-4 mr-1" />
        {{ $t('common.pagination.previous') }}
      </Button>
      
      <!-- Page numbers (optional) -->
      <div v-if="showPageNumbers" class="hidden sm:flex items-center gap-1">
        <template v-for="page in visiblePages" :key="page">
          <Button
            v-if="page !== '...'"
            :variant="page === currentPage ? 'default' : 'outline'"
            size="sm"
            class="h-8 w-8 p-0 transition-none"
            @click="goToPage(page)"
          >
            {{ page }}
          </Button>
          <span v-else class="px-2 text-muted-foreground">...</span>
        </template>
      </div>
      
      <!-- Next button -->
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)"
        class="transition-none"
      >
        {{ $t('common.pagination.next') }}
        <ChevronRight class="w-4 h-4 ml-1" />
      </Button>
      
      <!-- Last page button (optional) -->
      <Button
        v-if="showFirstLast && totalPages > 2"
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        @click="goToPage(totalPages)"
        class="h-8 w-8 p-0 transition-none"
      >
        <ChevronsRight class="w-4 h-4" />
      </Button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import Button from '@/components/ui/button.vue';
import Select from '@/components/ui/select.vue';
import SelectContent from '@/components/ui/select-content.vue';
import SelectItem from '@/components/ui/select-item.vue';
import SelectTrigger from '@/components/ui/select-trigger.vue';
import SelectValue from '@/components/ui/select-value.vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  // Current page number (1-indexed)
  currentPage: {
    type: Number,
    required: true,
  },
  // Total number of items
  totalItems: {
    type: Number,
    required: true,
  },
  // Items per page
  perPage: {
    type: Number,
    default: 10,
  },
  // Available per page options
  perPageOptions: {
    type: Array,
    default: () => [5, 10, 15, 20, 25, 50, 100],
  },
  // Show rows per page selector
  showPerPage: {
    type: Boolean,
    default: true,
  },
  // Show page number buttons
  showPageNumbers: {
    type: Boolean,
    default: false,
  },
  // Show first/last page buttons
  showFirstLast: {
    type: Boolean,
    default: false,
  },
  // Maximum visible page buttons
  maxVisiblePages: {
    type: Number,
    default: 5,
  },
  // Custom class
  class: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:currentPage', 'update:perPage', 'page-change']);

// Computed properties
const totalPages = computed(() => {
  return Math.ceil(props.totalItems / props.perPage) || 1;
});

const from = computed(() => {
  return (props.currentPage - 1) * props.perPage + 1;
});

const to = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = props.currentPage;
  const max = props.maxVisiblePages;
  
  if (total <= max) {
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    const half = Math.floor(max / 2);
    let start = current - half;
    let end = current + half;
    
    if (start < 1) {
      start = 1;
      end = max;
    }
    
    if (end > total) {
      end = total;
      start = total - max + 1;
    }
    
    if (start > 1) {
      pages.push(1);
      if (start > 2) pages.push('...');
    }
    
    for (let i = start; i <= end; i++) {
      if (i > 1 && i < total) {
        pages.push(i);
      }
    }
    
    if (end < total) {
      if (end < total - 1) pages.push('...');
      pages.push(total);
    }
  }
  
  return pages;
});

// Methods
const goToPage = (page) => {
  if (page < 1 || page > totalPages.value || page === props.currentPage) return;
  emit('update:currentPage', page);
  emit('page-change', page);
};

const handlePerPageChange = (value) => {
  const newPerPage = parseInt(value, 10);
  emit('update:perPage', newPerPage);
  // Reset to page 1 when changing per page
  emit('update:currentPage', 1);
  emit('page-change', 1);
};
</script>
