<template>
    <header class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between h-16 px-6">
            <button
                @click="$emit('toggle-sidebar')"
                class="lg:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="flex items-center space-x-4">
                <!-- Search -->
                <div class="relative">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            @focus="showSearchResults = true"
                            @input="handleSearch"
                            type="text"
                            placeholder="Search..."
                            class="w-64 pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400"
                        >
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    
                    <!-- Search Results Dropdown -->
                    <div
                        v-if="showSearchResults && searchQuery"
                        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50 border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto"
                        @click.stop
                    >
                        <div v-if="searching" class="p-4 text-center text-sm text-gray-500">
                            Searching...
                        </div>
                        <div v-else-if="searchResults.length === 0" class="p-4 text-center text-sm text-gray-500">
                            No results found
                        </div>
                        <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div
                                v-for="result in searchResults"
                                :key="`${result.type}-${result.id}`"
                                @click="handleSearchResultClick(result)"
                                class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                            >
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <span class="px-2 py-1 text-xs font-semibold rounded" :class="getResultTypeClass(result.type)">
                                            {{ result.type }}
                                        </span>
                                    </div>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ result.title }}</p>
                                        <p v-if="result.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ result.description }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-2 text-center">
                                <router-link
                                    :to="{ name: 'search', query: { q: searchQuery } }"
                                    @click="showSearchResults = false"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                >
                                    View all results
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications -->
                <div class="relative">
                    <button
                        @click="showNotificationsDropdown = !showNotificationsDropdown"
                        class="relative p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span
                            v-if="unreadNotificationsCount > 0"
                            class="absolute top-0 right-0 block h-5 w-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center"
                        >
                            {{ unreadNotificationsCount > 99 ? '99+' : unreadNotificationsCount }}
                        </span>
                    </button>
                    
                    <!-- Notifications Dropdown -->
                    <div
                        v-if="showNotificationsDropdown"
                        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-50 border border-gray-200 dark:border-gray-700"
                        @click.stop
                    >
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                            <router-link
                                :to="{ name: 'notifications' }"
                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                @click="showNotificationsDropdown = false"
                            >
                                View All
                            </router-link>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div v-if="loadingNotifications" class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                Loading...
                            </div>
                            <div v-else-if="recentNotifications.length === 0" class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No notifications
                            </div>
                            <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                                <div
                                    v-for="notification in recentNotifications"
                                    :key="notification.id"
                                    class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                    :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.read_at }"
                                    @click="handleNotificationClick(notification)"
                                >
                                    <div class="flex items-start">
                                        <span
                                            v-if="!notification.read_at"
                                            class="h-2 w-2 bg-blue-600 rounded-full mt-2 mr-2"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ notification.title }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ notification.message }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ formatNotificationDate(notification.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const router = useRouter();
const props = defineProps({
    isAuthenticated: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle-sidebar']);

const showNotificationsDropdown = ref(false);
const notifications = ref([]);
const loadingNotifications = ref(false);
const notificationInterval = ref(null);
const searchQuery = ref('');
const showSearchResults = ref(false);
const searchResults = ref([]);
const searching = ref(false);
const searchTimeout = ref(null);

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

const fetchNotifications = async () => {
    if (!props.isAuthenticated) return;
    
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
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
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
        content: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        category: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        user: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        media: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        page: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
    };
    return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};

const handleClickOutside = (event) => {
    if (showNotificationsDropdown.value && !event.target.closest('.relative')) {
        showNotificationsDropdown.value = false;
    }
    if (showSearchResults.value && !event.target.closest('.relative')) {
        showSearchResults.value = false;
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

