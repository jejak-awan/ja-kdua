import { Quote } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'testimonial',
    label: 'Testimonial',
    icon: Quote,
    description: 'A stylish quote from a customer or client.',
    component: defineAsyncComponent(() => import('@/components/content-renderer/blocks/TestimonialBlock.vue')),
    settings: [
        { type: 'header', label: 'Quote', tab: 'content' },
        {
            key: 'quote',
            type: 'textarea',
            label: 'Quote Text',
            default: 'This platform has completely transformed how we build our web presence. The visual builder is intuitive and powerful.',
            responsive: true,
            tab: 'content'
        },
        { type: 'header', label: 'Author', tab: 'content' },
        {
            key: 'author',
            type: 'text',
            label: 'Author Name',
            default: 'Sarah Johnson',
            responsive: true,
            tab: 'content'
        },
        {
            key: 'job_title',
            type: 'text',
            label: 'Job Title',
            default: 'CEO at Techflow',
            responsive: true,
            tab: 'content'
        },
        {
            key: 'avatar',
            type: 'image',
            label: 'Author Avatar',
            default: '',
            tab: 'content'
        },
        { type: 'header', label: 'Style', tab: 'style' },
        {
            key: 'alignment',
            type: 'toggle_group',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' },
                { label: 'Right', value: 'text-right' }
            ],
            default: 'text-left',
            responsive: true,
            tab: 'style'
        },
        {
            key: 'style',
            type: 'select',
            label: 'Card Style',
            options: [
                { label: 'Standard', value: 'bg-card border shadow-sm' },
                { label: 'Modern (Floating)', value: 'bg-background shadow-xl border-none' },
                { label: 'Minimal', value: 'bg-transparent border-none shadow-none p-0' }
            ],
            default: 'bg-card border shadow-sm',
            tab: 'style'
        },
        {
            key: 'quote_color',
            type: 'color',
            label: 'Quote Text Color',
            default: '',
            tab: 'style'
        }
    ],
    defaultSettings: {
        quote: 'This platform has completely transformed how we build our web presence. The visual builder is intuitive and powerful.',
        author: 'Sarah Johnson',
        job_title: 'CEO at Techflow',
        alignment: 'text-left',
        style: 'bg-card border shadow-sm',
        padding: 'p-8',
        radius: 'rounded-2xl'
    }
};
