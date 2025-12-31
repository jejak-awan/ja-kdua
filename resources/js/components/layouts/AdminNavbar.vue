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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
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
                        class="hidden md:flex items-center w-64 px-3 py-2 text-sm text-muted-foreground bg-muted/50 border border-input rounded-md hover:bg-muted hover:text-foreground transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>{{ t('common.actions.search') }}...</span>
                        <kbd class="ml-auto pointer-events-none inline-flex h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium text-muted-foreground opacity-100">
                            <span class="text-xs">⌘K</span>
                        </kbd>
                    </button>

                    <!-- Mobile Search Trigger -->
                    <button
                        @click="showGlobalSearch = true"
                        class="md:hidden p-2 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <!-- Global Search Component -->
                    <GlobalSearch 
                        v-model:isOpen="showGlobalSearch" 
                    />
                </div>
                
                <!-- Notifications -->
                <div class="relative">
                    <button
                        @click="toggleNotifications"
                        class="relative p-2 text-muted-foreground hover:text-foreground dark:hover:text-foreground"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
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
                        class="fixed inset-x-4 top-[64px] md:absolute md:inset-x-auto md:top-auto md:right-0 mt-2 md:w-80 bg-popover text-popover-foreground rounded-lg z-50 border border-border shadow-lg"
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
                                    :class="{ 'bg-blue-500/10': !notification.read_at }"
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

                <!-- Language Switcher -->
                <LanguageSwitcher />

                <!-- Dark Mode Toggle -->
                <DarkModeToggle />

                <!-- User Menu -->
                <div class="relative">
                    <button
                        @click="toggleUserMenu"
                        class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-accent transition-colors"
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
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div
                        v-if="showUserMenu"
                        class="absolute right-0 mt-2 w-56 bg-popover text-popover-foreground rounded-lg z-50 border border-border"
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
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ t('common.labels.myProfile') }}
                            </router-link>
                            <router-link
                                :to="{ name: 'settings' }"
                                @click="showUserMenu = false"
                                class="flex items-center px-4 py-2 text-sm text-foreground hover:bg-accent"
                            >
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ t('common.labels.settings') }}
                            </router-link>
                        </div>
                        <div class="border-t border-border">
                            <button
                                @click="handleLogout"
                                class="flex items-center w-full px-4 py-2 text-sm text-destructive hover:bg-destructive/10 hover:text-destructive"
                            >
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
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

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import Breadcrumbs from '../Breadcrumbs.vue';
import DarkModeToggle from '../DarkModeToggle.vue';
import LanguageSwitcher from '../LanguageSwitcher.vue';
import GlobalSearch from '../GlobalSearch.vue';

const router = useRouter();
const { t, te } = useI18n();
const props = defineProps({
    isAuthenticated: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['toggle-sidebar', 'logout']);

const showNotificationsDropdown = ref(false);
const showUserMenu = ref(false);
const notifications = ref([]);
const loadingNotifications = ref(false);
const notificationInterval = ref(null);
const avatarError = ref(false);

// Global Search State
const showGlobalSearch = ref(false);

const unreadNotificationsCount = computed(() => {
    if (!Array.isArray(notifications.value)) {
        return 0;
    }
    return notifications.value.filter(n => !n.read_at).length;
});

const recentNotifications = computed(() => {
    if (!Array.isArray(notifications.value)) {
        return [];
    }
    return notifications.value.slice(0, 5);
});

const userAvatar = computed(() => {
    if (!props.user?.avatar || avatarError.value) return null;

    const formatUrl = (path) => {
        if (!path) return null;
        if (path.startsWith('http') || path.startsWith('/storage/')) return path;
        // Remove leading slash if exists to avoid double slash
        return `/storage/${path.replace(/^\//, '')}`;
    };

    if (typeof props.user.avatar === 'string') {
        return formatUrl(props.user.avatar);
    }
    if (props.user.avatar?.url) {
        return formatUrl(props.user.avatar.url);
    }
    if (props.user.avatar?.path) {
        return formatUrl(props.user.avatar.path);
    }
    return null;
});

const userInitial = computed(() => {
    if (!props.user?.name) return 'U';
    return props.user.name.charAt(0).toUpperCase();
});

const fetchNotifications = async () => {
    if (!props.isAuthenticated || window.__isSessionTerminated) return;
    
    loadingNotifications.value = true;
    try {
        const response = await api.get('/admin/cms/notifications?limit=5');
        const data = response.data?.data || response.data || [];
        notifications.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
        notifications.value = [];
    } finally {
        loadingNotifications.value = false;
    }
};

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        try {
            await api.put(`/admin/cms/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }
    showNotificationsDropdown.value = false;
};

const formatNotificationDate = (date) => {
    if (!date) return '';
    const now = new Date();
    const notifDate = new Date(date);
    const diffMs = now - notifDate;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return t('common.messages.time.justNow');
    if (diffMins < 60) return t('common.messages.time.ago', { time: `${diffMins}m` });
    if (diffHours < 24) return t('common.messages.time.ago', { time: `${diffHours}h` });
    if (diffDays < 7) return t('common.messages.time.ago', { time: `${diffDays}d` });
    return notifDate.toLocaleDateString();
};

// Close all dropdowns helper
const closeAllDropdowns = () => {
    showNotificationsDropdown.value = false;
    showUserMenu.value = false;
};

// Dispatch event to close child component dropdowns (LanguageSwitcher, DarkModeToggle)
const closeChildDropdowns = () => {
    window.dispatchEvent(new CustomEvent('close-navbar-dropdowns'));
};

// Toggle functions that close other dropdowns first
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

const handleClickOutside = (event) => {
    if (showNotificationsDropdown.value && !event.target.closest('.relative')) {
        showNotificationsDropdown.value = false;
    }
    if (showUserMenu.value && !event.target.closest('.relative')) {
        showUserMenu.value = false;
    }
};

watch(() => props.isAuthenticated, (isAuth) => {
    if (isAuth) {
        fetchNotifications();
        notificationInterval.value = setInterval(fetchNotifications, 120000); // Increased from 30s to 2m
    } else {
        if (notificationInterval.value) {
            clearInterval(notificationInterval.value);
        }
    }
});

onMounted(() => {
    if (props.isAuthenticated) {
        fetchNotifications();
        notificationInterval.value = setInterval(fetchNotifications, 120000); // Increased from 30s to 2m
    }
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (notificationInterval.value) {
        clearInterval(notificationInterval.value);
    }
    document.removeEventListener('click', handleClickOutside);
});
</script>

