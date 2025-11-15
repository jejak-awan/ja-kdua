<template>
    <img
        :src="placeholder || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'1\' height=\'1\'%3E%3C/svg%3E'"
        :data-src="src"
        :alt="alt"
        :class="[
            'lazy-image',
            loadingClass,
            loadedClass,
            imageClass
        ]"
        @load="onLoad"
        @error="onError"
        ref="imageRef"
    />
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    alt: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: null,
    },
    imageClass: {
        type: String,
        default: '',
    },
    loadingClass: {
        type: String,
        default: 'opacity-0 transition-opacity duration-300',
    },
    loadedClass: {
        type: String,
        default: 'opacity-100',
    },
    rootMargin: {
        type: String,
        default: '50px',
    },
});

const emit = defineEmits(['load', 'error']);

const imageRef = ref(null);
const isLoaded = ref(false);
const hasError = ref(false);
let observer = null;

const onLoad = () => {
    isLoaded.value = true;
    emit('load');
};

const onError = (event) => {
    hasError.value = true;
    emit('error', event);
};

const loadImage = () => {
    if (imageRef.value && !isLoaded.value && !hasError.value) {
        const dataSrc = imageRef.value.getAttribute('data-src');
        if (dataSrc) {
            imageRef.value.src = dataSrc;
        }
    }
};

onMounted(() => {
    // Check if IntersectionObserver is supported
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        loadImage();
                        if (observer && imageRef.value) {
                            observer.unobserve(imageRef.value);
                        }
                    }
                });
            },
            {
                rootMargin: props.rootMargin,
            }
        );

        if (imageRef.value) {
            observer.observe(imageRef.value);
        }
    } else {
        // Fallback: load immediately if IntersectionObserver is not supported
        loadImage();
    }
});

onUnmounted(() => {
    if (observer && imageRef.value) {
        observer.unobserve(imageRef.value);
    }
});
</script>

<style scoped>
.lazy-image {
    will-change: opacity;
}
</style>

