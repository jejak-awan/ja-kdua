<template>
  <Dialog :open="open" @update:open="setOpen">
    <DialogContent 
      hide-close
      class="p-0 gap-0 max-w-2xl overflow-hidden bg-background text-foreground rounded-xl border border-border/50 shadow-2xl"
    >
      <!-- Search Input Header -->
      <div class="flex items-center border-b border-border/40 px-4 py-2" cmdk-input-wrapper>
        <Search class="ml-1 h-5 w-5 shrink-0 opacity-50" />
        <input
          ref="inputRef"
          v-model="searchQuery"
          @input="handleSearch"
          @keydown="handleKeydown"
          data-slot="search-input"
          class="flex h-12 w-full bg-transparent px-3 text-base outline-none border-none focus:ring-0 placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
          :placeholder="t('common.actions.search') + '...'"
          autocomplete="off" 
          autocorrect="off" 
          spellcheck="false"
        />
        <div class="flex items-center gap-1">
            <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
            <kbd class="pointer-events-none inline-flex h-5 select-none items-center gap-1 rounded-md border border-border/40 bg-muted px-1.5 font-mono text-[10px] font-medium text-muted-foreground opacity-100">
                <span class="text-xs uppercase">Esc</span>
            </kbd>
        </div>
      </div>

      <!-- Results List -->
      <div 
        class="max-h-[60vh] overflow-y-auto overflow-x-hidden py-2" 
        ref="listRef" 
        role="listbox" 
        id="cmdk-list"
      >
        <!-- Loading State (Initial) -->
        <div v-if="loading && !results.length && searchQuery" class="py-6 text-center text-sm text-muted-foreground">
           {{ t('common.messages.loading.searching') }}...
        </div>

        <!-- Empty State -->
        <div v-if="!loading && searchQuery && results.length === 0 && matchingStaticActions.length === 0" class="py-6 text-center text-sm text-muted-foreground">
           {{ t('common.messages.empty.search', { query: searchQuery }) }}
        </div>

        <!-- Default/Static Actions (When Empty) -->
        <template v-if="!searchQuery && !results.length">
            <div class="px-2">
                <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground">
                    {{ t('common.labels.quickActions') }}
                </div>
                <!-- Static Navigation Items -->
                <div 
                    v-for="(item, index) in staticActions" 
                    :key="'static-' + index"
                    :class="[
                        'relative flex cursor-default select-none items-center rounded-md px-3 py-2 text-sm outline-none transition-colors data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
                        selectedIndex === index ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-foreground' : 'text-foreground/70 hover:bg-muted/50 hover:text-foreground'
                    ]"
                    @click="handleSelect(item)"
                    @mousemove="selectedIndex = index"
                >
                    <component :is="item.icon" class="mr-2 h-4 w-4" />
                    <span>{{ item.title }}</span>
                    <span v-if="item.shortcut" class="ml-auto text-xs text-muted-foreground">
                        {{ item.shortcut }}
                    </span>
                </div>
            </div>
        </template>

        <!-- Loose Match Warning -->
        <div v-if="isLoose && results.length > 0" class="px-4 py-2 text-xs text-amber-500 bg-amber-500/10 border-y border-amber-500/20 mb-2">
            No exact match. Showing similar results.
        </div>

        <!-- Suggestions -->
        <div v-if="suggestions.length > 0 && results.length === 0" class="px-2">
             <div class="px-2 py-2 text-sm text-muted-foreground">
                Did you mean:
             </div>
             <div 
                v-for="(suggestion, idx) in suggestions" 
                :key="'sug-' + idx"
                class="px-2 py-1.5 text-sm cursor-pointer hover:bg-accent hover:text-accent-foreground rounded-sm text-primary font-medium"
                @click="applySuggestion(suggestion.text)"
             >
                {{ suggestion.text }}
             </div>
        </div>

        <!-- Dynamic Results -->
        <template v-for="(group, groupName) in groupedResults" :key="groupName">
             <div class="px-2 pt-2 pb-1 text-xs font-semibold text-muted-foreground">
                {{ groupLabel(groupName) }}
             </div>
             <div class="px-2">
                 <div
                    v-for="(item, index) in group"
                    :key="item.type + '-' + item.id"
                    :class="[
                        'relative flex cursor-default select-none items-center rounded-md px-3 py-2 text-sm outline-none transition-colors',
                        selectedIndex === (groupOffset(groupName) + index) ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-foreground' : 'text-foreground/70 hover:bg-muted/50 hover:text-foreground'
                    ]"
                    @click="handleSelect(item)"
                    @mousemove="updateSelectedIndex(item)"
                 >
                    <FileText v-if="item.type === 'post' || item.type === 'page'" class="mr-2 h-4 w-4" />
                    <Folder v-if="item.type === 'category'" class="mr-2 h-4 w-4" />
                    <Tag v-if="item.type === 'tag'" class="mr-2 h-4 w-4" />
                    <User v-if="item.type === 'user'" class="mr-2 h-4 w-4" />
                    
                    <div class="flex flex-col flex-1 min-w-0">
                        <span class="truncate">{{ item.title }}</span>
                        <span v-if="item.description" class="text-xs text-muted-foreground truncate">{{ item.description }}</span>
                    </div>
                 </div>
             </div>
        </template>
      </div>
      
      <!-- Footer/Status Bar -->
      <div class="flex items-center border-t p-2 text-xs text-muted-foreground bg-muted/20">
            <span class="mr-2">ProTip:</span>
            <span class="flex items-center mr-2"><kbd class="mr-1 rounded bg-muted-foreground/10 px-1 font-sans">↑</kbd><kbd class="rounded bg-muted-foreground/10 px-1 font-sans">↓</kbd> to navigate</span>
            <span class="flex items-center mr-2"><kbd class="mr-1 rounded bg-muted-foreground/10 px-1 font-sans">↵</kbd> to select</span>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../services/api';
