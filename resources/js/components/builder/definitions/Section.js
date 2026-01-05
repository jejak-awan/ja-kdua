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
            default: false
        },
        {
            key: 'padding_top',
            type: 'text',
            label: 'Top Padding',
            default: '4rem'
        },
        {
            key: 'padding_bottom',
            type: 'text',
            label: 'Bottom Padding',
            default: '4rem'
        },
        {
            key: 'background_color',
            type: 'color',
            label: 'Background Color',
            default: 'transparent'
        },
        {
            key: 'background_image',
            type: 'image',
            label: 'Background Image',
            default: null
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
