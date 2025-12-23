import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from '@vueuse/head';
import router from './router';
import App from './App.vue';
import lazyLoad from './directives/lazyLoad';
import i18n from './i18n';

// Initialize dark mode EARLY to prevent white flash
const initDarkMode = () => {
    const THEME_KEY = 'admin-dark-mode';
    const saved = localStorage.getItem(THEME_KEY);

    // Apply dark class immediately based on saved preference or system
    if (saved === 'dark' || (saved === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else if (saved === 'light') {
        document.documentElement.classList.remove('dark');
    }
};

// Run before anything else
initDarkMode();

const app = createApp(App);
const pinia = createPinia();
const head = createHead();

// Register global directives
app.directive('lazy', lazyLoad);

app.use(pinia);
app.use(router);
app.use(head);
app.use(i18n);

app.mount('#app');
