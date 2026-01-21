<template>
    <section 
        :class="containerClasses"
        :style="{ backgroundColor: bgColor || 'transparent', ...paddingStyle }"
    >
        <div :class="['mx-auto px-6', width || 'max-w-4xl']">
            <div :class="['flex gap-4 p-4 rounded-xl', variantClass]">
                <div class="flex-shrink-0" v-if="showIcon">
                    <component :is="variantIcon" class="w-5 h-5 mt-0.5" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 v-if="title" class="font-bold mb-1">{{ title }}</h4>
                    <p class="text-sm leading-relaxed opacity-90">{{ message || 'Alert message goes here...' }}</p>
                </div>
                <button 
                    v-if="dismissible" 
                    class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity"
                    @click="dismissed = true"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Info, AlertTriangle, CheckCircle, XCircle, Bell, X } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    title: { type: String, default: '' },
    message: { type: String, default: 'This is an important message for your visitors.' },
    variant: { type: String, default: 'info' },
    dismissible: { type: Boolean, default: false },
    showIcon: { type: Boolean, default: true },
    width: { type: String, default: 'max-w-4xl' },
    padding: { type: [String, Object], default: null }, // Accepting object or string
    bgColor: String,
    animation: { type: String, default: '' }
});

const dismissed = ref(false);

const paddingStyle = computed(() => {
    if (typeof props.padding === 'object' && props.padding !== null) {
         return {
             paddingTop: `${props.padding.top}${props.padding.unit || 'px'}`,
             paddingBottom: `${props.padding.bottom}${props.padding.unit || 'px'}`,
             paddingLeft: `${props.padding.left}${props.padding.unit || 'px'}`,
             paddingRight: `${props.padding.right}${props.padding.unit || 'px'}`
         };
    }
    return {};
});

const containerClasses = computed(() => {
    const classes = ['transition-all duration-500', props.animation];
    // Only add padding class if it's a string, otherwise style handles it
    if (typeof props.padding === 'string') {
        classes.push(props.padding);
    } else if (!props.padding) {
        classes.push('py-6'); // Default backup
    }
    return classes.filter(Boolean);
});

const variantIcons = {
    info: Info, warning: AlertTriangle, success: CheckCircle, error: XCircle, notice: Bell
};

const variantIcon = computed(() => variantIcons[props.variant] || Info);

const variantClass = computed(() => ({
    'info': 'bg-blue-50 text-blue-900 dark:bg-blue-950/50 dark:text-blue-100 border border-blue-200 dark:border-blue-800',
    'warning': 'bg-amber-50 text-amber-900 dark:bg-amber-950/50 dark:text-amber-100 border border-amber-200 dark:border-amber-800',
    'success': 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-100 border border-emerald-200 dark:border-emerald-800',
    'error': 'bg-red-50 text-red-900 dark:bg-red-950/50 dark:text-red-100 border border-red-200 dark:border-red-800',
    'notice': 'bg-purple-50 text-purple-900 dark:bg-purple-950/50 dark:text-purple-100 border border-purple-200 dark:border-purple-800'
}[props.variant] || 'bg-blue-50 text-blue-900'));
</script>
