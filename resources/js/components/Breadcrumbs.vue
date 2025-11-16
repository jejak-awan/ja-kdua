<template>
  <nav 
    v-if="breadcrumbs.length > 0" 
    class="py-3 px-4 mb-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700" 
    aria-label="Breadcrumb"
  >
    <ol class="flex items-center flex-wrap space-x-2 text-sm">
      <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
        <!-- Home Icon for first item -->
        <router-link
          v-if="index === 0"
          :to="crumb.path"
          class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
          :aria-current="index === breadcrumbs.length - 1 ? 'page' : undefined"
        >
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
          </svg>
          <span v-if="crumb.label" class="ml-1">{{ crumb.label }}</span>
        </router-link>

        <!-- Regular breadcrumb items -->
        <template v-else>
          <!-- Separator -->
          <svg
            class="flex-shrink-0 h-4 w-4 text-gray-400 dark:text-gray-600 mx-2"
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
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors truncate max-w-[200px] sm:max-w-[120px]"
          >
            {{ crumb.label }}
          </router-link>
          <span
            v-else
            class="text-gray-900 dark:text-white font-medium truncate max-w-[200px] sm:max-w-[120px]"
            aria-current="page"
          >
            {{ crumb.label }}
          </span>
        </template>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

const route = useRoute();
const { getBreadcrumbs } = useBreadcrumbs();

const breadcrumbs = computed(() => getBreadcrumbs(route));
</script>

