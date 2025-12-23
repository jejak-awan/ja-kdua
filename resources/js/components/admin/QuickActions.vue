<template>
  <div class="quick-actions bg-card rounded-lg shadow-sm border border-border p-6">
    <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
      {{ $t('features.dashboard.widgets.quickActions.title') }}
    </h3>
    
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
      <!-- Create Post -->
      <button
        @click="handleAction('create-post')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-500/20 group-hover:bg-blue-600 dark:group-hover:bg-blue-600 transition-colors">
          <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.createPost') }}</span>
      </button>

      <!-- Create Page -->
      <button
        @click="handleAction('create-page')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-500/20 group-hover:bg-purple-600 dark:group-hover:bg-purple-600 transition-colors">
          <svg class="w-6 h-6 text-purple-600 dark:text-purple-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.createPage') }}</span>
      </button>

      <!-- Upload Media -->
      <button
        @click="handleAction('upload-media')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-500/20 group-hover:bg-green-600 dark:group-hover:bg-green-600 transition-colors">
          <svg class="w-6 h-6 text-green-600 dark:text-green-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.uploadMedia') }}</span>
      </button>

      <!-- Create Category -->
      <button
        @click="handleAction('create-category')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-500/20 group-hover:bg-yellow-600 dark:group-hover:bg-yellow-600 transition-colors">
          <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.createCategory') }}</span>
      </button>

      <!-- Create Tag -->
      <button
        @click="handleAction('create-tag')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-pink-500/20 group-hover:bg-pink-600 dark:group-hover:bg-pink-600 transition-colors">
          <svg class="w-6 h-6 text-pink-600 dark:text-pink-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.createTag') }}</span>
      </button>

      <!-- Manage Users -->
      <button
        @click="handleAction('manage-users')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-indigo-500/20 group-hover:bg-indigo-600 dark:group-hover:bg-indigo-600 transition-colors">
          <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.manageUsers') }}</span>
      </button>

      <!-- View Comments -->
      <button
        @click="handleAction('view-comments')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-orange-500/20 group-hover:bg-orange-600 dark:group-hover:bg-orange-600 transition-colors">
          <svg class="w-6 h-6 text-orange-600 dark:text-orange-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.viewComments') }}</span>
      </button>

      <!-- Settings -->
      <button
        @click="handleAction('settings')"
        class="flex flex-col items-center justify-center p-4 rounded-lg border-2 border-border hover:border-blue-500 dark:hover:border-blue-500 transition-all hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed group"
        :disabled="loading"
      >
        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-500/20 group-hover:bg-gray-600 dark:group-hover:bg-gray-600 transition-colors">
          <svg class="w-6 h-6 text-muted-foreground dark:text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <span class="mt-2 text-sm font-medium text-foreground">{{ $t('features.dashboard.widgets.quickActions.settings') }}</span>
      </button>
    </div>
    
    <!-- Recent Actions (Optional) -->
    <div v-if="showRecent && recentActions.length > 0" class="mt-6 pt-6 border-t border-border">
      <h4 class="text-sm font-medium text-foreground dark:text-gray-300 mb-3">{{ $t('features.dashboard.widgets.quickActions.recentActions') }}</h4>
      <div class="space-y-2">
        <div
          v-for="action in recentActions.slice(0, 3)"
          :key="action.id"
          class="flex items-center text-sm text-muted-foreground dark:text-gray-400 hover:text-foreground dark:hover:text-white transition-colors cursor-pointer"
          @click="repeatAction(action)"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ getActionLabel(action.action) }}</span>
          <span class="ml-auto text-xs">{{ formatTime(action.timestamp) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();

const props = defineProps({
  showRecent: {
    type: Boolean,
    default: true,
  },
});

const loading = ref(false);
const recentActions = ref([]);

const actionRoutes = {
  'create-post': { name: 'contents.create', query: { type: 'post' } },
  'create-page': { name: 'contents.create', query: { type: 'page' } },
  'upload-media': { name: 'media' },
  'create-category': { name: 'categories' },
  'create-tag': { name: 'tags' },
  'manage-users': { name: 'users' },
  'view-comments': { name: 'comments' },
  'settings': { name: 'settings' },
};

const actionLabels = {
  'create-post': 'features.dashboard.widgets.quickActions.createPost',
  'create-page': 'features.dashboard.widgets.quickActions.createPage',
  'upload-media': 'features.dashboard.widgets.quickActions.uploadMedia',
  'create-category': 'features.dashboard.widgets.quickActions.createCategory',
  'create-tag': 'features.dashboard.widgets.quickActions.createTag',
  'manage-users': 'features.dashboard.widgets.quickActions.manageUsers',
  'view-comments': 'features.dashboard.widgets.quickActions.viewComments',
  'settings': 'features.dashboard.widgets.quickActions.settings',
};

const getActionLabel = (action) => {
    const key = actionLabels[action];
    return key ? t(key) : action;
};

const handleAction = (action) => {
  if (loading.value) return;
  
  loading.value = true;
  
  // Save to recent actions
  saveRecentAction(action);
  
  // Navigate
  const route = actionRoutes[action];
  if (route) {
    router.push(route);
  }
  
  setTimeout(() => {
    loading.value = false;
  }, 500);
};

const saveRecentAction = (action) => {
  const newAction = {
    id: Date.now(),
    action,
    timestamp: new Date().toISOString(),
  };
  
  // Get from localStorage
  const stored = localStorage.getItem('quickActions_recent');
  const actions = stored ? JSON.parse(stored) : [];
  
  // Add new action (avoid duplicates)
  const filtered = actions.filter(a => a.action !== action);
  filtered.unshift(newAction);
  
  // Keep only last 10
  const limited = filtered.slice(0, 10);
  
  // Save back
  localStorage.setItem('quickActions_recent', JSON.stringify(limited));
  recentActions.value = limited;
};

const repeatAction = (action) => {
  handleAction(action.action);
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;
  
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);
  
  if (minutes < 1) return t('features.dashboard.widgets.quickActions.time.justNow');
  if (minutes < 60) return t('features.dashboard.widgets.quickActions.time.ago', { time: `${minutes}m` });
  if (hours < 24) return t('features.dashboard.widgets.quickActions.time.ago', { time: `${hours}h` });
  return t('features.dashboard.widgets.quickActions.time.ago', { time: `${days}d` });
};

const loadRecentActions = () => {
  const stored = localStorage.getItem('quickActions_recent');
  if (stored) {
    recentActions.value = JSON.parse(stored);
  }
};

onMounted(() => {
  if (props.showRecent) {
    loadRecentActions();
  }
});
</script>

