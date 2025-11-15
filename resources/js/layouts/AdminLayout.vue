<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white transform transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-6 border-b border-gray-800">
                    <h1 class="text-xl font-bold">JA CMS</h1>
                    <button
                        @click="sidebarOpen = false"
                        class="lg:hidden text-gray-400 hover:text-white"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto">
                    <!-- Dashboard -->
                    <div>
                        <router-link
                            :to="'/admin'"
                            class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                            :class="[
                                $route.name === 'dashboard'
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                        >
                            <component :is="getIcon('dashboard')" class="w-5 h-5 mr-3" />
                            Dashboard
                        </router-link>
                    </div>

                    <!-- Content Management -->
                    <div>
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            Content
                        </h3>
                        <div class="space-y-1">
                            <router-link
                                v-for="item in navigationGroups.content"
                                :key="item.name"
                                :to="item.to"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                                :class="[
                                    $route.name === item.name
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                ]"
                            >
                                <component :is="getIcon(item.name)" class="w-5 h-5 mr-3" />
                                {{ item.label }}
                            </router-link>
                        </div>
                    </div>

                    <!-- User Management -->
                    <div>
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            Users
                        </h3>
                        <div class="space-y-1">
                            <router-link
                                v-for="item in navigationGroups.users"
                                :key="item.name"
                                :to="item.to"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                                :class="[
                                    $route.name === item.name
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                ]"
                            >
                                <component :is="getIcon(item.name)" class="w-5 h-5 mr-3" />
                                {{ item.label }}
                            </router-link>
                        </div>
                    </div>

                    <!-- Analytics & SEO -->
                    <div>
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            Analytics & SEO
                        </h3>
                        <div class="space-y-1">
                            <router-link
                                v-for="item in navigationGroups.analytics"
                                :key="item.name"
                                :to="item.to"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                                :class="[
                                    $route.name === item.name
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                ]"
                            >
                                <component :is="getIcon(item.name)" class="w-5 h-5 mr-3" />
                                {{ item.label }}
                            </router-link>
                        </div>
                    </div>

                    <!-- System -->
                    <div>
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            System
                        </h3>
                        <div class="space-y-1">
                            <router-link
                                v-for="item in navigationGroups.system"
                                :key="item.name"
                                :to="item.to"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                                :class="[
                                    $route.name === item.name
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                ]"
                            >
                                <component :is="getIcon(item.name)" class="w-5 h-5 mr-3" />
                                {{ item.label }}
                            </router-link>
                        </div>
                    </div>

                    <!-- Logs & Monitoring -->
                    <div>
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            Monitoring
                        </h3>
                        <div class="space-y-1">
                            <router-link
                                v-for="item in navigationGroups.monitoring"
                                :key="item.name"
                                :to="item.to"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                                :class="[
                                    $route.name === item.name
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                ]"
                            >
                                <component :is="getIcon(item.name)" class="w-5 h-5 mr-3" />
                                {{ item.label }}
                            </router-link>
                        </div>
                    </div>

                    <!-- Advanced -->
                    <div>
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                            Advanced
                        </h3>
                        <div class="space-y-1">
                            <router-link
                                v-for="item in navigationGroups.advanced"
                                :key="item.name"
                                :to="item.to"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors"
                                :class="[
                                    $route.name === item.name
                                        ? 'bg-gray-800 text-white'
                                        : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                                ]"
                            >
                                <component :is="getIcon(item.name)" class="w-5 h-5 mr-3" />
                                {{ item.label }}
                            </router-link>
                        </div>
                    </div>
                </nav>

                <!-- User Info -->
                <div class="p-4 border-t border-gray-800">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img
                                :src="authStore.user?.avatar || '/default-avatar.png'"
                                :alt="authStore.user?.name"
                                class="w-10 h-10 rounded-full"
                            />
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">
                                {{ authStore.user?.name }}
                            </p>
                            <p class="text-xs text-gray-400 truncate">
                                {{ authStore.user?.email }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="handleLogout"
                        class="mt-3 w-full px-4 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                    >
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:pl-64">
            <!-- Top Navbar -->
            <header class="sticky top-0 z-40 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-6">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden text-gray-500 hover:text-gray-700"
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
                                    class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                />
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            
                            <!-- Search Results Dropdown -->
                            <div
                                v-if="showSearchResults && searchQuery"
                                class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg z-50 border border-gray-200 max-h-96 overflow-y-auto"
                                @click.stop
                            >
                                <div v-if="searching" class="p-4 text-center text-sm text-gray-500">
                                    Searching...
                                </div>
                                <div v-else-if="searchResults.length === 0" class="p-4 text-center text-sm text-gray-500">
                                    No results found
                                </div>
                                <div v-else class="divide-y divide-gray-200">
                                    <div
                                        v-for="result in searchResults"
                                        :key="`${result.type}-${result.id}`"
                                        @click="handleSearchResultClick(result)"
                                        class="p-4 hover:bg-gray-50 cursor-pointer"
                                    >
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <span class="px-2 py-1 text-xs font-semibold rounded" :class="getResultTypeClass(result.type)">
                                                    {{ result.type }}
                                                </span>
                                            </div>
                                            <div class="ml-3 flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">{{ result.title }}</p>
                                                <p v-if="result.description" class="text-xs text-gray-500 mt-1 truncate">{{ result.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-2 text-center">
                                        <router-link
                                            :to="{ name: 'search', query: { q: searchQuery } }"
                                            @click="showSearchResults = false"
                                            class="text-xs text-indigo-600 hover:text-indigo-800"
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
                                class="relative p-2 text-gray-500 hover:text-gray-700"
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
                                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 border border-gray-200"
                                @click.stop
                            >
                                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                    <router-link
                                        :to="{ name: 'notifications' }"
                                        class="text-xs text-indigo-600 hover:text-indigo-800"
                                        @click="showNotificationsDropdown = false"
                                    >
                                        View All
                                    </router-link>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div v-if="loadingNotifications" class="p-4 text-center text-sm text-gray-500">
                                        Loading...
                                    </div>
                                    <div v-else-if="recentNotifications.length === 0" class="p-4 text-center text-sm text-gray-500">
                                        No notifications
                                    </div>
                                    <div v-else class="divide-y divide-gray-200">
                                        <div
                                            v-for="notification in recentNotifications"
                                            :key="notification.id"
                                            class="p-4 hover:bg-gray-50 cursor-pointer"
                                            :class="{ 'bg-blue-50': !notification.read_at }"
                                            @click="handleNotificationClick(notification)"
                                        >
                                            <div class="flex items-start">
                                                <span
                                                    v-if="!notification.read_at"
                                                    class="h-2 w-2 bg-blue-600 rounded-full mt-2 mr-2"
                                                ></span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                                    <p class="text-xs text-gray-500 mt-1 truncate">{{ notification.message }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">{{ formatNotificationDate(notification.created_at) }}</p>
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

            <!-- Page Content -->
            <main class="p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, h } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const router = useRouter();
const authStore = useAuthStore();
const sidebarOpen = ref(false);
const showNotificationsDropdown = ref(false);
const notifications = ref([]);
const loadingNotifications = ref(false);
const notificationInterval = ref(null);
const searchQuery = ref('');
const showSearchResults = ref(false);
const searchResults = ref([]);
const searching = ref(false);
const searchTimeout = ref(null);

const navigationGroups = {
    content: [
        { name: 'contents', to: '/admin/contents', label: 'Contents' },
        { name: 'media', to: '/admin/media', label: 'Media' },
        { name: 'categories', to: '/admin/categories', label: 'Categories' },
        { name: 'tags', to: '/admin/tags', label: 'Tags' },
    ],
    users: [
        { name: 'users', to: '/admin/users', label: 'Users' },
        { name: 'comments', to: '/admin/comments', label: 'Comments' },
        { name: 'forms', to: '/admin/forms', label: 'Forms' },
    ],
    analytics: [
        { name: 'analytics', to: '/admin/analytics', label: 'Analytics' },
        { name: 'seo', to: '/admin/seo', label: 'SEO Tools' },
        { name: 'redirects', to: '/admin/redirects', label: 'Redirects' },
    ],
    system: [
        { name: 'settings', to: '/admin/settings', label: 'Settings' },
        { name: 'security', to: '/admin/security', label: 'Security' },
        { name: 'system', to: '/admin/system', label: 'System Info' },
        { name: 'backups', to: '/admin/backups', label: 'Backups' },
    ],
    monitoring: [
        { name: 'activity-logs', to: '/admin/activity-logs', label: 'Activity Logs' },
        { name: 'notifications', to: '/admin/notifications', label: 'Notifications' },
        { name: 'scheduled-tasks', to: '/admin/scheduled-tasks', label: 'Scheduled Tasks' },
        { name: 'logs', to: '/admin/logs', label: 'Log Viewer' },
    ],
    advanced: [
        { name: 'webhooks', to: '/admin/webhooks', label: 'Webhooks' },
        { name: 'custom-fields', to: '/admin/custom-fields', label: 'Custom Fields' },
        { name: 'file-manager', to: '/admin/file-manager', label: 'File Manager' },
        { name: 'themes', to: '/admin/themes', label: 'Themes' },
        { name: 'menus', to: '/admin/menus', label: 'Menus' },
        { name: 'widgets', to: '/admin/widgets', label: 'Widgets' },
        { name: 'plugins', to: '/admin/plugins', label: 'Plugins' },
        { name: 'languages', to: '/admin/languages', label: 'Languages' },
    ],
};

const getIcon = (name) => {
    const icons = {
        dashboard: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
        ]),
        contents: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
        ]),
        media: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' })
        ]),
        categories: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' })
        ]),
        tags: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' })
        ]),
        comments: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })
        ]),
        forms: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
        ]),
        users: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
        ]),
        analytics: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
        ]),
        seo: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' })
        ]),
        redirects: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' })
        ]),
        backups: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4' })
        ]),
        security: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' })
        ]),
        system: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01' })
        ]),
        'activity-logs': () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })
        ]),
        notifications: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' })
        ]),
        'scheduled-tasks': () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })
        ]),
        logs: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
        ]),
        webhooks: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 10V3L4 14h7v7l9-11h-7z' })
        ]),
        'custom-fields': () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z' })
        ]),
        'file-manager': () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' })
        ]),
        themes: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' })
        ]),
        menus: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 6h16M4 12h16M4 18h16' })
        ]),
        widgets: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v9a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h6a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z' })
        ]),
        plugins: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' })
        ]),
        languages: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129' })
        ]),
        settings: () => h('svg', { class: 'w-5 h-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }),
            h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })
        ]),
    };
    return icons[name] || icons.dashboard;
};

const unreadNotificationsCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

const recentNotifications = computed(() => {
    return notifications.value.slice(0, 5);
});

const fetchNotifications = async () => {
    if (!authStore.isAuthenticated) return;
    
    loadingNotifications.value = true;
    try {
        const response = await api.get('/admin/cms/notifications?limit=5');
        notifications.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    } finally {
        loadingNotifications.value = false;
    }
};

const handleNotificationClick = async (notification) => {
    if (!notification.read_at) {
        try {
            await api.post(`/admin/cms/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }
    showNotificationsDropdown.value = false;
    // Navigate to notification detail or related page if needed
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

const handleLogout = async () => {
    if (notificationInterval.value) {
        clearInterval(notificationInterval.value);
    }
    await authStore.logout();
    router.push({ name: 'login' });
};

// Close dropdown when clicking outside
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
            searchResults.value = response.data.data || response.data || [];
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
    
    // Navigate based on result type
    if (result.type === 'content') {
        router.push({ name: 'contents.edit', params: { id: result.id } });
    } else if (result.type === 'category') {
        router.push({ name: 'categories.edit', params: { id: result.id } });
    } else if (result.type === 'user') {
        router.push({ name: 'users.edit', params: { id: result.id } });
    } else if (result.url) {
        router.push(result.url);
    }
};

const getResultTypeClass = (type) => {
    const classes = {
        content: 'bg-blue-100 text-blue-800',
        category: 'bg-green-100 text-green-800',
        user: 'bg-purple-100 text-purple-800',
        media: 'bg-yellow-100 text-yellow-800',
        page: 'bg-indigo-100 text-indigo-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const handleClickOutside = (event) => {
    if (showNotificationsDropdown.value && !event.target.closest('.relative')) {
        showNotificationsDropdown.value = false;
    }
    if (showSearchResults.value && !event.target.closest('.relative')) {
        showSearchResults.value = false;
    }
};

watch(() => authStore.isAuthenticated, (isAuth) => {
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
    if (authStore.isAuthenticated) {
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
});
</script>

