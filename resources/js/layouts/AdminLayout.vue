<template>
    <div class="min-h-screen bg-background text-foreground">
        <!-- Sidebar -->
        <AdminSidebar
            :sidebar-minimized="sidebarMinimized"
            :sidebar-open="sidebarOpen"
            :user="authStore.user"
            @toggle-minimize="toggleSidebarMinimize"
            @close="closeSidebar"
            @logout="handleLogout"
        />

        <!-- Main Content -->
        <div :class="[
            'transition-all duration-300 ease-in-out',
            sidebarMinimized ? 'lg:pl-[68px]' : 'lg:pl-64'
        ]">
            <!-- Top Navbar -->
            <AdminNavbar
                :is-authenticated="authStore.isAuthenticated"
                :user="authStore.user"
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

<script setup>
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
