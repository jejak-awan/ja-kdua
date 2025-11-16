import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createHead } from '@vueuse/head';
import router from './router';
import App from './App.vue';
import lazyLoad from './directives/lazyLoad';

const app = createApp(App);
const pinia = createPinia();
const head = createHead();

// Register global directives
app.directive('lazy', lazyLoad);

app.use(pinia);
app.use(router);
app.use(head);

app.mount('#app');