import Dialog from './ui/dialog.vue';
import DialogContent from './ui/dialog-content.vue';
import { Search, FileText, Folder, Tag, User, Home, Settings, UserCircle, PlusCircle, LogOut } from 'lucide-vue-next';

// Props & Emitters
const props = defineProps(['isOpen']);
const emit = defineEmits(['update:isOpen', 'close']);

// Utils
const router = useRouter();
const { t } = useI18n();

// State
const open = ref(false);
const searchQuery = ref('');
const results = ref([]);
const suggestions = ref([]);
const isLoose = ref(false);
const loading = ref(false);
const selectedIndex = ref(0);
const searchTimeout = ref(null);
const inputRef = ref(null);
const listRef = ref(null);

// ... existing code ...

const applySuggestion = (text) => {
    searchQuery.value = text;
    handleSearch();
};

// Static Actions (Nav Items) - Comprehensive list of admin shortcuts
const staticActions = computed(() => [
    // Core Navigation
    { title: t('common.navigation.menu.dashboard'), icon: Home, route: { name: 'dashboard' }, type: 'action', keywords: 'home overview' },
    { title: t('features.content.list.createNew'), icon: PlusCircle, route: { name: 'contents.create' }, type: 'action', keywords: 'new post article write' },
    { title: t('common.labels.myProfile'), icon: UserCircle, route: { name: 'profile' }, type: 'action', keywords: 'account me user' },
    { title: t('common.labels.settings'), icon: Settings, route: { name: 'settings' }, type: 'action', keywords: 'config preferences options' },
    
    // Content Management
    { title: t('common.navigation.menu.contents'), icon: FileText, route: { name: 'contents' }, type: 'action', keywords: 'posts articles pages blogs' },
    { title: t('common.navigation.menu.categories'), icon: Folder, route: { name: 'categories' }, type: 'action', keywords: 'taxonomy classify organize' },
    { title: t('common.navigation.menu.tags'), icon: Tag, route: { name: 'tags' }, type: 'action', keywords: 'labels keywords' },
    { title: t('common.navigation.menu.media'), icon: FileText, route: { name: 'media' }, type: 'action', keywords: 'images files upload gallery' },
    
    // User Management
    { title: t('common.navigation.menu.users'), icon: User, route: { name: 'users.index' }, type: 'action', keywords: 'members accounts people' },
    { title: t('common.navigation.menu.roles'), icon: Settings, route: { name: 'roles' }, type: 'action', keywords: 'permissions access rbac' },
    
    // SEO & Analytics
    { title: t('common.navigation.menu.seoTools'), icon: Search, route: { name: 'seo' }, type: 'action', keywords: 'search engine optimize meta robots sitemap' },
    { title: t('common.navigation.menu.analytics'), icon: FileText, route: { name: 'analytics' }, type: 'action', keywords: 'stats visitors traffic' },
    { title: t('common.navigation.menu.redirects'), icon: FileText, route: { name: 'redirects' }, type: 'action', keywords: '301 302 url forward' },
]);

