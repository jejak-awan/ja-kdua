<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 bg-sidebar text-sidebar-foreground border-r border-border',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarMinimized ? 'w-[68px]' : 'w-64'
        ]"
    >
        <!-- Floating Toggle Button (Desktop) -->
        <button
            @click="$emit('toggle-minimize')"
            class="hidden lg:flex absolute -right-3 top-5 items-center justify-center h-6 w-6 rounded-full border border-border bg-sidebar text-muted-foreground hover:text-foreground shadow-sm z-[51]"
            :title="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
        >
            <component :is="getIcon('chevron-left')" v-if="!sidebarMinimized" />
            <component :is="getIcon('chevron-right')" v-else />
        </button>

        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-border">
                <TooltipProvider>
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <a href="/" target="_blank" class="block hover:opacity-80 focus:outline-none">
                                <AdminLogo :minimized="sidebarMinimized" />
                            </a>
                        </TooltipTrigger>
                        <TooltipContent side="bottom" :side-offset="10">
                            {{ getVisitTooltip }}
                        </TooltipContent>
                    </Tooltip>
                </TooltipProvider>
                <div class="flex items-center gap-2">
                    <!-- Mobile Close Button -->
                    <button
                        @click="$emit('close')"
                        class="lg:hidden text-muted-foreground hover:text-accent-foreground"
                    >
                        <component :is="getIcon('x')" />
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <!-- Dashboard (standalone) -->
                <router-link
                    :to="'/admin'"
                    @click="$emit('close')"
                    class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl group"
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
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground tracking-wide rounded-xl hover:bg-accent"
                            >
                                <div class="flex items-center gap-2">
                                    <component :is="section.icon" class="w-4 h-4" />
                                    <span>{{ t(section.labelKey) }}</span>
                                </div>
                                <component 
                                    :is="getIcon('chevron-down')" 
                                    :class="{ 'rotate-180': expandedSections[section.key] }"
                                />
                            </button>

                            <!-- Section Items -->
                            <div 
                                v-show="expandedSections[section.key]"
                                class="mt-1 space-y-0.5"
                            >
                                <template v-for="item in filteredNavigation[section.key]" :key="item.name || item.label">
                                    <div v-if="item.type === 'divider'" class="py-2 px-9 flex items-center gap-2">
                                        <div class="h-px bg-border flex-1"></div>
                                        <span class="text-[10px] uppercase font-bold text-muted-foreground/30 tracking-widest whitespace-nowrap">{{ getNavigationLabel(item) }}</span>
                                        <div class="h-px bg-border flex-1"></div>
                                    </div>
                                    
                                    <!-- SUB-DROPDOWN -->
                                    <div v-else-if="item.children && item.children.length > 0" class="space-y-0.5">
                                        <button 
                                            @click="toggleSubSection(item.label || '')"
                                            class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-xl group pl-9"
                                            :class="[
                                                isSubSectionActive(item)
                                                    ? 'text-foreground hover:bg-accent'
                                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                                            ]"
                                        >
                                            <div class="flex items-center gap-2.5">
                                                <component :is="getIcon(item.icon || '')" class="w-4 h-4 flex-shrink-0" />
                                                <span class="truncate">{{ getNavigationLabel(item) }}</span>
                                            </div>
                                            <component 
                                                :is="getIcon('chevron-down')" 
                                                :class="{ 'rotate-180': expandedSubSections[item.label || ''] }"
                                                class="w-3.5 h-3.5 transition-transform duration-200"
                                            />
                                        </button>
                                        
                                        <div 
                                            v-show="expandedSubSections[item.label || '']"
                                            class="mt-0.5 space-y-0.5"
                                        >
                                            <router-link
                                                v-for="subItem in item.children"
                                                :key="subItem.name || subItem.label"
                                                :to="subItem.to || ''"
                                                @click="$emit('close')"
                                                class="flex items-center px-3 py-1.5 text-xs font-medium rounded-xl group pl-16"
                                                :class="[
                                                    $route.name === subItem.name
                                                        ? 'bg-accent text-foreground font-semibold'
                                                        : 'text-muted-foreground/80 hover:bg-accent hover:text-accent-foreground'
                                                ]"
                                            >
                                                <component :is="getIcon(subItem.icon || subItem.name || '')" class="w-3.5 h-3.5 flex-shrink-0 mr-2" />
                                                <span class="truncate">{{ getNavigationLabel(subItem) }}</span>
                                            </router-link>
                                        </div>
                                    </div>

                                    <!-- NORMAL ITEM -->
                                    <router-link
                                        v-else
                                        :to="item.to || ''"
                                        @click="$emit('close')"
                                        class="flex items-center px-3 py-2 text-sm font-medium rounded-xl group pl-9"
                                        :class="[
                                            $route.name === item.name
                                                ? 'bg-accent text-foreground'
                                                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                                        ]"
                                    >
                                        <component :is="getIcon(item.icon || item.name || '')" class="w-4 h-4 flex-shrink-0 mr-2.5" />
                                        <span class="truncate">{{ getNavigationLabel(item) }}</span>
                                    </router-link>
                                </template>
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
                                class="w-full flex items-center justify-center p-2.5 rounded-xl cursor-pointer"
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
                                    <div 
                                        v-if="activePopup === section.key"
                                        ref="popups"
                                        class="fixed z-[9999] w-56 bg-popover border border-border/40 rounded-xl py-2 ml-2 max-h-[90vh] overflow-y-auto"
                                        :style="{ top: popupTop + 'px', left: popupLeft + 'px' }"
                                        @mouseenter="openPopup(section.key)"
                                        @mouseleave="scheduleClosePopup(section.key)"
                                    >
                                        <!-- Header -->
                                        <div class="px-3 py-1.5 text-xs font-semibold text-muted-foreground tracking-wide border-b border-border mb-1">
                                            {{ t(section.labelKey) }}
                                        </div>
                                        
                                        <!-- Items -->
                                        <template v-for="item in filteredNavigation[section.key]" :key="item.name || item.label">
                                            <div v-if="item.type === 'divider'" class="py-2 px-3 flex items-center gap-2">
                                                <div class="h-px bg-border flex-1"></div>
                                                <span class="text-[10px] uppercase font-bold text-muted-foreground/30 tracking-widest whitespace-nowrap">{{ getNavigationLabel(item) }}</span>
                                                <div class="h-px bg-border flex-1"></div>
                                            </div>
                                            
                                            <!-- Sub-category Header in Popover -->
                                            <div v-else-if="item.children && item.children.length > 0" class="mt-2 first:mt-0">
                                                <div class="px-3 py-1 text-[10px] uppercase font-bold text-muted-foreground/50 tracking-widest">
                                                    {{ getNavigationLabel(item) }}
                                                </div>
                                                <router-link
                                                    v-for="subItem in item.children"
                                                    :key="subItem.name || subItem.label"
                                                    :to="subItem.to || ''"
                                                    @click="activePopup = null; $emit('close')"
                                                    class="flex items-center px-3 py-1.5 text-xs font-medium hover:bg-accent mx-1 rounded-lg"
                                                    :class="[
                                                        $route.name === subItem.name
                                                            ? 'text-foreground bg-accent'
                                                            : 'text-muted-foreground hover:text-accent-foreground'
                                                    ]"
                                                >
                                                    <component :is="getIcon(subItem.icon || subItem.name || '')" class="w-3.5 h-3.5 flex-shrink-0 mr-2" />
                                                    <span class="truncate">{{ getNavigationLabel(subItem) }}</span>
                                                </router-link>
                                            </div>

                                            <router-link
                                                v-else
                                                :to="item.to || ''"
                                                @click="activePopup = null; $emit('close')"
                                                class="flex items-center px-3 py-2 text-sm font-medium hover:bg-accent mx-1 rounded-xl"
                                                :class="[
                                                    $route.name === item.name
                                                        ? 'text-foreground bg-accent'
                                                        : 'text-muted-foreground hover:text-accent-foreground'
                                                ]"
                                            >
                                                <component :is="getIcon(item.icon || item.name || '')" class="w-4 h-4 flex-shrink-0 mr-2.5" />
                                                <span class="truncate">{{ getNavigationLabel(item) }}</span>
                                            </router-link>
                                        </template>
                                    </div>
                            </Teleport>
                        </div>
