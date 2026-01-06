import { LayoutPanelTop } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'section',
    label: 'Section',
    icon: LayoutPanelTop,
    category: 'structure',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/SectionBlock.vue')),
    settings: [
        {
            key: 'full_width',
            type: 'switch',
            label: 'Full Width',
            default: false,
            tab: 'style'
        },
        {
            key: 'padding_top',
            type: 'toggle_group',
            label: 'Top Padding',
            options: [
                { label: 'None', value: '0rem' },
                { label: 'Sm', value: '2rem' },
                { label: 'Md', value: '4rem' },
                { label: 'Lg', value: '8rem' }
            ],
            default: '4rem',
            tab: 'style'
        },
        {
            key: 'padding_bottom',
            type: 'toggle_group',
            label: 'Bottom Padding',
            options: [
                { label: 'None', value: '0rem' },
                { label: 'Sm', value: '2rem' },
                { label: 'Md', value: '4rem' },
                { label: 'Lg', value: '8rem' }
            ],
            default: '4rem',
            tab: 'style'
        },
        {
            key: 'background_color',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'background_image',
            type: 'image',
            label: 'Background Image',
            default: null,
            tab: 'style'
        }
    ],
    defaultSettings: {
        full_width: false,
        background_color: 'transparent',
        padding_top: '4rem',
        padding_bottom: '4rem',
        blocks: [] // Nested blocks
    }
};