// Computed
const filterActions = computed(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) return [];
    
    return [
        {
            id: 'filter-posts',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.posts'),
            icon: FileText,
            route: { name: 'contents', query: { q: searchQuery.value } },
            group: 'filters'
        },
        {
            id: 'filter-users',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.users'),
            icon: User,
            route: { name: 'users.index', query: { q: searchQuery.value } },
            group: 'filters'
        },
        {
            id: 'filter-categories',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.categories'),
            icon: Folder,
            route: { name: 'categories', query: { q: searchQuery.value } },
            group: 'filters'
        },
        {
            id: 'filter-tags',
            type: 'action',
            title: t('common.actions.searchIn') + ' ' + t('common.labels.tags'),
            icon: Tag,
            route: { name: 'tags', query: { q: searchQuery.value } },
            group: 'filters'
        }
    ];
});

/**
 * Comprehensive Fuzzy Match Utility
 * Returns a score from 0 to 1 based on multiple matching strategies
 */
const fuzzyMatch = (query, target) => {
    if (!query || !target) return 0;
    
    const q = query.toLowerCase().trim();
    const t = target.toLowerCase().trim();
    
    // 1. Exact match (Score: 1.0)
    if (q === t) return 1.0;
    
    // 2. Target starts with query (Score: 0.95)
    if (t.startsWith(q)) return 0.95;
    
    // 3. Target contains query as substring (Score: 0.85)
    if (t.includes(q)) return 0.85;
    
    // 4. Query matches start of any word in target (Score: 0.75)
    // e.g., "prof" matches "My Profile" because "Profile" starts with "prof"
    const words = t.split(/\s+/);
    for (const word of words) {
        if (word.startsWith(q)) return 0.75;
    }
    
    // 5. All query characters appear in order in target (Score: 0.6)
    // e.g., "mprof" matches "My Profile" (m...p-r-o-f)
    let qIdx = 0;
    for (let i = 0; i < t.length && qIdx < q.length; i++) {
        if (t[i] === q[qIdx]) qIdx++;
    }
    if (qIdx === q.length) return 0.6;
    
    // 6. Bigram similarity (Dice Coefficient style)
    // Calculate how many character pairs match
    const getBigrams = (str) => {
        const bigrams = new Set();
        for (let i = 0; i < str.length - 1; i++) {
            bigrams.add(str.substring(i, i + 2));
        }
        return bigrams;
    };
    
    const qBigrams = getBigrams(q);
    const tBigrams = getBigrams(t);
    
    if (qBigrams.size === 0 || tBigrams.size === 0) return 0;
    
    let intersection = 0;
    for (const pair of qBigrams) {
        if (tBigrams.has(pair)) intersection++;
    }
    
    // Dice coefficient: 2 * |A ∩ B| / (|A| + |B|)
    const bigramScore = (2 * intersection) / (qBigrams.size + tBigrams.size);
    
    // Scale it down to max 0.5 since it's a weaker match
    return bigramScore * 0.5;
};

// Minimum score threshold for a match to be considered
const MATCH_THRESHOLD = 0.3;

const matchingStaticActions = computed(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) return [];
    
    // Score all static actions against title AND keywords, take the best
    return staticActions.value
        .map(action => {
            const titleScore = fuzzyMatch(searchQuery.value, action.title);
            const keywordScore = action.keywords ? fuzzyMatch(searchQuery.value, action.keywords) : 0;
            return {
                ...action,
                score: Math.max(titleScore, keywordScore)
            };
        })
        .filter(action => action.score >= MATCH_THRESHOLD)
        .sort((a, b) => b.score - a.score); // Sort by score descending
});

const groupedResults = computed(() => {
    const allItems = [...results.value];
    
    // Add filter actions if searching
    if (searchQuery.value && !loading.value) {
        // Prepend filter actions
        const grouped = {
            filters: filterActions.value,
            ...allItems.reduce((acc, item) => {
                const type = item.type || 'other';
                if (!acc[type]) acc[type] = [];
                acc[type].push(item);
                return acc;
            }, {})
        };
        
        // Add matching static actions if any
        if (matchingStaticActions.value.length) {
            grouped.action = matchingStaticActions.value;
        }

        return grouped;
    }
    
    if (!results.value.length) return {};
    
    // Group by type
    return results.value.reduce((acc, item) => {
        const type = item.type || 'other';
        if (!acc[type]) acc[type] = [];
        acc[type].push(item);
        return acc;
    }, {});
});

