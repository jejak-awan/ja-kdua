<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-foreground">{{ $t('features.notifications.title') }}</h1>
            <div class="flex items-center space-x-2">
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 border border-input bg-card text-foreground rounded-md text-foreground hover:bg-muted text-sm"
                >
                    {{ $t('features.notifications.actions.markAllRead') }}
                </button>
            </div>
        </div>

        <div class="bg-card shadow rounded-lg">
            <div class="px-6 py-4 border-b border-border">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('features.notifications.filters.search')"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <select
                        v-model="typeFilter"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">{{ $t('features.notifications.filters.allTypes') }}</option>
                        <option value="info">{{ $t('features.notifications.filters.type.info') }}</option>
                        <option value="success">{{ $t('features.notifications.filters.type.success') }}</option>
                        <option value="warning">{{ $t('features.notifications.filters.type.warning') }}</option>
                        <option value="error">{{ $t('features.notifications.filters.type.error') }}</option>
                    </select>
                    <select
                        v-model="readFilter"
                        class="px-4 py-2 border border-input bg-card text-foreground rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">{{ $t('features.notifications.filters.readStatus.all') }}</option>
                        <option value="unread">{{ $t('features.notifications.filters.readStatus.unread') }}</option>
                        <option value="read">{{ $t('features.notifications.filters.readStatus.read') }}</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-muted-foreground">{{ $t('features.notifications.messages.loading') }}</p>
            </div>

            <div v-else-if="filteredNotifications.length === 0" class="p-6 text-center">
                <p class="text-muted-foreground">{{ $t('features.notifications.messages.empty') }}</p>
            </div>

            <div v-else class="divide-y divide-border">
                <div
                    v-for="notification in filteredNotifications"
                    :key="notification.id"
                    class="px-6 py-4 hover:bg-muted"
                    :class="{ 'bg-blue-50': !notification.read_at }"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span
                                    v-if="!notification.read_at"
                                    class="h-2 w-2 bg-blue-600 rounded-full"
                                />
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-blue-500/20 text-blue-400': notification.type === 'info',
                                        'bg-green-500/20 text-green-400': notification.type === 'success',
                                        'bg-yellow-500/20 text-yellow-400': notification.type === 'warning',
                                        'bg-red-500/20 text-red-400': notification.type === 'error',
                                    }"
                                >
                                    {{ $t(`features.notifications.filters.type.${notification.type}`) }}
                                </span>
                                <h3 class="text-sm font-medium text-foreground">{{ notification.title }}</h3>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">{{ notification.message }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ formatDate(notification.created_at) }}</p>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                v-if="!notification.read_at"
                                @click="markAsRead(notification)"
                                class="text-sm text-indigo-600 hover:text-indigo-900"
                            >
                                {{ $t('features.notifications.actions.markRead') }}
                            </button>
                            <button
                                @click="deleteNotification(notification)"
                                class="text-sm text-red-600 hover:text-red-900"
                            >
                                {{ $t('features.notifications.actions.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../../../services/api';

const { t } = useI18n();

const notifications = ref([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('');
const readFilter = ref('');

const unreadCount = computed(() => {
    if (!Array.isArray(notifications.value)) {
        return 0;
    }
    return notifications.value.filter(n => !n.read_at).length;
});

const filteredNotifications = computed(() => {
    if (!Array.isArray(notifications.value)) {
        return [];
    }
    
    let filtered = notifications.value;
    
    if (typeFilter.value) {
        filtered = filtered.filter(n => n?.type === typeFilter.value);
    }
    
    if (readFilter.value === 'read') {
        filtered = filtered.filter(n => n?.read_at);
    } else if (readFilter.value === 'unread') {
        filtered = filtered.filter(n => !n?.read_at);
    }
    
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(n => 
            n?.title?.toLowerCase().includes(searchLower) ||
            n?.message?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/notifications');
        // Handle different response structures
        let data = [];
        if (response.data?.data && Array.isArray(response.data.data)) {
            data = response.data.data;
        } else if (Array.isArray(response.data)) {
            data = response.data;
        } else if (response.data?.items && Array.isArray(response.data.items)) {
            data = response.data.items;
        }
        notifications.value = data;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
        notifications.value = [];
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (notification) => {
    try {
        await api.post(`/admin/cms/notifications/${notification.id}/read`);
        notification.read_at = new Date().toISOString();
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await api.post('/admin/cms/notifications/read-all');
        await fetchNotifications();
    } catch (error) {
        console.error('Failed to mark all as read:', error);
    }
};

const deleteNotification = async (notification) => {
    if (!confirm(t('features.notifications.messages.deleteConfirm'))) {
        return;
    }

    try {
        await api.delete(`/admin/cms/notifications/${notification.id}`);
        await fetchNotifications();
    } catch (error) {
        console.error('Failed to delete notification:', error);
        alert(t('features.notifications.messages.deleteFailed'));
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

onMounted(() => {
    fetchNotifications();
    // Poll for new notifications every 30 seconds
    setInterval(fetchNotifications, 30000);
});
</script>

