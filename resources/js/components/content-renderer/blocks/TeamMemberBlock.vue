<template>
    <div class="team-member-block flex flex-col group/team" :class="containerClass">
        <!-- Image -->
        <div 
            v-if="image" 
            class="team-member-image overflow-hidden"
            :style="{ 
                width: `${imageSize}px`, 
                height: `${imageSize}px`,
                borderRadius: `${imageBorderRadius}%`,
                marginBottom: layout === 'stacked' ? '20px' : '0',
                marginRight: layout === 'horizontal' ? '20px' : '0'
            }"
        >
            <img 
                :src="image" 
                :alt="name" 
                class="w-full h-full object-cover transition-transform duration-500 group-hover/team:scale-110"
            />
        </div>

        <!-- Content -->
        <div class="team-member-content flex-1">
            <h3 
                class="text-xl font-bold mb-1"
                :style="{ color: nameColor }"
            >
                {{ name }}
            </h3>
            <div 
                class="text-sm font-medium mb-3 uppercase tracking-wider opacity-70"
                :style="{ color: positionColor }"
            >
                {{ position }}
            </div>
            
            <p 
                v-if="bio" 
                class="text-sm leading-relaxed mb-4 opacity-80"
                :style="{ color: bioColor }"
            >
                {{ bio }}
            </p>

            <!-- Social Links (Repeater) -->
            <div 
                v-if="socialLinks && socialLinks.length > 0" 
                class="flex flex-wrap gap-3 mt-auto"
                :class="socialLinksClass"
            >
                <a 
                    v-for="(link, index) in socialLinks" 
                    :key="index"
                    :href="link.url || '#'" 
                    class="p-2 rounded-full transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                    :style="getLinkStyle(link)"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <component 
                        :is="getIcon(link.network)" 
                        class="w-4 h-4" 
                    />
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import * as Icons from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    name: { type: String, default: '' },
    position: { type: String, default: '' },
    bio: { type: String, default: '' },
    image: { type: String, default: '' },
    socialLinks: { type: Array, default: () => [] }, // Repeater items
    // Layout
    layout: { type: String, default: 'stacked' }, // stacked, horizontal
    alignment: { type: String, default: 'center' },
    // Styling
    imageSize: { type: [Number, String], default: 150 },
    imageBorderRadius: { type: [Number, String], default: 50 },
    nameColor: String,
    positionColor: String,
    bioColor: String
});

const containerClass = computed(() => {
    return {
        'items-center text-center': props.layout === 'stacked' && props.alignment === 'center',
        'items-start text-left': props.layout === 'stacked' && props.alignment === 'left',
        'items-end text-right': props.layout === 'stacked' && props.alignment === 'right',
        'flex-row items-center': props.layout === 'horizontal',
        'flex-row-reverse': props.layout === 'horizontal' && props.alignment === 'right' // rudimentary support
    };
});

const socialLinksClass = computed(() => {
    if (props.layout === 'stacked') {
        if (props.alignment === 'center') return 'justify-center';
        if (props.alignment === 'right') return 'justify-end';
        return 'justify-start';
    }
    return '';
});

const getIcon = (network) => {
    // Map network names to Lucide icons
    const map = {
        facebook: 'Facebook',
        twitter: 'Twitter',
        instagram: 'Instagram',
        linkedin: 'Linkedin',
        youtube: 'Youtube',
        email: 'Mail',
        website: 'Globe'
    };
    const iconName = map[network] || 'Link';
    return Icons[iconName] || Icons.Link;
};

const getLinkStyle = (link) => {
    // Default Social Colors
    const colors = {
        facebook: { bg: '#e7f3ff', text: '#1877F2' },
        twitter: { bg: '#e8f5fe', text: '#1DA1F2' },
        instagram: { bg: '#fef0f3', text: '#E4405F' },
        linkedin: { bg: '#e7f0f7', text: '#0A66C2' },
        youtube: { bg: '#ffebee', text: '#FF0000' },
        email: { bg: '#f5f5f5', text: '#333333' },
        website: { bg: '#f0f0f0', text: '#333333' }
    };

    if (link.useCustomColor) {
        return {
            backgroundColor: link.backgroundColor || '#f5f5f5',
            color: link.iconColor || '#333333'
        };
    }

    const defaultColor = colors[link.network] || { bg: '#f5f5f5', text: '#333333' };
    return {
        backgroundColor: defaultColor.bg,
        color: defaultColor.text
    };
};
</script>
