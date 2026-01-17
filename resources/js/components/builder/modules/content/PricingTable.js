import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings,
    visibilitySettings,
    positionSettings,
    transitionSettings,
    cssSettings,
    typographySettings
} from '../commonSettings';

/**
 * Pricing Table Module Definition
 */
export default {
    name: 'pricingtable',
    title: 'Pricing Table',
    icon: 'CreditCard',
    category: 'content',

    children: ['pricing_feature'],

    defaults: {
        title: 'Professional',
        subtitle: 'Most Popular',
        price: '49',
        currency: '$',
        period: '/month',
        // Button
        buttonText: 'Get Started',
        buttonUrl: '#',
        // Featured
        featured: false,
        featuredLabel: 'Best Value',
        // Styling
        backgroundColor: '#ffffff',
        headerBackgroundColor: '#2059ea',
        buttonBackgroundColor: '#2059ea',
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 1, color: '#e0e0e0', style: 'solid' },
                top: { width: 1, color: '#e0e0e0', style: 'solid' },
                right: { width: 1, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 1, color: '#e0e0e0', style: 'solid' },
                left: { width: 1, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 4, blur: 12, spread: 0, color: 'rgba(0,0,0,0.1)', inset: false },
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'plan',
                label: 'Plan',
                fields: [
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Plan Title',
                        responsive: true
                    },
                    {
                        name: 'subtitle',
                        type: 'text',
                        label: 'Subtitle',
                        responsive: true
                    },
                    {
                        name: 'featured',
                        type: 'toggle',
                        label: 'Featured Plan',
                        responsive: true
                    },
                    {
                        name: 'featuredLabel',
                        type: 'text',
                        label: 'Featured Label',
                        responsive: true
                    }
                ]
            },
            {
                id: 'pricing',
                label: 'Pricing',
                fields: [
                    {
                        name: 'currency',
                        type: 'text',
                        label: 'Currency Symbol',
                        responsive: true
                    },
                    {
                        name: 'price',
                        type: 'text',
                        label: 'Price',
                        responsive: true
                    },
                    {
                        name: 'period',
                        type: 'text',
                        label: 'Period (e.g., /month)',
                        responsive: true
                    }
                ]
            },
            {
                id: 'features',
                label: 'Features',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Features' }
                ]
            },
            {
                id: 'button',
                label: 'Button',
                fields: [
                    {
                        name: 'buttonText',
                        type: 'text',
                        label: 'Button Text',
                        responsive: true
                    },
                    {
                        name: 'buttonUrl',
                        type: 'text',
                        label: 'Button URL',
                        responsive: true
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'headerStyle',
                label: 'Header Style',
                fields: [
                    {
                        name: 'headerBackgroundColor',
                        type: 'color',
                        label: 'Header Background',
                        responsive: true
                    },
                    {
                        name: 'headerTextColor',
                        type: 'color',
                        label: 'Header Text',
                        responsive: true
                    }
                ]
            },
            {
                id: 'pricingStyle',
                label: 'Pricing Style',
                fields: [
                    {
                        name: 'priceColor',
                        type: 'color',
                        label: 'Price Color',
                        responsive: true
                    }
                ]
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    {
                        name: 'buttonBackgroundColor',
                        type: 'color',
                        label: 'Button Background',
                        responsive: true
                    },
                    ...typographySettings.fields.map(f => ({
                        ...f,
                        name: `button_${f.name}`,
                        label: `Button ${f.label}`
                    }))
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
                }))
            },
            {
                id: 'priceTypography',
                label: 'Price Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `price_${f.name}`,
                    label: `Price ${f.label}`
                }))
            },
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            sizingSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            visibilitySettings,
            positionSettings,
            transitionSettings,
            cssSettings
        ]
    }
}
