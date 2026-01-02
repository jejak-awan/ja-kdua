<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['mx-auto px-6', width, alignmentClass]">
            <div :class="['flex flex-wrap gap-3', alignmentFlexClass]">
                <a 
                    v-for="(link, index) in links" 
                    :key="index"
                    :href="link.url || '#'"
                    target="_blank"
                    rel="noopener noreferrer"
                    :class="[
                        'flex items-center justify-center transition-all transform hover:scale-110',
                        sizeClass,
                        shapeClass
                    ]"
                    :style="{ 
                        backgroundColor: iconBgColor || getDefaultBg(link.platform),
                        color: iconColor || '#ffffff'
                    }"
                >
                    <component :is="getPlatformIcon(link.platform)" :class="iconSizeClass" />
                </a>
            </div>
        </div>
    </section>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
});

import { computed } from 'vue';
import { 
    Facebook, Twitter, Instagram, Linkedin, Youtube, Github,
    Mail, Globe, MessageCircle, Send, Phone
} from 'lucide-vue-next';

const props = defineProps({
    links: { 
        type: Array, 
        default: () => [
            { platform: 'facebook', url: '#' },
            { platform: 'twitter', url: '#' },
            { platform: 'instagram', url: '#' },
            { platform: 'linkedin', url: '#' }
        ] 
    },
    size: { type: String, default: 'medium' },
    shape: { type: String, default: 'circle' },
    alignment: { type: String, default: 'center' },
    iconColor: { type: String, default: '' },
    iconBgColor: { type: String, default: '' },
    width: { type: String, default: 'max-w-4xl' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const platformIcons = {
    facebook: Facebook, twitter: Twitter, instagram: Instagram, linkedin: Linkedin,
    youtube: Youtube, github: Github, email: Mail, website: Globe,
    whatsapp: MessageCircle, telegram: Send, phone: Phone
};

const platformColors = {
    facebook: '#1877F2', twitter: '#1DA1F2', instagram: '#E4405F', linkedin: '#0A66C2',
    youtube: '#FF0000', github: '#181717', email: '#EA4335', website: '#4285F4',
    whatsapp: '#25D366', telegram: '#0088CC', phone: '#34B7F1'
};

const getPlatformIcon = (platform) => platformIcons[platform] || Globe;
const getDefaultBg = (platform) => platformColors[platform] || '#6366f1';

const alignmentClass = computed(() => ({
    'left': 'text-left', 'center': 'text-center', 'right': 'text-right'
}[props.alignment] || 'text-center'));

const alignmentFlexClass = computed(() => ({
    'left': 'justify-start', 'center': 'justify-center', 'right': 'justify-end'
}[props.alignment] || 'justify-center'));

const sizeClass = computed(() => ({
    'small': 'w-10 h-10', 'medium': 'w-12 h-12', 'large': 'w-14 h-14'
}[props.size] || 'w-12 h-12'));

const iconSizeClass = computed(() => ({
    'small': 'w-4 h-4', 'medium': 'w-5 h-5', 'large': 'w-6 h-6'
}[props.size] || 'w-5 h-5'));

const shapeClass = computed(() => ({
    'circle': 'rounded-full', 'rounded': 'rounded-xl', 'square': 'rounded-none'
}[props.shape] || 'rounded-full'));
</script>
