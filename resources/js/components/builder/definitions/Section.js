import { LayoutPanelTop } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'section',
    label: 'Section',
    icon: LayoutPanelTop,
    category: 'structure',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/SectionBlock.vue')),
    settings_schema: {
        layout: {
            type: 'group',
            label: 'Layout',
            fields: {
                full_width: {
                    type: 'switch',
                    label: 'Full Width',
                    default: false
                },
                height: {
                    type: 'select',
                    label: 'Height',
                    options: [
                        { label: 'Auto', value: 'auto' },
                        { label: 'Screen Height', value: 'min-h-screen' },
                        { label: 'Large (py-32)', value: 'py-32' },
                        { label: 'Medium (py-20)', value: 'py-20' },
                        { label: 'Small (py-12)', value: 'py-12' }
                    ],
                    default: 'auto'
                }
            }
        },
        background: {
            type: 'group',
            label: 'Background',
            fields: {
                background_color: {
                    type: 'color',
                    label: 'Color',
                    default: 'transparent'
                },
                background_image: {
                    type: 'image',
                    label: 'Image',
                    default: null
                },
                overlay_opacity: {
                    type: 'slider',
                    label: 'Overlay Opacity',
                    min: 0,
                    max: 100,
                    default: 0
                }
            }
        },
        spacing: {
            type: 'group',
            label: 'Spacing',
            fields: {
                padding_top: { type: 'text', label: 'Top Padding', default: '4rem' },
                padding_bottom: { type: 'text', label: 'Bottom Padding', default: '4rem' }
            }
        }
    },
    defaultSettings: {
        full_width: false,
        background_color: 'transparent',
        padding_top: '4rem',
        padding_bottom: '4rem',
        blocks: [] // Nested blocks
    }
};
