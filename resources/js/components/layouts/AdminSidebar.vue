<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 bg-sidebar text-sidebar-foreground border-r border-border shadow-sm transition-all duration-300',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarMinimized ? 'w-[68px]' : 'w-64'
        ]"
    >
        <!-- Floating Toggle Button (Desktop) -->
        <button
            @click="$emit('toggle-minimize')"
            class="hidden lg:flex absolute -right-3 top-5 items-center justify-center h-6 w-6 rounded-full border border-border bg-sidebar text-muted-foreground hover:text-foreground shadow-sm z-[51] transition-colors"
            :title="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
        >
            <ChevronLeft v-if="!sidebarMinimized" class="w-3 h-3" />
            <ChevronRight v-else class="w-3 h-3" />
        </button>

        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-border">
                <div v-if="!sidebarMinimized" class="flex items-center">
                    <h1 class="text-xl font-bold">JA CMS</h1>
                </div>
                <div v-else class="flex items-center justify-center w-full">
                    <span class="text-xl font-bold">JA</span>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Mobile Close Button -->
                    <button
                        @click="$emit('close')"
                        class="lg:hidden text-muted-foreground hover:text-accent-foreground"
                    >
                        <X class="w-6 h-6" />
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <!-- Dashboard (standalone) -->
                <router-link
                    :to="'/admin'"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group"
                    :class="[
                        $route.name === 'dashboard'
                            ? 'bg-accent text-foreground'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                    ]"
                    :title="sidebarMinimized ? t('common.navigation.menu.dashboard') : ''"
                >
                    <component :is="getIcon('dashboard')" class="w-5 h-5 flex-shrink-0" />
                    <span v-if="!sidebarMinimized" class="ml-3 truncate">{{ t('common.navigation.menu.dashboard') }}</span>
                </router-link>

                <!-- Collapsible Sections -->
                <template v-for="section in sidebarSections" :key="section.key">
                    <div v-if="filteredNavigation[section.key]?.length > 0" class="pt-2">
                        
                        <!-- EXPANDED MODE: Accordion Style -->
                        <template v-if="!sidebarMinimized">
                            <!-- Section Header -->
                            <button
                                @click="toggleSection(section.key)"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground tracking-wide transition-colors rounded-md hover:bg-muted/50"
                            >
                                <div class="flex items-center gap-2">
                                    <component :is="section.icon" class="w-4 h-4" />
                                    <span>{{ t(section.labelKey) }}</span>
                                </div>
                                <ChevronDown 
                                    class="w-3.5 h-3.5 transition-transform duration-200" 
                                    :class="{ 'rotate-180': expandedSections[section.key] }"
                                />
                            </button>

                            <!-- Section Items -->
                            <div 
                                v-show="expandedSections[section.key]"
                                class="mt-1 space-y-0.5"
                            >
                                <router-link
                                    v-for="item in filteredNavigation[section.key]"
                                    :key="item.name"
                                    :to="item.to"
                                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors group pl-9"
                                    :class="[
                                        $route.name === item.name
                                            ? 'bg-accent text-foreground'
                                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                                    ]"
                                >
                                    <component :is="getIcon(item.name)" class="w-4 h-4 flex-shrink-0 mr-2.5" />
                                    <span class="truncate">{{ getNavigationLabel(item) }}</span>
                                </router-link>
                            </div>
                        </template>

                        <!-- MINIMIZED MODE: Group Icon with Floating Menu -->
                        <div 
                            v-else 
                            class="relative group"
                            @mouseenter="openPopup(section.key, $event)"
                            @mouseleave="scheduleClosePopup(section.key)"
                        >
                            <button
                                @click="togglePopup(section.key, $event)"
                                class="w-full flex items-center justify-center p-2.5 rounded-lg transition-colors cursor-pointer"
                                :class="[
                                    isSectionActive(section.key) || activePopup === section.key
                                        ? 'bg-accent text-foreground'
                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                                ]"
                            >
                                <component :is="section.icon" class="w-5 h-5" />
                            </button>

                            <!-- Floating Submenu (Teleported to body) -->
                            <Teleport to="body">
                                <Transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="opacity-0 translate-x-1"
                                    enter-to-class="opacity-100 translate-x-0"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="opacity-100 translate-x-0"
                                    leave-to-class="opacity-0 translate-x-1"
                                >
                                    <div 
                                        v-if="activePopup === section.key"
                                        ref="popups"
                                        class="fixed z-[9999] w-56 bg-sidebar border border-border rounded-lg shadow-lg py-2 ml-2 max-h-[90vh] overflow-y-auto"
                                        :style="{ top: popupTop + 'px', left: popupLeft + 'px' }"
                                        @mouseenter="openPopup(section.key)"
                                        @mouseleave="scheduleClosePopup(section.key)"
                                    >
                                        <!-- Header -->
                                        <div class="px-3 py-1.5 text-xs font-semibold text-muted-foreground tracking-wide border-b border-border mb-1">
                                            {{ t(section.labelKey) }}
                                        </div>
                                        
                                        <!-- Items -->
                                        <router-link
                                            v-for="item in filteredNavigation[section.key]"
                                            :key="item.name"
                                            :to="item.to"
                                            @click="activePopup = null"
                                            class="flex items-center px-3 py-2 text-sm font-medium transition-colors hover:bg-accent mx-1 rounded-md"
                                            :class="[
                                                $route.name === item.name
                                                    ? 'text-foreground bg-accent'
                                                    : 'text-muted-foreground hover:text-accent-foreground'
                                            ]"
                                        >
                                            <component :is="getIcon(item.name)" class="w-4 h-4 flex-shrink-0 mr-2.5" />
                                            <span class="truncate">{{ getNavigationLabel(item) }}</span>
                                        </router-link>
                                    </div>
                                </Transition>
                            </Teleport>
                        </div>

                    </div>
                </template>
            </nav>
        </div>
    </aside>
