<template>
    <section 
        :class="['transition-all duration-500', padding, animation]"
        :style="{ backgroundColor: bgColor || 'transparent' }"
    >
        <div :class="['flex', alignmentClass]">
            <a 
                :href="url || '#'" 
                :target="openNewTab ? '_blank' : '_self'"
                :class="[
                    'inline-flex items-center justify-center gap-2 font-bold transition-all shadow-lg transform hover:-translate-y-0.5 active:scale-95',
                    sizeClass,
                    variantClass,
                    radiusClass
                ]"
            >
                <component v-if="iconPosition === 'left' && iconName" :is="iconComponent" class="w-5 h-5" />
                {{ text || 'Click Here' }}
                <component v-if="iconPosition === 'right' && iconName" :is="iconComponent" class="w-5 h-5" />
            </a>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowRight, ExternalLink, ChevronRight, Download, Play, Mail, Phone } from 'lucide-vue-next';

const props = defineProps({
    text: { type: String, default: 'Click Here' },
    url: { type: String, default: '#' },
    openNewTab: { type: Boolean, default: false },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'medium' },
    alignment: { type: String, default: 'left' },
    iconName: { type: String, default: '' },
    iconPosition: { type: String, default: 'right' },
    radius: { type: String, default: 'rounded-full' },
    padding: { type: String, default: 'py-8' },
    bgColor: String,
    animation: { type: String, default: '' }
});

const icons = {
    'arrow-right': ArrowRight,
    'external-link': ExternalLink,
    'chevron-right': ChevronRight,
    'download': Download,
    'play': Play,
    'mail': Mail,
    'phone': Phone
};

const iconComponent = computed(() => icons[props.iconName] || null);

const alignmentClass = computed(() => ({
    'left': 'justify-start',
    'center': 'justify-center',
    'right': 'justify-end'
}[props.alignment] || 'justify-start'));

const sizeClass = computed(() => ({
    'small': 'px-4 py-2 text-sm',
    'medium': 'px-6 py-3 text-base',
    'large': 'px-8 py-4 text-lg'
}[props.size] || 'px-6 py-3 text-base'));

const variantClass = computed(() => ({
    'primary': 'bg-primary text-primary-foreground hover:bg-primary/90 hover:shadow-xl',
    'secondary': 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
    'outline': 'border-2 border-primary text-primary hover:bg-primary hover:text-primary-foreground',
    'ghost': 'text-primary hover:bg-primary/10'
}[props.variant] || 'bg-primary text-primary-foreground hover:bg-primary/90'));

const radiusClass = computed(() => props.radius);
</script>
