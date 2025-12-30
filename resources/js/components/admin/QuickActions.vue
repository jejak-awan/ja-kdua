<template>
  <Card class="quick-actions h-full hover:shadow-md transition-all duration-300">
    <CardHeader class="pb-3">
      <CardTitle class="text-xl font-bold flex items-center gap-2">
        <Zap class="w-5 h-5 text-amber-500 fill-amber-500" />
        {{ $t('features.dashboard.widgets.quickActions.title') }}
      </CardTitle>
    </CardHeader>
    
    <CardContent>
      <div class="grid grid-cols-2 gap-3">
        <!-- Create Post -->
        <button
          v-if="authStore.hasPermission('create content')"
          @click="handleAction('create-post')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all group relative overflow-hidden"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-indigo-500/10 text-indigo-500 group-hover:scale-110 transition-transform">
            <FileEdit class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createPost') }}
          </span>
        </button>
 
        <!-- Create Page -->
        <button
          v-if="authStore.hasPermission('create content')"
          @click="handleAction('create-page')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-purple-500/50 hover:bg-purple-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-purple-500/10 text-purple-500 group-hover:scale-110 transition-transform">
            <PlusSquare class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createPage') }}
          </span>
        </button>
 
        <!-- Upload Media -->
        <button
          v-if="authStore.hasPermission('upload media') || authStore.hasPermission('create media')"
          @click="handleAction('upload-media')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-emerald-500/50 hover:bg-emerald-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-emerald-500/10 text-emerald-500 group-hover:scale-110 transition-transform">
            <Upload class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.uploadMedia') }}
          </span>
        </button>
 
        <!-- Create Category -->
        <button
          v-if="authStore.hasPermission('create categories')"
          @click="handleAction('create-category')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-amber-500/50 hover:bg-amber-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-amber-500/10 text-amber-500 group-hover:scale-110 transition-transform">
            <Hash class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createCategory') }}
          </span>
        </button>
 
        <!-- Create Tag -->
        <button
          v-if="authStore.hasPermission('create tags')"
          @click="handleAction('create-tag')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-rose-500/50 hover:bg-rose-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-rose-500/10 text-rose-500 group-hover:scale-110 transition-transform">
            <Tag class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.createTag') }}
          </span>
        </button>
 
        <!-- Manage Users -->
        <button
          v-if="authStore.hasPermission('view users')"
          @click="handleAction('manage-users')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-cyan-500/50 hover:bg-cyan-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-cyan-500/10 text-cyan-500 group-hover:scale-110 transition-transform">
            <UserCog class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.manageUsers') }}
          </span>
        </button>
 
        <!-- View Comments -->
        <button
          v-if="authStore.hasPermission('view comments')"
          @click="handleAction('view-comments')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-orange-500/50 hover:bg-orange-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-orange-500/10 text-orange-500 group-hover:scale-110 transition-transform">
            <MessageSquare class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.viewComments') }}
          </span>
        </button>
 
        <!-- Settings -->
        <button
          v-if="authStore.hasPermission('view settings')"
          @click="handleAction('settings')"
          class="flex flex-col items-center justify-center p-3 rounded-lg border border-border hover:border-slate-500/50 hover:bg-slate-500/5 transition-all group"
          :disabled="loading"
        >
          <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 group-hover:scale-110 transition-transform">
            <Settings class="w-5 h-5" />
          </div>
          <span class="mt-2 text-xs font-semibold text-foreground text-center line-clamp-1 leading-tight w-full">
            {{ $t('features.dashboard.widgets.quickActions.settings') }}
          </span>
        </button>
      </div>
      
      <!-- Recent Actions -->
      <div v-if="showRecent && recentActions.length > 0" class="mt-6 pt-4 border-t border-border/40">
        <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-3">{{ $t('features.dashboard.widgets.quickActions.recentActions') }}</h4>
        <div class="space-y-1">
          <div
            v-for="action in recentActions.slice(0, 3)"
            :key="action.id"
            class="flex items-center p-2 rounded-lg text-sm text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-all cursor-pointer group"
            @click="repeatAction(action)"
          >
            <Clock class="w-4 h-4 mr-2 opacity-50 group-hover:opacity-100 transition-opacity" />
            <span class="flex-1 truncate font-medium">{{ getActionLabel(action.action) }}</span>
            <span class="text-[10px] tabular-nums opacity-50">{{ formatTime(action.timestamp) }}</span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../../stores/auth';
import Card from '@/components/ui/card.vue';
import CardHeader from '@/components/ui/card-header.vue';
import CardTitle from '@/components/ui/card-title.vue';
import CardContent from '@/components/ui/card-content.vue';
import { 
  Zap, 
  FileEdit, 
  PlusSquare, 
  Upload, 
  Hash, 
  Tag, 
  UserCog, 
  MessageSquare, 
  Settings,
  Clock 
} from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();

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
  saveRecentAction(action);
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
  
  const stored = localStorage.getItem('quickActions_recent');
  const actions = stored ? JSON.parse(stored) : [];
  const filtered = actions.filter(a => a.action !== action);
  filtered.unshift(newAction);
  const limited = filtered.slice(0, 10);
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