const flatResults = computed(() => {
    // If searching, return results + filter actions + matching static actions
    if (searchQuery.value) {
        return [...filterActions.value, ...matchingStaticActions.value, ...results.value];
    }
    
    // If empty, return static actions
    return staticActions.value;
});

// Methods
const setOpen = (value) => {
    open.value = value;
    emit('update:isOpen', value);
    if (!value) {
        searchQuery.value = '';
        results.value = [];
        selectedIndex.value = 0;
    }
};

const handleSearch = () => {
    selectedIndex.value = 0;
    
    if (searchTimeout.value) clearTimeout(searchTimeout.value);
    
    if (!searchQuery.value || searchQuery.value.length < 2) {
        results.value = [];
        loading.value = false;
        return;
    }
    
    loading.value = true;
    searchTimeout.value = setTimeout(async () => {
        try {
            const response = await api.get('/admin/ja/search', {
                params: { q: searchQuery.value, limit: 10 }
            });
            // API returns { data: { results: [...], total: ... } }
            const responseData = response.data?.data || response.data;
            results.value = Array.isArray(responseData) ? responseData : (responseData.results || []);
            suggestions.value = responseData.suggestions || [];
            isLoose.value = responseData.is_loose || false;
        } catch (error) {
            console.error('Search error:', error);
            results.value = [];
            suggestions.value = [];
        } finally {
            loading.value = false;
        }
    }, 300);
};

const handleSelect = (item) => {
    if (!item) return;
    
    setOpen(false);
    
    // 1. Static Actions (Navigation)
    if (item.route) {
        router.push(item.route);
        return;
    } 
    
    // 2. Internal Admin Resources (Prioritize over generic URL)
    // Use searchable_id if available, otherwise fallback to id (just in case)
    const resourceId = item.searchable_id || item.id;
    
    if ((item.type === 'post' || item.type === 'page' || item.type === 'content') && resourceId) {
         router.push({ name: 'contents.edit', params: { id: resourceId } });
         return;
    }
    
    if (item.type === 'category' && resourceId) {
         router.push({ name: 'categories.edit', params: { id: resourceId } });
         return;
    }
    
    if (item.type === 'tag' && resourceId) {
         router.push({ name: 'tags.edit', params: { id: resourceId } });
         return;
    }

    if (item.type === 'user' && resourceId) {
         router.push({ name: 'users.edit', params: { id: resourceId } });
         return;
    }
    
    // 3. Generic URL Fallback (Public links etc)
    if (item.url) {
        if (item.url.startsWith('http')) {
             window.open(item.url, '_blank');
        } else {
             router.push(item.url);
        }
        return;
    }
};

const handleKeydown = (e) => {
    // Navigation Down
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        const max = flatResults.value.length - 1;
        selectedIndex.value = selectedIndex.value >= max ? 0 : selectedIndex.value + 1;
        scrollToSelected();
    }
    // Navigation Up
    else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const max = flatResults.value.length - 1;
        selectedIndex.value = selectedIndex.value <= 0 ? max : selectedIndex.value - 1;
        scrollToSelected();
    }
    // Selection
    else if (e.key === 'Enter') {
        e.preventDefault();
        const item = flatResults.value[selectedIndex.value];
        if (item) handleSelect(item);
    }
    // Close
    else if (e.key === 'Escape') {
        setOpen(false);
    }
};

const scrollToSelected = () => {
    // Basic scroll into view logic for the list box
    // Ideally use scrollIntoView() on the DOM element of the selected index
    // Note: Since we use v-for, we'd need refs array or similar. For MVP, auto-scroll is okay or simple logic.
};

const updateSelectedIndex = (item) => {
    const index = flatResults.value.indexOf(item);
    if (index !== -1) selectedIndex.value = index;
};

const groupLabel = (type) => {
    const labels = {
        filters: t('common.actions.filter'),
        action: t('common.labels.quickActions'),
        post: t('common.labels.posts'),
        page: t('common.labels.pages'),
        category: t('common.labels.categories'),
        tag: t('common.labels.tags'),
        user: t('common.labels.users'),
        other: t('common.labels.other'),
    };
    return labels[type] || type;
};

// Keyboard Shortcuts (Cmd+K)
const onKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        setOpen(!open.value);
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});

// Watch external prop
watch(() => props.isOpen, (val) => {
    open.value = val;
});
</script>

<style scoped>
/* Optional: Custom scrollbar styling if needed */
</style>