</template>

<script setup>
import { computed, ref, watch, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { navigationGroups } from '../../utils/navigation';
import { getIcon } from '../../utils/icons';
import { useAuthStore } from '../../stores/auth';
import { 
    ChevronLeft, 
    ChevronRight, 
    ChevronDown, 
    X,
    FileText,
    Image,
    MessageSquare,
    Users,
    Palette,
    BarChart3,
    ScrollText,
    Settings,
    Code
} from 'lucide-vue-next';

const props = defineProps({
    sidebarMinimized: {
        type: Boolean,
        default: false,
    },
    sidebarOpen: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

defineEmits(['toggle-minimize', 'close', 'logout']);

const { t, te } = useI18n();
const $route = useRoute();
const authStore = useAuthStore();

// Section definitions with icons
const sidebarSections = [
    { key: 'content', labelKey: 'common.navigation.sections.content', icon: FileText },
    { key: 'media', labelKey: 'common.navigation.sections.media', icon: Image },
    { key: 'engagement', labelKey: 'common.navigation.sections.engagement', icon: MessageSquare },
    { key: 'users', labelKey: 'common.navigation.sections.usersAccess', icon: Users },
    { key: 'appearance', labelKey: 'common.navigation.sections.appearance', icon: Palette },
    { key: 'analytics', labelKey: 'common.navigation.sections.analyticsSeo', icon: BarChart3 },
    { key: 'logs', labelKey: 'common.navigation.sections.logs', icon: ScrollText },
    { key: 'system', labelKey: 'common.navigation.sections.system', icon: Settings },
    { key: 'developer', labelKey: 'common.navigation.sections.developer', icon: Code },
];

const expandedSections = ref({});
const activePopup = ref(null);
const popupTop = ref(0);
const popupLeft = ref(0);
const popups = ref([]);
let popupCloseTimeout = null;

// Load persisted state
onMounted(() => {
    const saved = localStorage.getItem('sidebarExpandedSections');
    if (saved) {
        try {
            expandedSections.value = JSON.parse(saved);
        } catch {
            initializeExpandedSections();
        }
    } else {
        initializeExpandedSections();
    }
    
    // Auto-expand section based on current route
    autoExpandActiveSection();
});

const initializeExpandedSections = () => {
    // Default: first 3 sections expanded
    sidebarSections.forEach((section, index) => {
        expandedSections.value[section.key] = index < 3;
    });
};

// Save to localStorage when changed
watch(expandedSections, (newVal) => {
    localStorage.setItem('sidebarExpandedSections', JSON.stringify(newVal));
}, { deep: true });

// Auto-expand section when route changes
watch(() => $route.name, () => {
    autoExpandActiveSection();
});

const autoExpandActiveSection = () => {
    for (const section of sidebarSections) {
        const items = filteredNavigation.value[section.key] || [];
        if (items.some(item => item.name === $route.name)) {
            expandedSections.value[section.key] = true;
            break;
        }
    }
};

const toggleSection = (key) => {
    expandedSections.value[key] = !expandedSections.value[key];
};

const openPopup = async (key, event = null) => {
    if (popupCloseTimeout) {
        clearTimeout(popupCloseTimeout);
        popupCloseTimeout = null;
    }
    
    if (event) {
        const target = event.currentTarget;
        let anchor = target;
        if (target.tagName !== 'BUTTON') {
             const btn = target.querySelector('button');
             if (btn) anchor = btn;
        }
        
        const rect = anchor.getBoundingClientRect();
        popupTop.value = rect.top;
        popupLeft.value = rect.right;
    }
    
    activePopup.value = key;

    await nextTick();

    // Adjust position if out of viewport
    if (popups.value && popups.value.length > 0) {
        // In Vue 3 with v-for and v-if, the ref array contains the mounted elements
        const content = popups.value.find(el => el);
        if (content) {
            const rect = content.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const bottomEdge = rect.top + rect.height;
            
            // If overlapping bottom edge
            if (bottomEdge > windowHeight - 20) {
                // Shift up to fit, with 20px padding from bottom
                popupTop.value = Math.max(10, windowHeight - rect.height - 20);
            }
        }
    }
};

const closePopup = (key) => {
    if (activePopup.value === key) {
        activePopup.value = null;
    }
};

const scheduleClosePopup = (key) => {
    popupCloseTimeout = setTimeout(() => {
        closePopup(key);
    }, 150);
};

const togglePopup = (key, event) => {
    if (activePopup.value === key) {
        activePopup.value = null;
    } else {
        openPopup(key, event);
    }
};

const isSectionActive = (key) => {
    const items = filteredNavigation.value[key] || [];
    return items.some(item => item.name === $route.name);
};

// Filter all navigation groups based on permissions
const filteredNavigation = computed(() => {
    const filtered = {};
    for (const [group, items] of Object.entries(navigationGroups)) {
        filtered[group] = items.filter(item => {
            if (!item.permission) return true;
            return authStore.hasPermission(item.permission);
        });
    }
    return filtered;
});

const getNavigationLabel = (item) => {
    const camelName = item.name.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
    const key = `common.navigation.menu.${camelName}`;
    return te(key) ? t(key) : item.label;
};
</script>
