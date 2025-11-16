<template>
    <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import { useTheme } from './composables/useTheme';

const authStore = useAuthStore();
const { loadActiveTheme } = useTheme();

// Initialize dark mode from localStorage or system preference
const initDarkMode = () => {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

onMounted(async () => {
    initDarkMode();
    authStore.initAuth();
    
    // Load active theme for frontend (non-blocking, don't wait)
    // Theme loading failure shouldn't prevent app from loading
    loadActiveTheme('frontend').catch(err => {
        console.warn('Theme loading failed, continuing without theme:', err);
    });
    
    if (authStore.isAuthenticated) {
        authStore.fetchUser();
    }
});
</script>

