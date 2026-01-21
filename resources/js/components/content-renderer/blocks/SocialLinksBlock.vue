<script setup>
import { computed } from 'vue';
import { 
  Facebook, Twitter, Instagram, Linkedin, Youtube, 
  Github, Dribbble, MessageCircle, Mail, Globe, Share2
} from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    links: {
        type: Array,
        default: () => []
    },
    alignment: {
        type: String,
        default: 'center'
    },
    iconSize: {
        type: [Number, String],
        default: 24
    },
    displayStyle: {
        type: String,
        default: 'icon-only'
    },
    gap: {
        type: [Number, String],
        default: 16
    },
    color: {
        type: String,
        default: '#333333'
    },
    hoverColor: {
        type: String,
        default: '#2059ea'
    }
});

const iconMap = {
  facebook: Facebook,
  twitter: Twitter,
  instagram: Instagram,
  linkedin: Linkedin,
  youtube: Youtube,
  github: Github,
  dribbble: Dribbble,
  whatsapp: MessageCircle,
  email: Mail,
  website: Globe
};

const getIcon = (network) => iconMap[network] || Share2;

const wrapperClasses = computed(() => {
    return {
        'justify-start': props.alignment === 'left' || props.alignment === 'start',
        'justify-center': props.alignment === 'center',
        'justify-end': props.alignment === 'right' || props.alignment === 'end'
    };
});

const getLinkStyles = (link) => {
    const size = parseInt(props.iconSize) || 24;
    let styles = {
        color: link.useCustomColor ? link.iconColor : props.color,
        transition: 'all 0.3s ease'
    };
    
    if (props.displayStyle === 'icon-circle' || props.displayStyle === 'icon-square') {
        const dim = size * 1.8;
        styles.width = `${dim}px`;
        styles.height = `${dim}px`;
        styles.backgroundColor = link.useCustomColor ? link.backgroundColor : '#f3f4f6';
        styles.borderRadius = props.displayStyle === 'icon-circle' ? '50%' : '8px';
        styles.display = 'flex';
        styles.alignItems = 'center';
        styles.justifyContent = 'center';
    }
    
    return styles;
};

const gapStyles = computed(() => ({
    gap: `${parseInt(props.gap) || 16}px`
}));
</script>

<template>
    <div class="flex flex-wrap" :class="wrapperClasses" :style="gapStyles">
        <a 
            v-for="(link, index) in links" 
            :key="index"
            :href="link.url || '#'"
            target="_blank"
            class="social-link-item hover:scale-110"
            :style="getLinkStyles(link)"
        >
            <component 
                :is="getIcon(link.network)" 
                :size="parseInt(iconSize) || 24"
            />
        </a>
    </div>
</template>

<style scoped>
.social-link-item:hover {
    filter: brightness(0.9);
}
</style>
