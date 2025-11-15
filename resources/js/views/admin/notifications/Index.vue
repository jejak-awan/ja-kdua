<template>
    <div>
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <div class="flex items-center space-x-2">
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm"
                >
                    Mark All as Read
                </button>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center space-x-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search notifications..."
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <select
                        v-model="typeFilter"
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All Types</option>
                        <option value="info">Info</option>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                    </select>
                    <select
                        v-model="readFilter"
                        class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="p-6 text-center">
                <p class="text-gray-500">Loading...</p>
            </div>

            <div v-else-if="filteredNotifications.length === 0" class="p-6 text-center">
                <p class="text-gray-500">No notifications found</p>
            </div>

            <div v-else class="divide-y divide-gray-200">
                <div
                    v-for="notification in filteredNotifications"
                    :key="notification.id"
                    class="px-6 py-4 hover:bg-gray-50"
                    :class="{ 'bg-blue-50': !notification.read_at }"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span
                                    v-if="!notification.read_at"
                                    class="h-2 w-2 bg-blue-600 rounded-full"
                                ></span>
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    :class="{
                                        'bg-blue-100 text-blue-800': notification.type === 'info',
                                        'bg-green-100 text-green-800': notification.type === 'success',
                                        'bg-yellow-100 text-yellow-800': notification.type === 'warning',
                                        'bg-red-100 text-red-800': notification.type === 'error',
                                    }"
                                >
                                    {{ notification.type }}
                                </span>
                                <h3 class="text-sm font-medium text-gray-900">{{ notification.title }}</h3>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">{{ notification.message }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ formatDate(notification.created_at) }}</p>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                v-if="!notification.read_at"
                                @click="markAsRead(notification)"
                                class="text-sm text-indigo-600 hover:text-indigo-900"
                            >
                                Mark as Read
                            </button>
                            <button
                                @click="deleteNotification(notification)"
                                class="text-sm text-red-600 hover:text-red-900"
                            >
                                Delete
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
import api from '../../../services/api';

const notifications = ref([]);
const loading = ref(false);
const search = ref('');
const typeFilter = ref('');
const readFilter = ref('');

const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const filteredNotifications = computed(() => {
    let filtered = notifications.value;
    
    if (typeFilter.value) {
        filtered = filtered.filter(n => n.type === typeFilter.value);
    }
    
    if (readFilter.value === 'read') {
        filtered = filtered.filter(n => n.read_at);
    } else if (readFilter.value === 'unread') {
        filtered = filtered.filter(n => !n.read_at);
    }
    
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(n => 
            n.title?.toLowerCase().includes(searchLower) ||
            n.message?.toLowerCase().includes(searchLower)
        );
    }
    
    return filtered;
});

const fetchNotifications = async () => {
    loading.value = true;
    try {
        const response = await api.get('/admin/cms/notifications');
        notifications.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
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
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }

    try {
        await api.delete(`/admin/cms/notifications/${notification.id}`);
        await fetchNotifications();
    } catch (error) {
        console.error('Failed to delete notification:', error);
        alert('Failed to delete notification');
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

