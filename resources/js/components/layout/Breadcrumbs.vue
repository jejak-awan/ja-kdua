<template>
  <nav 
    v-if="breadcrumbs.length > 0" 
    :class="navClasses"
    aria-label="Breadcrumb"
  >
    <ol :class="compact ? 'flex items-center space-x-1 text-xs' : 'flex items-center flex-wrap space-x-2 text-sm'">
      <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
        <!-- Home Icon for first item -->
        <router-link
          v-if="index === 0"
          :to="crumb.path"
          :class="compact 
            ? 'flex items-center text-muted-foreground hover:text-foreground dark:hover:text-foreground transition-colors' 
            : 'flex items-center text-muted-foreground hover:text-foreground dark:hover:text-foreground transition-colors'"
          :aria-current="index === breadcrumbs.length - 1 ? 'page' : undefined"
        >
          <svg :class="compact ? 'h-3.5 w-3.5' : 'h-4 w-4'" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
          </svg>
          <span v-if="crumb.label && !compact" class="ml-1">{{ crumb.label }}</span>
        </router-link>

        <!-- Regular breadcrumb items -->
        <template v-else>
          <!-- Separator -->
          <svg
            :class="compact 
              ? 'flex-shrink-0 h-3 w-3 text-muted-foreground mx-1' 
              : 'flex-shrink-0 h-4 w-4 text-muted-foreground mx-2'"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
              clip-rule="evenodd"
            />
          </svg>

          <!-- Link or Text -->
          <router-link
            v-if="index < breadcrumbs.length - 1"
            :to="crumb.path"
            :class="compact 
              ? 'text-muted-foreground hover:text-foreground dark:hover:text-foreground transition-colors truncate max-w-[100px]' 
              : 'text-muted-foreground hover:text-foreground dark:hover:text-foreground transition-colors truncate max-w-[200px] sm:max-w-[120px]'"
          >
            {{ crumb.label }}
          </router-link>
          <span
            v-else
            :class="compact 
              ? 'text-foreground font-medium truncate max-w-[150px]' 
              : 'text-foreground font-medium truncate max-w-[200px] sm:max-w-[120px]'"
            aria-current="page"
          >
            {{ crumb.label }}
          </span>
        </template>
      </li>
    </ol>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useTheme } from '@/composables/useTheme';

const props = withDefaults(defineProps<{
  compact?: boolean;
}>(), {
  compact: false,
});

const route = useRoute();
const { getBreadcrumbs } = useBreadcrumbs();
const { getSetting } = useTheme();

const breadcrumbs = computed(() => getBreadcrumbs(route));

// Theme settings
const isSticky = computed(() => getSetting('breadcrumb_sticky', false));
const isHeaderSticky = computed(() => getSetting('header_sticky', true));

const navClasses = computed(() => {
  if (props.compact) {
    return 'flex items-center';
  }

  const classes = [
    'py-3 px-4 mb-4 bg-card/95'
  ];

  if (isSticky.value) {
    classes.push('sticky z-20 border-b border-border/40'); // Removed backdrop-blur-sm for performance
    
    // Adjust top position based on header
    if (isHeaderSticky.value) {
      classes.push('top-16'); // Assuming header is h-16
    } else {
      classes.push('top-0');
    }
  }

  return classes;
});
</script>
