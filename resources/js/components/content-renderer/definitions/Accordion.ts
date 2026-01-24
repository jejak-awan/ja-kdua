import type { BlockDefinition } from '@/types/builder';
import { List } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'accordion',
    label: 'Accordion',
    icon: List,
    description: 'Expandable list for FAQs or details.',
    component: defineAsyncComponent(() => import('@/shared/blocks/AccordionBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: 'Frequently Asked Questions',
            tab: 'content'
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Items',
            itemLabel: 'Question',
            fields: [
                { key: 'question', type: 'text', label: 'Question', default: 'New Question' },
                { key: 'answer', type: 'textarea', label: 'Answer', default: 'Answer goes here.' }
            ],
            default: [
                { question: 'What is JA-Builder?', answer: 'A powerful visual page builder for modern web apps.' },
                { question: 'Is it responsive?', answer: 'Yes, it works perfectly on all devices.' },
                { question: 'Can I customize it?', answer: 'Absolutely! It is built with Vue and Tailwind CSS.' }
            ],
            tab: 'content'
        },
        {
            key: 'variant',
            type: 'select',
            label: 'Style Variant',
            options: [
                { label: 'Simple (Dividers)', value: 'simple' },
                { label: 'Boxed (Cards)', value: 'boxed' },
                { label: 'Minimal (Clean)', value: 'minimal' }
            ],
            default: 'simple',
            tab: 'style'
        },
        {
            key: 'iconStyle',
            type: 'select',
            label: 'Expand Icon',
            options: [
                { label: 'Chevron', value: 'chevron-down' },
                { label: 'Plus/Minus', value: 'plus' },
                { label: 'Arrow', value: 'arrow-down' }
            ],
            default: 'chevron-down',
            tab: 'style'
        },
        {
            key: 'activeBgColor',
            type: 'color',
            label: 'Active Background',
            default: '#f8fafc',
            tab: 'style'
        },
        { type: 'header', label: 'Dimensions', tab: 'style' },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Medium', value: 'max-w-4xl' },
                { label: 'Large', value: 'max-w-5xl' },
                { label: 'Full', value: 'max-w-full' }
            ],
            default: 'max-w-4xl',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Small', value: 'py-12' },
                { label: 'Medium', value: 'py-20' },
                { label: 'Large', value: 'py-32' }
            ],
            default: 'py-20',
            tab: 'style'
        }
    ],
    defaultSettings: {
        title: 'Frequently Asked Questions',
        items: [
            { question: 'What is JA-Builder?', answer: 'A powerful visual page builder for modern web apps.' },
            { question: 'Is it responsive?', answer: 'Yes, it works perfectly on all devices.' },
            { question: 'Can I customize it?', answer: 'Absolutely! It is built with Vue and Tailwind CSS.' }
        ],
        width: 'max-w-5xl',
        padding: 'py-20',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
} as BlockDefinition;
