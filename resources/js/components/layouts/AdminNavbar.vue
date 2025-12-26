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
                    <!-- Mobile Search Button -->
                    <button
                        @click="toggleMobileSearch"
                        class="md:hidden p-2 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <!-- Desktop Search Input -->
                    <div class="hidden md:block relative">
                        <input
                            v-model="searchQuery"
                            @focus="showSearchResults = true"
                            @input="handleSearch"
                            type="text"
                            :placeholder="t('common.actions.search') + '...'"
                            class="w-64 pl-10 pr-4 py-2 border border-input rounded-md bg-input text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Mobile Search Dropdown -->
                    <div
                        v-if="showMobileSearch"
                        class="md:hidden absolute left-0 mt-2 w-72 bg-popover text-popover-foreground rounded-lg z-50 border border-border p-3"
                        @click.stop
                    >
                        <div class="relative">
                            <input
                                ref="mobileSearchInput"
                                v-model="searchQuery"
                                @input="handleSearch"
                                type="text"
                                :placeholder="t('common.actions.search') + '...'"
                                class="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-input text-foreground placeholder-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                            >
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <!-- Mobile Search Results -->
                        <div v-if="searchQuery" class="mt-2 max-h-64 overflow-y-auto">
                            <div v-if="searching" class="p-3 text-center text-sm text-muted-foreground">
                                {{ t('common.messages.loading.searching') }}
                            </div>
                            <div v-else-if="searchResults.length === 0" class="p-3 text-center text-sm text-muted-foreground">
                                {{ t('common.messages.empty.search', { query: searchQuery }) }}
                            </div>
                            <div v-else class="divide-y divide-border">
                                <div
                                    v-for="result in searchResults"
                                    :key="`${result.type}-${result.id}`"
                                    @click="handleSearchResultClick(result); showMobileSearch = false"
                                    class="p-3 hover:bg-muted cursor-pointer rounded-md"
                                >
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <span class="px-2 py-1 text-xs font-semibold rounded" :class="getResultTypeClass(result.type)">
                                                {{ getResultLabel(result.type) }}
                                            </span>
                                        </div>
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground">{{ result.title }}</p>
                                            <p v-if="result.description" class="text-xs text-muted-foreground mt-1 truncate">{{ result.description }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-2 text-center">
                                    <router-link
                                        :to="{ name: 'search', query: { q: searchQuery } }"
                                        @click="showMobileSearch = false"
                                        class="text-xs text-primary hover:text-primary/80"
                                    >
                                        {{ t('common.actions.viewAll') }}
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desktop Search Results Dropdown -->
                    <div
                        v-if="showSearchResults && searchQuery && !showMobileSearch"
                        class="hidden md:block absolute right-0 mt-2 w-96 bg-popover text-popover-foreground rounded-lg z-50 border border-border max-h-96 overflow-y-auto"
                        @click.stop
                    >
                        <div v-if="searching" class="p-4 text-center text-sm text-muted-foreground">
                            {{ t('common.messages.loading.searching') }}
                        </div>
                        <div v-else-if="searchResults.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                            {{ t('common.messages.empty.search', { query: searchQuery }) }}
                        </div>
                        <div v-else class="divide-y divide-border">
                            <div
                                v-for="result in searchResults"
                                :key="`${result.type}-${result.id}`"
                                @click="handleSearchResultClick(result)"
                                class="p-4 hover:bg-muted cursor-pointer"
                            >
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <span class="px-2 py-1 text-xs font-semibold rounded" :class="getResultTypeClass(result.type)">
                                            {{ getResultLabel(result.type) }}
                                        </span>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-foreground">{{ result.title }}</p>
                                        <p v-if="result.description" class="text-xs text-muted-foreground mt-1 truncate">{{ result.description }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2 text-center">
                                <router-link
                                    :to="{ name: 'search', query: { q: searchQuery } }"
                                    @click="showSearchResults = false"
                                    class="text-xs text-primary hover:text-primary/80"
                                >
                                    {{ t('common.actions.viewAll') }}
                                </router-link>
                            </div>
                        </div>
                    </div>
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
const searchQuery = ref('');
const showSearchResults = ref(false);
const searchResults = ref([]);
const searching = ref(false);
const searchTimeout = ref(null);
const avatarError = ref(false);
const showMobileSearch = ref(false);
const mobileSearchInput = ref(null);

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

const handleSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    
    if (!searchQuery.value || searchQuery.value.length < 2) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }
    
    searchTimeout.value = setTimeout(async () => {
        searching.value = true;
        try {
            const response = await api.get('/admin/cms/search', {
                params: { q: searchQuery.value, limit: 5 },
            });
            const data = response.data?.data || response.data || [];
            searchResults.value = Array.isArray(data) ? data : [];
            showSearchResults.value = true;
        } catch (error) {
            console.error('Failed to search:', error);
            searchResults.value = [];
        } finally {
            searching.value = false;
        }
    }, 300);
};

