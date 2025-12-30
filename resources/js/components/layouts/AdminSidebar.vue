<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 bg-sidebar text-sidebar-foreground border-r border-border shadow-sm',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarMinimized ? 'w-20' : 'w-64'
        ]"
    >
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-border">
                <div v-if="!sidebarMinimized" class="flex items-center">
                    <h1 class="text-xl font-bold">JA CMS</h1>
                </div>
                <div v-else class="flex items-center justify-center w-full">
                    <span class="text-xl font-bold">J</span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="$emit('toggle-minimize')"
                        class="hidden lg:flex p-2 text-muted-foreground hover:text-accent-foreground hover:bg-accent rounded transition-colors"
                        :title="sidebarMinimized ? t('common.navigation.sidebar.expand') : t('common.navigation.sidebar.minimize')"
                    >
                        <svg v-if="!sidebarMinimized" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                        </svg>
                    </button>
                    <button
                        @click="$emit('close')"
                        class="lg:hidden text-muted-foreground hover:text-accent-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-2 py-6 space-y-6 overflow-y-auto">
                <!-- Dashboard -->
                <div>
                    <router-link
                        :to="'/admin'"
                        class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                        :class="[
                            $route.name === 'dashboard'
                                ? 'bg-accent text-foreground'
                                : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                        ]"
                        :title="sidebarMinimized ? t('common.navigation.menu.dashboard') : ''"
                    >
                        <component :is="getIcon('dashboard')" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                        <span v-if="!sidebarMinimized" class="truncate">{{ t('common.navigation.menu.dashboard') }}</span>
                    </router-link>
                </div>

                <!-- Content -->
                <div v-if="filteredNavigation.content.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.content') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.content"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Media -->
                <div v-if="filteredNavigation.media.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.media') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.media"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Engagement -->
                <div v-if="filteredNavigation.engagement.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.engagement') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.engagement"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Users & Access -->
                <div v-if="filteredNavigation.users.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.usersAccess') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.users"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Appearance -->
                <div v-if="filteredNavigation.appearance.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.appearance') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.appearance"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Analytics & SEO -->
                <div v-if="filteredNavigation.analytics.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.analyticsSeo') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.analytics"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Logs & Monitoring -->
                <div v-if="filteredNavigation.logs.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.logs') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.logs"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- System -->
                <div v-if="filteredNavigation.system.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.system') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.system"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Developer -->
                <div v-if="filteredNavigation.developer.length > 0">
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-muted-foreground tracking-wider mb-2">
                        {{ t('common.navigation.sections.developer') }}
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in filteredNavigation.developer"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-accent text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            ]"
                            :title="sidebarMinimized ? getNavigationLabel(item) : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ getNavigationLabel(item) }}</span>
                        </router-link>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useLayoutMount } from '../../composables/useLayoutMount';
import { navigationGroups } from '../../utils/navigation';
import { getIcon } from '../../utils/icons';
import { useAuthStore } from '../../stores/auth';

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
const avatarError = ref(false);
const authStore = useAuthStore();

// Use shared mounted state for synchronized transitions
const { mounted } = useLayoutMount();

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

const userAvatar = computed(() => {
    if (!props.user?.avatar) return null;
    if (typeof props.user.avatar === 'string') {
        return props.user.avatar.startsWith('http') ? props.user.avatar : `/storage/${props.user.avatar}`;
    }
    if (props.user.avatar?.url) {
        return props.user.avatar.url.startsWith('http') ? props.user.avatar.url : `/storage/${props.user.avatar.url}`;
    }
    if (props.user.avatar?.path) {
        return props.user.avatar.path.startsWith('http') ? props.user.avatar.path : `/storage/${props.user.avatar.path}`;
    }
    return null;
});

const userInitial = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name.charAt(0).toUpperCase();
});
</script>

