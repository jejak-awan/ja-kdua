import { Map } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'map',
    label: 'Map',
    icon: Map,
    description: 'Embed Google Maps or other map services.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/MapBlock.vue')),
    settings: [
        {
            key: 'embedUrl',
            type: 'text',
            label: 'Map Embed URL',
            default: ''
        },
        {
            key: 'height',
            type: 'slider',
            label: 'Map Height',
            min: 200,
            max: 600,
            step: 50,
            unit: 'px',
            default: 400
        },
        {
            key: 'caption',
            type: 'text',
            label: 'Caption (Optional)',
            default: ''
        },
        {
            key: 'radius',
            type: 'select',
            label: 'Border Radius',
            options: [
                { label: 'None', value: 'rounded-none' },
                { label: 'Small', value: 'rounded-lg' },
                { label: 'Medium', value: 'rounded-xl' },
                { label: 'Large', value: 'rounded-2xl' }
            ],
            default: 'rounded-xl'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8'
        }
    ],
    defaultSettings: {
        embedUrl: '',
        height: 400,
        caption: '',
        radius: 'rounded-xl',
        width: 'max-w-4xl',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
