<template>
    <div class="min-h-screen bg-background text-foreground">
        <!-- Sidebar -->
        <AdminSidebar
            :sidebar-minimized="sidebarMinimized"
            :sidebar-open="sidebarOpen"
            :user="authStore.user || undefined"
            @toggle-minimize="toggleSidebarMinimize"
            @close="closeSidebar"
            @logout="handleLogout"
        />

        <!-- Mobile Backdrop -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="sidebarOpen" 
                class="fixed inset-0 z-40 bg-background/80 backdrop-blur-sm lg:hidden"
                @click="closeSidebar"
            ></div>
        </Transition>

        <!-- Main Content -->
        <div :class="[
            'transition-all duration-300 ease-in-out',
            sidebarMinimized ? 'lg:pl-[68px]' : 'lg:pl-64'
        ]">
            <!-- Top Navbar -->
            <AdminNavbar
                :is-authenticated="authStore.isAuthenticated"
                :user="authStore.user || undefined"
                @toggle-sidebar="toggleSidebarOpen"
                @logout="handleLogout"
            />

            <!-- Page Content -->
            <main class="p-6">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useSidebar } from '../composables/useSidebar';
import { useLayoutMount } from '../composables/useLayoutMount';
import AdminSidebar from '../components/layouts/AdminSidebar.vue';
import AdminNavbar from '../components/layouts/AdminNavbar.vue';

const router = useRouter();
const authStore = useAuthStore();
const { sidebarMinimized, sidebarOpen, toggleSidebarMinimize, toggleSidebarOpen, closeSidebar } = useSidebar();

// Use shared mounted state for synchronized transitions
const { mounted } = useLayoutMount();

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
