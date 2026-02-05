<template>
    <header class="sticky top-0 z-40 bg-card border-b border-border">
        <div class="flex items-center justify-between h-16 px-6">
            <!-- Left Group: Mobile Toggle + Breadcrumb -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle -->
                <button
                    @click="$emit('toggle-sidebar')"
                    class="lg:hidden text-muted-foreground hover:text-foreground"
                >
                    <Menu class="w-6 h-6" />
                </button>

                <!-- Breadcrumb (Desktop Only) -->
                <Breadcrumbs compact class="hidden lg:flex" />
            </div>

            <!-- Right: Search, Notifications, User Menu -->
            <div class="flex items-center space-x-4 ml-auto">
                <!-- Search -->
                <div class="relative">
                    <!-- Global Search Trigger -->
                    <button
                        @click="showGlobalSearch = true"
                        data-slot="search-trigger"
                        class="hidden md:flex items-center w-64 px-3 py-2 text-sm text-muted-foreground bg-transparent border border-border/40 rounded-lg hover:bg-muted/5 hover:text-foreground"
                    >
                        <Search class="mr-2 w-4 h-4 opacity-50" />
                        <span>{{ t('common.actions.search') }}...</span>
                        <kbd class="ml-auto pointer-events-none inline-flex h-5 select-none items-center gap-1 rounded-md border-border/40 bg-background px-1.5 font-mono text-[10px] font-medium text-muted-foreground opacity-100 uppercase">
                            <span class="text-[10px]">Ctrl</span>
                            <span class="text-[10px]">K</span>
                        </kbd>
                    </button>

                    <!-- Mobile Search Trigger -->
                    <button
                        @click="showGlobalSearch = true"
                        class="md:hidden p-2 text-muted-foreground hover:text-foreground rounded-xl hover:bg-accent"
                    >
                        <Search class="w-5 h-5" />
                    </button>

                    <!-- Global Search Component -->
                    <GlobalSearch 
                        v-model:is-open="showGlobalSearch" 
                    />
                </div>
                
                <!-- Notifications -->
                <div class="relative">
                    <button
                        @click="toggleNotifications"
                        class="relative p-2 text-muted-foreground hover:text-foreground dark:hover:text-foreground"
                    >
                        <Bell class="w-6 h-6" />
                        <span
                            v-if="unreadNotificationsCount > 0"
                            class="absolute top-0 right-0 block h-5 w-5 rounded-full bg-destructive text-destructive-foreground text-xs flex items-center justify-center"
                        >
                            {{ unreadNotificationsCount > 99 ? '99+' : unreadNotificationsCount }}
                        </span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div
                        v-if="showNotificationsDropdown"
                        class="fixed inset-x-4 top-[64px] md:absolute md:inset-x-auto md:top-auto md:right-0 mt-2 md:w-80 bg-popover text-popover-foreground rounded-lg z-50 border border-border"
                        @click.stop
                    >
                        <div class="p-4 border-b border-border flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-foreground">{{ t('common.labels.notifications') }}</h3>
                            <router-link
                                :to="{ name: 'notifications' }"
                                class="text-xs text-primary hover:text-primary/80"
                                @click="showNotificationsDropdown = false"
                            >
                                {{ t('common.actions.viewAll') }}
                            </router-link>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div v-if="loadingNotifications" class="p-4 text-center text-sm text-muted-foreground">
                                {{ t('common.messages.loading.default') }}
                            </div>
                            <div v-else-if="recentNotifications.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                                {{ t('common.messages.empty.default') }}
                            </div>
                            <div v-else class="divide-y divide-border">
                                <div
                                    v-for="notification in recentNotifications"
                                    :key="notification.id"
                                    class="p-4 hover:bg-muted cursor-pointer"
                                    :class="{ 'bg-primary/10': !notification.read_at }"
                                    @click="handleNotificationClick(notification)"
                                >
                                    <div class="flex items-start">
                                        <span
                                            v-if="!notification.read_at"
                                            class="h-2 w-2 bg-primary rounded-full mt-2 mr-2"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground">{{ notification.title }}</p>
                                            <p class="text-xs text-muted-foreground mt-1 truncate">{{ notification.message }}</p>
                                            <p class="text-xs text-muted-foreground mt-1">{{ formatNotificationDate(notification.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Dark Mode Toggle -->
                <DarkModeToggle />

                <!-- User Menu -->
                <div class="relative">
                    <button
                        @click="toggleUserMenu"
                        class="flex items-center space-x-3 px-3 py-2 rounded-xl hover:bg-accent"
                    >
                        <img
                            v-if="userAvatar"
                            :src="userAvatar"
                            :alt="user?.name"
                            class="w-8 h-8 rounded-full object-cover"
                            @error="avatarError = true"
                        >
                        <div
                            v-else
                            class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-foreground font-semibold text-sm"
                        >
                            {{ userInitial }}
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-foreground">{{ user?.name || t('common.labels.user') }}</p>
                            <p class="text-xs text-muted-foreground">{{ user?.email || '' }}</p>
                        </div>
                        <ChevronDown class="w-4 h-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': showUserMenu }" />
                    </button>

                    <!-- User Dropdown -->
                    <div
                        v-if="showUserMenu"
                        class="absolute right-0 mt-2 w-56 bg-popover text-foreground rounded-xl z-50 border border-border/60 shadow-lg"
                        @click.stop
                    >
                        <div class="p-3 border-b border-border">
                            <p class="text-sm font-semibold text-foreground">{{ user?.name || t('common.labels.user') }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ user?.email || '' }}</p>
                        </div>

        <div class="py-1">
                            <router-link
                                :to="{ name: 'profile' }"
                                @click="showUserMenu = false"
                                class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-accent"
                            >
                                <UserIcon class="w-4 h-4 mr-3" />
                                {{ t('common.labels.myProfile') }}
                            </router-link>
                            <router-link
                                v-if="authStore.hasPermission('manage settings')"
                                :to="{ name: 'settings' }"
                                @click="showUserMenu = false"
                                class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-accent"
                            >
                                <Settings class="w-4 h-4 mr-3" />
                                {{ t('common.labels.settings') }}
                            </router-link>
                        </div>
                        
                        <!-- Language Selection -->
                        <div class="p-1 border-t border-border">
                            <button
                                v-for="lang in languages"
                                :key="lang.id"
                                @click="selectLanguage(lang)"
                                class="flex items-center w-full px-4 py-2 text-sm hover:bg-accent"
                                :class="currentLanguage?.code === lang.code ? 'text-primary font-medium' : 'text-foreground'"
                            >
                                <span class="mr-3 text-base leading-none">{{ getLanguageFlag(lang) }}</span>
                                <span class="flex-1 text-left">{{ lang.native_name }}</span>
                                <Check v-if="currentLanguage?.code === lang.code" class="ml-auto w-4 h-4 text-primary" />
                            </button>
                        </div>
                        <div class="border-t border-border">
                            <button
                                @click="handleLogout"
                                class="flex items-center w-full px-4 py-2 text-sm text-destructive hover:bg-destructive/10 hover:text-destructive"
                            >
                                <LogOut class="w-4 h-4 mr-3" />
                                {{ t('common.navigation.menu.logout') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Breadcrumb (below navbar, full width) -->
        <div class="lg:hidden border-t border-border px-6 py-2">
            <Breadcrumbs compact />
        </div>
    </header>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import { useLanguage, type Language } from '@/composables/useLanguage';
import api from '@/services/api';
import Breadcrumbs from '@/components/layout/Breadcrumbs.vue';
import DarkModeToggle from '@/components/shared/DarkModeToggle.vue';
import GlobalSearch from '@/components/shared/GlobalSearch.vue';

import Menu from 'lucide-vue-next/dist/esm/icons/menu.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Bell from 'lucide-vue-next/dist/esm/icons/bell.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import UserIcon from 'lucide-vue-next/dist/esm/icons/user.js';
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import LogOut from 'lucide-vue-next/dist/esm/icons/log-out.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';

import type { User } from '@/types/auth';

interface Notification {
    id: number;
    title: string;
    message: string;
    read_at: string | null;
    created_at: string;
    [key: string]: unknown;
}

// router is unused here but kept if needed for future navigation
// const router = useRouter();
const authStore = useAuthStore();
const { t } = useI18n();

// Language Composable
const {
    currentLanguage,
    languages,
    setLanguage,
    getLanguageFlag,
    initializeLanguage,
} = useLanguage();

const props = withDefaults(defineProps<{
    isAuthenticated?: boolean;
    user?: User | null;
}>(), {
    isAuthenticated: false,
    user: null,
});

const emit = defineEmits<{
    (e: 'toggle-sidebar'): void;
    (e: 'logout'): void;
}>();

const showNotificationsDropdown = ref(false);
const showUserMenu = ref(false);
const notifications = ref<Notification[]>([]);
const loadingNotifications = ref(false);
const notificationInterval = ref<ReturnType<typeof setInterval> | null>(null);
const avatarError = ref(false);
const showGlobalSearch = ref(false);

const selectLanguage = async (lang: Language) => {
    await setLanguage(lang.code);
    showUserMenu.value = false;
};

const unreadNotificationsCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const recentNotifications = computed(() => {
    return notifications.value.slice(0, 5);
});

const userAvatar = computed(() => {
    if (!props.user?.avatar || avatarError.value) return null;

    const formatUrl = (path: unknown) => {
        if (!path || typeof path !== 'string') return null;
        if (path.startsWith('http') || path.startsWith('/storage/')) return path;
        return `/storage/${path.replace(/^\//, '')}`;
    };

    const avatar = props.user.avatar;
    if (typeof avatar === 'string') return formatUrl(avatar);
    if (typeof avatar === 'object') {
        return formatUrl(avatar.url || avatar.path);
    }
    return null;
});

const userInitial = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name.charAt(0).toUpperCase();
});

const fetchNotifications = async () => {
    if (!props.isAuthenticated || (window as unknown as { __isSessionTerminated?: boolean }).__isSessionTerminated) return;
    
    loadingNotifications.value = true;
    try {
        const response = await api.get('/admin/ja/notifications?limit=5');
        
        let data: Notification[] = [];
        if (response.data?.data?.data && Array.isArray(response.data.data.data)) {
            data = response.data.data.data;
        } else if (response.data?.data && Array.isArray(response.data.data)) {
            data = response.data.data;
        } else if (Array.isArray(response.data)) {
            data = response.data;
        }
        
        notifications.value = data;
    } catch (error) {
        logger.error('Failed to fetch notifications:', error);
        notifications.value = [];
    } finally {
        loadingNotifications.value = false;
    }
};

const handleNotificationClick = async (notification: Notification) => {
    if (!notification.read_at) {
        try {
            await api.put(`/admin/ja/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
        } catch (error) {
            logger.error('Failed to mark notification as read:', error);
        }
    }
    showNotificationsDropdown.value = false;
};

const formatNotificationDate = (date: string) => {
    if (!date) return '';
    const now = new Date();
    const notifDate = new Date(date);
    const diffMs = now.getTime() - notifDate.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return t('common.messages.time.justNow');
    if (diffMins < 60) return t('common.messages.time.ago', { time: `${diffMins}m` });
    if (diffHours < 24) return t('common.messages.time.ago', { time: `${diffHours}h` });
    if (diffDays < 7) return t('common.messages.time.ago', { time: `${diffDays}d` });
    return notifDate.toLocaleDateString();
};

const closeAllDropdowns = () => {
    showNotificationsDropdown.value = false;
    showUserMenu.value = false;
};

const closeChildDropdowns = () => {
    window.dispatchEvent(new CustomEvent('close-navbar-dropdowns'));
};

const toggleNotifications = () => {
    const wasOpen = showNotificationsDropdown.value;
    closeAllDropdowns();
    closeChildDropdowns();
    showNotificationsDropdown.value = !wasOpen;
};

const toggleUserMenu = () => {
    const wasOpen = showUserMenu.value;
    closeAllDropdowns();
    closeChildDropdowns();
    showUserMenu.value = !wasOpen;
};

const handleLogout = () => {
    showUserMenu.value = false;
    emit('logout');
};

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (showNotificationsDropdown.value && !target.closest('.relative')) {
        showNotificationsDropdown.value = false;
    }
    if (showUserMenu.value && !target.closest('.relative')) {
        showUserMenu.value = false;
    }
};

watch(() => props.isAuthenticated, (isAuth) => {
    if (isAuth) {
        fetchNotifications();
        notificationInterval.value = setInterval(fetchNotifications, 120000);
    } else {
        if (notificationInterval.value) {
            clearInterval(notificationInterval.value);
            notificationInterval.value = null;
        }
    }
});

onMounted(() => {
    if (props.isAuthenticated) {
        fetchNotifications();
        // Poll faster (every 30s) instead of 2 minutes
        notificationInterval.value = setInterval(fetchNotifications, 30000);
    }
    initializeLanguage();
    document.addEventListener('click', handleClickOutside);
    // Listen for manual triggers from other components
    window.addEventListener('notification:sent', fetchNotifications);
});

onUnmounted(() => {
    if (notificationInterval.value) {
        clearInterval(notificationInterval.value);
    }
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('notification:sent', fetchNotifications);
});
</script>

