import { HelpCircle } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'faq',
    label: 'FAQ / Accordion',
    icon: HelpCircle,
    description: 'List of questions and answers in list or accordion layout.',
    component: defineAsyncComponent(() => import('@/shared/blocks/FAQBlock.vue')),
    settings: [
        {
            key: 'items',
            type: 'repeater',
            label: 'FAQ Items',
            fields: [
                { key: 'question', type: 'text', label: 'Question', default: 'What is your question?' },
                { key: 'answer', type: 'textarea', label: 'Answer', default: 'Here is the answer.' }
            ],
            default: [
                { question: 'Is this responsive?', answer: 'Yes, it works on all devices.' },
                { question: 'Can I change the layout?', answer: 'Yes, you can choose between List and Accordion mode.' }
            ]
        },
        {
            key: 'layout',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Accordion', value: 'accordion' },
                { label: 'List', value: 'list' }
            ],
            default: 'accordion'
        },
        { key: 'allowMultiple', type: 'boolean', label: 'Allow Multiple Open', default: false },
        { key: 'itemBorderColor', type: 'color', label: 'Item Border Color', default: '#e2e8f0' },
        { key: 'iconColor', type: 'color', label: 'Icon Color', default: '' }
    ],
    defaultSettings: {
        items: [
            { question: 'New Question', answer: 'New Answer' }
        ],
        layout: 'accordion',
        allowMultiple: false,
        padding: 'py-8',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
