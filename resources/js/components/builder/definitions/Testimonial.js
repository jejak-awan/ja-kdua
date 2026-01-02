import { Quote } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'testimonial',
    label: 'Testimonial',
    icon: Quote,
    description: 'A stylish quote from a customer or client.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/TestimonialBlock.vue')),
    settings: [
        {
            key: 'quote',
            type: 'textarea',
            label: 'Quote Text',
            default: 'This platform has completely transformed how we build our web presence. The visual builder is intuitive and powerful.',
            responsive: true
        },
        {
            key: 'author',
            type: 'text',
            label: 'Author Name',
            default: 'Sarah Johnson',
            responsive: true
        },
        {
            key: 'job_title',
            type: 'text',
            label: 'Job Title',
            default: 'CEO at Techflow',
            responsive: true
        },
        {
            key: 'avatar',
            type: 'image',
            label: 'Author Avatar',
            default: ''
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'text-left' },
                { label: 'Center', value: 'text-center' },
                { label: 'Right', value: 'text-right' }
            ],
            default: 'text-left',
            responsive: true
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
            default: 'bg-card border shadow-sm'
        },
        {
            key: 'quote_color',
            type: 'color',
            label: 'Quote Text Color',
            default: ''
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
