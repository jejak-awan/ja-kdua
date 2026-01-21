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
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Medium', value: 'max-w-4xl' },
                { label: 'Large', value: 'max-w-5xl' }
            ],
            default: 'max-w-5xl',
            tab: 'style'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'Medium', value: 'py-20' },
                { label: 'Large', value: 'py-32' }
            ],
            default: 'py-20',
            tab: 'style'
        },
        {
            key: 'bgColor',
            type: 'color',
            label: 'Background Color',
            default: 'transparent',
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
};