const handleSearchResultClick = (result) => {
    showSearchResults.value = false;
    searchQuery.value = '';
    
    if (!result || !result.id) {
        return;
    }
    
    if (result.type === 'content' && result.id) {
        router.push({ name: 'contents.edit', params: { id: result.id } });
    } else if (result.type === 'category' && result.id) {
        router.push({ name: 'categories.edit', params: { id: result.id } });
    } else if (result.type === 'user' && result.id) {
        router.push({ name: 'users.edit', params: { id: result.id } });
    } else if (result.url) {
        router.push(result.url);
    }
};

const getResultTypeClass = (type) => {
    const classes = {
        content: 'bg-muted text-foreground',
        category: 'bg-muted text-foreground',
        user: 'bg-muted text-foreground',
        media: 'bg-muted text-foreground',
        page: 'bg-muted text-foreground',
    };
    return classes[type] || 'bg-secondary text-secondary-foreground';
};

const getResultLabel = (type) => {
    const key = `common.labels.${type?.toLowerCase()}`;
    return te(key) ? t(key) : type;
};

// Close all dropdowns helper
const closeAllDropdowns = () => {
    showNotificationsDropdown.value = false;
    showUserMenu.value = false;
    showMobileSearch.value = false;
    showSearchResults.value = false;
};

// Dispatch event to close child component dropdowns (LanguageSwitcher, DarkModeToggle)
const closeChildDropdowns = () => {
    window.dispatchEvent(new CustomEvent('close-navbar-dropdowns'));
};

// Toggle functions that close other dropdowns first
const toggleMobileSearch = () => {
    const wasOpen = showMobileSearch.value;
    closeAllDropdowns();
    closeChildDropdowns();
    showMobileSearch.value = !wasOpen;
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

const handleClickOutside = (event) => {
    if (showNotificationsDropdown.value && !event.target.closest('.relative')) {
        showNotificationsDropdown.value = false;
    }
    if (showSearchResults.value && !event.target.closest('.relative')) {
        showSearchResults.value = false;
    }
    if (showMobileSearch.value && !event.target.closest('.relative')) {
        showMobileSearch.value = false;
    }
    if (showUserMenu.value && !event.target.closest('.relative')) {
        showUserMenu.value = false;
    }
};

watch(() => props.isAuthenticated, (isAuth) => {
    if (isAuth) {
        fetchNotifications();
        notificationInterval.value = setInterval(fetchNotifications, 30000);
    } else {
        if (notificationInterval.value) {
            clearInterval(notificationInterval.value);
        }
    }
});

onMounted(() => {
    if (props.isAuthenticated) {
        fetchNotifications();
        notificationInterval.value = setInterval(fetchNotifications, 30000);
    }
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (notificationInterval.value) {
        clearInterval(notificationInterval.value);
    }
    document.removeEventListener('click', handleClickOutside);
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
});
</script>

