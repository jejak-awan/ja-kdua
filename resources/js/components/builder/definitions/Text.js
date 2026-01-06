import { Type, AlignLeft, AlignCenter, AlignRight } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'text',
    label: 'Rich Text',
    icon: Type,
    description: 'Rich text area for your page body.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/TextBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: 'Section Title',
            tab: 'content'
        },
        {
            key: 'content',
            type: 'richtext', // Will map to textarea/tiptap
            label: 'Content',
            default: 'Design beautiful layouts with zero compromise on performance. JA-Builder gives you the power of professional tools directly in your browser.',
            tab: 'content'
        },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left', icon: AlignLeft },
                { label: 'Center', value: 'text-center', icon: AlignCenter },
                { label: 'Right', value: 'text-right', icon: AlignRight }
            ],
            default: 'text-left',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16',
            tab: 'style'
        },
        {
            key: 'isProse',
            type: 'boolean',
            label: 'Use Prose Styling',
            default: true,
            tab: 'style'
        },
        {
            key: 'animation',
            type: 'select',
            label: 'Animation',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-in fade-in duration-700' },
                { label: 'Slide Up', value: 'animate-in slide-in-from-bottom-4 duration-700' }
            ],
            default: '',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'Section Title',
        content: 'Design beautiful layouts with zero compromise on performance. JA-Builder gives you the power of professional tools directly in your browser.',
        alignment: 'text-left',
        padding: 'py-16',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
