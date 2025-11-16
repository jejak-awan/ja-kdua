<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 bg-gray-900 dark:bg-gray-950 text-white transform transition-all duration-300 ease-in-out',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarMinimized ? 'w-20' : 'w-64'
        ]"
    >
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-800">
                <div v-if="!sidebarMinimized" class="flex items-center">
                    <h1 class="text-xl font-bold">JA CMS</h1>
                </div>
                <div v-else class="flex items-center justify-center w-full">
                    <span class="text-xl font-bold">J</span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="$emit('toggle-minimize')"
                        class="hidden lg:flex p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded transition-colors"
                        :title="sidebarMinimized ? 'Expand sidebar' : 'Minimize sidebar'"
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
                        class="lg:hidden text-gray-400 hover:text-white"
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
                                ? 'bg-gray-800 text-white'
                                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                        ]"
                        :title="sidebarMinimized ? 'Dashboard' : ''"
                    >
                        <component :is="getIcon('dashboard')" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                        <span v-if="!sidebarMinimized" class="truncate">Dashboard</span>
                    </router-link>
                </div>

                <!-- Content Management -->
                <div>
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Content
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in navigationGroups.content"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                            :title="sidebarMinimized ? item.label : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- User Management -->
                <div>
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Users
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in navigationGroups.users"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                            :title="sidebarMinimized ? item.label : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Analytics & SEO -->
                <div>
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Analytics & SEO
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in navigationGroups.analytics"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                            :title="sidebarMinimized ? item.label : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- System -->
                <div>
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        System
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in navigationGroups.system"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                            :title="sidebarMinimized ? item.label : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Logs & Monitoring -->
                <div>
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Monitoring
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in navigationGroups.monitoring"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                            :title="sidebarMinimized ? item.label : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>

                <!-- Advanced -->
                <div>
                    <h3 v-if="!sidebarMinimized" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Advanced
                    </h3>
                    <div class="space-y-1">
                        <router-link
                            v-for="item in navigationGroups.advanced"
                            :key="item.name"
                            :to="item.to"
                            class="flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-colors group"
                            :class="[
                                $route.name === item.name
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            ]"
                            :title="sidebarMinimized ? item.label : ''"
                        >
                            <component :is="getIcon(item.name)" class="w-5 h-5 flex-shrink-0" :class="sidebarMinimized ? '' : 'mr-3'" />
                            <span v-if="!sidebarMinimized" class="truncate">{{ item.label }}</span>
                        </router-link>
                    </div>
                </div>
            </nav>

            <!-- User Info -->
            <div class="p-3 border-t border-gray-800">
                <div v-if="!sidebarMinimized" class="flex items-center mb-3">
                    <div class="flex-shrink-0">
                        <img
                            :src="user?.avatar || '/default-avatar.png'"
                            :alt="user?.name"
                            class="w-10 h-10 rounded-full"
                        >
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ user?.name }}
                        </p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
                <div v-else class="flex items-center justify-center mb-3">
                    <img
                        :src="user?.avatar || '/default-avatar.png'"
                        :alt="user?.name"
                        class="w-10 h-10 rounded-full"
                        :title="user?.name"
                    >
                </div>
                <button
                    @click="$emit('logout')"
                    class="w-full px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 rounded-lg transition-colors flex items-center justify-center"
                    :title="sidebarMinimized ? 'Logout' : ''"
                >
                    <svg v-if="sidebarMinimized" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span v-else>Logout</span>
                </button>
            </div>
        </div>
    </aside>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { navigationGroups } from '../../utils/navigation';
import { getIcon } from '../../utils/icons';

defineProps({
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

const $route = useRoute();
</script>