</div>
                </template>
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted, nextTick, type Component } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { navigationGroups, type NavItem } from '@/utils/navigation';
import { getIcon } from '@/utils/icons';
import { useAuthStore } from '@/stores/auth';
import { useCmsStore } from '@/stores/cms';
import AdminLogo from '@/components/layouts/AdminLogo.vue';
// Inline SVG icons from icons.ts - no lucide bundle needed
import { 
    Tooltip, 
    TooltipContent, 
    TooltipProvider, 
    TooltipTrigger 
} from '@/components/ui';
import type { User } from '@/types/auth';

interface SidebarSection {
    key: string;
    labelKey: string;
    icon: Component | string;
}

defineProps<{
    sidebarMinimized?: boolean;
    sidebarOpen?: boolean;
    user?: User | null;
}>();

defineEmits<{
    (e: 'toggle-minimize'): void;
    (e: 'close'): void;
    (e: 'logout'): void;
}>();

const { t, te } = useI18n();
const $route = useRoute();
const authStore = useAuthStore();
const cmsStore = useCmsStore();

const sidebarSections: SidebarSection[] = [
    { key: 'content', labelKey: 'common.navigation.sections.content', icon: getIcon('layers') },
    { key: 'marketing', labelKey: 'common.navigation.sections.marketing', icon: getIcon('megaphone') },
    { key: 'isp', labelKey: 'common.navigation.sections.isp', icon: getIcon('network') },
    { key: 'users', labelKey: 'common.navigation.sections.usersAccess', icon: getIcon('users') },
    { key: 'appearance', labelKey: 'common.navigation.sections.appearance', icon: getIcon('palette') },
    { key: 'logs', labelKey: 'common.navigation.sections.logs', icon: getIcon('activity') },
    { key: 'system', labelKey: 'common.navigation.sections.system', icon: getIcon('settings') },
    { key: 'developer', labelKey: 'common.navigation.sections.developer', icon: getIcon('code') },
];

