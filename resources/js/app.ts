import './bootstrap';
import '../css/app.css';
import '../css/editor.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from '@vueuse/head';
import router from './router'; // Assuming router is index.js/ts
import App from './App.vue';
// @ts-ignore
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

import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

const app = createApp(App);
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);
const head = createHead();

// Register global directives
app.directive('lazy', lazyLoad);

app.use(pinia);
app.use(router);
app.use(head);
app.use(i18n);

import Logger from './utils/logger';
app.use(Logger);



app.mount('#app');
