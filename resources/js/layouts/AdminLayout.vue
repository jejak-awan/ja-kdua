<template>
    <div 
        class="min-h-screen bg-sidebar text-foreground admin-instant"
        :class="{ 'no-transitions': resizing }"
    >
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
        <div 
            v-if="sidebarOpen" 
            class="fixed inset-0 z-40 bg-background/60 lg:hidden"
            @click="closeSidebar"
        ></div>

        <!-- Main Content -->
        <div
            :class="[
                'min-h-screen',
                sidebarMinimized ? 'lg:pl-[68px]' : 'lg:pl-64'
            ]"
        >
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
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useCmsStore } from '../stores/cms';
import { useSidebar } from '../composables/useSidebar';
import { useLayoutMount } from '../composables/useLayoutMount';
import { useHead } from '@vueuse/head';
import { useI18n } from 'vue-i18n';
import AdminSidebar from '../components/layouts/AdminSidebar.vue';
import AdminNavbar from '../components/layouts/AdminNavbar.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cmsStore = useCmsStore();
const { t, te } = useI18n();
const { sidebarMinimized, sidebarOpen, toggleSidebarMinimize, toggleSidebarOpen, closeSidebar } = useSidebar();

// Use shared mounted state for synchronized transitions
const { mounted } = useLayoutMount();

// Resize Throttling
const resizing = ref(false);
let resizeTimer: ReturnType<typeof setTimeout> | null = null;

const handleResize = () => {
    resizing.value = true;
    document.body.classList.add('no-transitions');
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        resizing.value = false;
        document.body.classList.remove('no-transitions');
    }, 200);
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    if (resizeTimer) clearTimeout(resizeTimer);
    document.body.classList.remove('no-transitions');
    window.removeEventListener('resize', handleResize);
});

// Reactive Global Title Management
useHead({
    title: computed(() => {
        // 1. Priority: Child components with useHead will override this (handled by library)
        // BUT if no child sets title, this is the default.
        
        // 2. Route Meta Title
        if (route.meta?.title) {
            return `${cmsStore.siteSettings?.site_name || 'JA CMS'} | ${route.meta.title}`;
        }
        
        // 3. Auto-generated from Route Name (Fallback)
        // Tries to find translation 'common.navigation.menu.[name]' or just capitalizes name
        if (route.name) {
            const name = String(route.name);
            // Handle dotted names like 'content-templates.create' -> just 'Create' or similar logic?
            // Simple approach: check specific translation or usage
            const camelName = name.replace(/-([a-z])/g, (g) => g[1].toUpperCase()).split('.')[0]; 
            const key = `common.navigation.menu.${camelName}`;
            
            let label = name;
            if (te(key)) {
                label = t(key);
            } else {
                // Fallback formatter
                label = name.split('.').pop() || name; // 'users.create' -> 'create'
                label = label.charAt(0).toUpperCase() + label.slice(1);
            }
            
            return `${cmsStore.siteSettings?.site_name || 'JA CMS'} | ${label}`;
        }

        return cmsStore.siteSettings?.site_name || 'JA CMS';
    })
});

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'login' });
};
</script>
