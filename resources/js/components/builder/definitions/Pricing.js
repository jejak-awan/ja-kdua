import { CreditCard } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'pricing',
    label: 'Pricing Table',
    icon: CreditCard,
    description: 'Compare plans and pricing.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/PricingBlock.vue')),
    settings: [
        {
            key: 'title',
            type: 'text',
            label: 'Section Title',
            default: 'Simple Pricing',
            tab: 'content'
        },
        {
            key: 'items',
            type: 'repeater',
            label: 'Plans',
            itemLabel: 'Plan',
            fields: [
                { key: 'name', type: 'text', label: 'Plan Name', default: 'New Plan' },
                { key: 'price', type: 'text', label: 'Price', default: '$99' },
                { key: 'features', type: 'list', label: 'Features', default: ['Feature 1', 'Feature 2'] }, // Needs simple list support
                { key: 'buttonText', type: 'text', label: 'Button Text', default: 'Sign Up' }
            ],
            default: [
                { name: 'Starter', price: '$29', features: ['All Core Features', '5 Projects', 'Community Support'], buttonText: 'Start Free' },
                { name: 'Pro', price: '$99', features: ['Everything in Starter', 'Unlimited Projects', 'Priority Support', 'Advanced Analytics'], buttonText: 'Go Pro' },
                { name: 'Enterprise', price: '$299', features: ['Custom Solutions', 'Dedicated Manager', 'SLA', 'SSO'], buttonText: 'Contact Us' }
            ],
            tab: 'content'
        },
        {
            key: 'width',
            type: 'select',
            label: 'Width',
            options: [
                { label: 'Medium', value: 'max-w-6xl' },
                { label: 'Large', value: 'max-w-7xl' }
            ],
            default: 'max-w-6xl',
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
        },
    ],
    defaultSettings: {
        title: 'Simple Pricing',
        items: [
            { name: 'Starter', price: '$29', features: ['All Core Features', '5 Projects', 'Community Support'], buttonText: 'Start Free' },
            { name: 'Pro', price: '$99', features: ['Everything in Starter', 'Unlimited Projects', 'Priority Support', 'Advanced Analytics'], buttonText: 'Go Pro' },
            { name: 'Enterprise', price: '$299', features: ['Custom Solutions', 'Dedicated Manager', 'SLA', 'SSO'], buttonText: 'Contact Us' }
        ],
        width: 'max-w-6xl',
        padding: 'py-20',
        bgColor: 'transparent',
        radius: 'rounded-none',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
