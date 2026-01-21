import { defineAsyncComponent } from 'vue';

export default {
    name: 'lottie', // Must match builder module name
    component: defineAsyncComponent(() => import('../blocks/LottieBlock.vue')),
    label: 'Lottie Animation',
    icon: 'Film'
};