const expandedSections = ref<Record<string, boolean>>({});
const expandedSubSections = ref<Record<string, boolean>>({});
const activePopup = ref<string | null>(null);
const popupTop = ref(0);
const popupLeft = ref(0);
const popups = ref<(HTMLElement | null)[]>([]);
let popupCloseTimeout: ReturnType<typeof setTimeout> | null = null;

const initializeExpandedSections = () => {
    if (sidebarSections.length > 0) {
        expandedSections.value[sidebarSections[0].key] = true;
    }
};

const isItemActive = (item: NavItem): boolean => {
    if (item.name === $route.name) return true;
    if (item.children && item.children.length > 0) {
        return item.children.some(child => child.name === $route.name);
    }
    return false;
};

const autoExpandActiveSection = () => {
    for (const section of sidebarSections) {
        const items = filteredNavigation.value[section.key] || [];
        
        let sectionHasActive = false;
        items.forEach(item => {
            if (isItemActive(item)) {
                sectionHasActive = true;
                // If it's a sub-section with children, expand it
                if (item.label && item.children && item.children.some(c => c.name === $route.name)) {
                    expandedSubSections.value[item.label] = true;
                }
            }
        });

        if (sectionHasActive) {
            expandedSections.value[section.key] = true;
        }
    }
};

const toggleSection = (key: string) => {
    const isCurrentlyExpanded = expandedSections.value[key];
    expandedSections.value = {};
    expandedSections.value[key] = !isCurrentlyExpanded;
};

const toggleSubSection = (key: string) => {
    expandedSubSections.value[key] = !expandedSubSections.value[key];
};

const isSubSectionActive = (item: NavItem) => {
    return isItemActive(item);
};

const closePopup = (key: string) => {
    if (activePopup.value === key) {
        activePopup.value = null;
    }
};

const scheduleClosePopup = (key: string) => {
    popupCloseTimeout = setTimeout(() => {
        closePopup(key);
    }, 150);
};

const openPopup = async (key: string, event: MouseEvent | null = null) => {
    if (popupCloseTimeout) {
        clearTimeout(popupCloseTimeout);
        popupCloseTimeout = null;
    }
    
    if (event) {
        const target = event.currentTarget as HTMLElement;
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

    if (popups.value && popups.value.length > 0) {
        const content = popups.value.find(el => el);
        if (content) {
            const rect = content.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const bottomEdge = rect.top + rect.height;
            
            if (bottomEdge > windowHeight - 20) {
                popupTop.value = Math.max(10, windowHeight - rect.height - 20);
            }
        }
    }
};

const togglePopup = (key: string, event: MouseEvent) => {
    if (activePopup.value === key) {
        activePopup.value = null;
    } else {
        openPopup(key, event);
    }
};

const isSectionActive = (key: string) => {
    const items = filteredNavigation.value[key] || [];
    return items.some(item => isItemActive(item));
};

const filteredNavigation = computed(() => {
    const filtered: Record<string, NavItem[]> = {};
    for (const [group, items] of Object.entries(navigationGroups)) {
        filtered[group] = items.filter(item => {
            if (!item.permission) return true;
            return authStore.hasPermission(item.permission);
        });
    }
    return filtered;
});

const getNavigationLabel = (item: NavItem) => {
    if (item.type === 'divider') {
        const key = `common.navigation.sections.${item.label}`;
        return te(key) ? t(key) : item.label || '';
    }
    
    // For nested groups that don't have a route name but have a label
    if (!item.name && item.label) {
        // Try to translate from navigation.sections first
        const sectionKey = `common.navigation.sections.${item.label.toLowerCase().replace(/\s+/g, '_')}`;
        if (te(sectionKey)) return t(sectionKey);
        return item.label;
    }

    if (!item.name) return item.label || '';
    
    // Try isp namespace first if it's an isp route
    if (item.name.startsWith('isp')) {
        const ispKey = item.name.replace(/-/g, '.');
        const fullIspKey = `isp.${ispKey}`;
        if (te(fullIspKey)) return t(fullIspKey);
    }

    const camelName = item.name.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
    const key = `common.navigation.menu.${camelName}`;
    return te(key) ? t(key) : item.label || '';
};

const getVisitTooltip = computed(() => {
    const siteUrl = cmsStore.siteSettings?.site_url || 'domain.com';
    let domain = siteUrl;
    try {
        const url = new URL(siteUrl.startsWith('http') ? siteUrl : `https://${siteUrl}`);
        domain = url.hostname;
    } catch (_e) {
        // fallback
    }
    return t('common.navigation.visit_site', { url: domain });
});

watch(expandedSections, (newVal) => {
    localStorage.setItem('sidebarExpandedSections', JSON.stringify(newVal));
}, { deep: true });

watch(() => $route.name, () => {
    autoExpandActiveSection();
});

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
    autoExpandActiveSection();
});
</script>
