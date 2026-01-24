<template>
    <!-- Dynamic Dashboard based on Role/Permissions -->
    <component :is="activeDashboard" />
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, type Component } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

// Dynamically import dashboard components to improve initial load
const AdminDashboard = defineAsyncComponent(() => import('@/components/dashboard/AdminDashboard.vue'));
const CreatorDashboard = defineAsyncComponent(() => import('@/components/dashboard/CreatorDashboard.vue'));
const ViewerDashboard = defineAsyncComponent(() => import('@/components/dashboard/ViewerDashboard.vue'));

// Determine which dashboard to show based on permissions
const activeDashboard = computed<Component>(() => {
    // Admin Dashboard: Users who can manage users or settings (Super Admin, Admin)
    // @ts-ignore - Permissions are string based
    if (authStore.hasPermission('manage users') || authStore.hasPermission('manage settings') || authStore.hasPermission('view analytics')) {
        return AdminDashboard;
    }
    
    // Creator Dashboard: Users who can create or edit content/media (Editor, Author)
    // @ts-ignore
    if (authStore.hasPermission('create content') || authStore.hasPermission('edit content') || authStore.hasPermission('upload media')) {
        return CreatorDashboard;
    }
    
    // Viewer Dashboard: Fallback for read-only users (Subscriber)
    return ViewerDashboard;
});
</script>
