<script setup>
import { computed } from 'vue';
import { Facebook, Twitter, Linkedin } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    name: {
        type: String,
        default: 'John Doe'
    },
    position: {
        type: String,
        default: 'Position'
    },
    bio: {
        type: String,
        default: 'Write a short biography here.'
    },
    image_url: {
        type: String,
        default: ''
    },
    image_shape: {
        type: String,
        default: 'circle'
    },
    alignment: {
        type: String,
        default: 'center'
    },
    facebook_url: {
        type: String,
        default: ''
    },
    twitter_url: {
        type: String,
        default: ''
    },
    linkedin_url: {
        type: String,
        default: ''
    }
});

const imageClass = computed(() => {
    return {
        'rounded-full': props.image_shape === 'circle',
        'rounded-2xl': props.image_shape === 'rounded',
        'rounded-none': props.image_shape === 'square'
    };
});

const containerClass = computed(() => {
    return {
        'flex flex-col items-center text-center': props.alignment === 'center',
        'flex flex-row items-center gap-8 text-left': props.alignment === 'left'
    };
});
</script>

<template>
    <div :class="containerClass" class="person-block group">
        <!-- Profile Image -->
        <div class="relative mb-6 overflow-hidden" :class="[imageClass, { 'mb-0': alignment === 'left' }]">
            <img 
                v-if="image_url"
                :src="image_url" 
                :alt="name" 
                class="w-48 h-48 object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div v-else class="w-48 h-48 bg-muted flex items-center justify-center text-muted-foreground rounded-full">
                No Image
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1">
            <h3 class="text-2xl font-bold tracking-tight mb-1">{{ name }}</h3>
            <p class="text-primary font-medium mb-4">{{ position }}</p>
            <p class="opacity-80 leading-relaxed mb-6 max-w-md">
                {{ bio }}
            </p>

            <!-- Social Links -->
            <div class="flex items-center gap-4" :class="{ 'justify-center': alignment === 'center' }">
                <a v-if="facebook_url" :href="facebook_url" class="p-2 rounded-full bg-muted hover:bg-primary hover:text-white transition-colors">
                    <Facebook :size="18" />
                </a>
                <a v-if="twitter_url" :href="twitter_url" class="p-2 rounded-full bg-muted hover:bg-primary hover:text-white transition-colors">
                    <Twitter :size="18" />
                </a>
                <a v-if="linkedin_url" :href="linkedin_url" class="p-2 rounded-full bg-muted hover:bg-primary hover:text-white transition-colors">
                    <Linkedin :size="18" />
                </a>
            </div>
        </div>
    </div>
</template>
