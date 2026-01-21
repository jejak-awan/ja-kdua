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
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
} from '../commonSettings';

/**
 * Pricing Tables Module Definition
 */
export default {
    name: 'pricingtable', // Keep name for now to avoid breaking existing instances, but it represents 'Pricing Tables'
    title: 'Pricing Tables',
    icon: 'CreditCard',
    category: 'content',

    children: null,

    defaults: {
        items: [
            {
                title: 'Starter',
                price: '29',
                currency: '$',
                period: '/mo',
                buttonText: 'Get Started',
                buttonUrl: '#',
                features: 'Standard Support\n1 Business Website\n10GB Storage',
                isFeatured: false
            },
            {
                title: 'Professional',
                price: '99',
                currency: '$',
                period: '/mo',
                buttonText: 'Go Pro',
                buttonUrl: '#',
                features: 'Priority Support\n5 Business Websites\n50GB Storage\nCustom Domains',
                isFeatured: true
            }
        ],
        columns: 3,
        gap: 24,
        // Card Styling
        cardBackgroundColor: '#ffffff',
        featuredCardBackgroundColor: '#ffffff',
        accentColor: '#2059ea',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 40, bottom: 40, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'items',
                label: 'Pricing Plans',
                fields: [
                    {
                        name: 'items',
                        type: 'repeater',
                        label: 'Plans',
                        itemLabel: 'title',
                        fields: [
                            { name: 'title', type: 'text', label: 'Plan Title' },
                            { name: 'price', type: 'text', label: 'Price' },
                            { name: 'currency', type: 'text', label: 'Currency Symbol' },
                            { name: 'period', type: 'text', label: 'Period' },
                            { name: 'buttonText', type: 'text', label: 'Button Text' },
                            { name: 'buttonUrl', type: 'text', label: 'Button URL' },
                            { name: 'features', type: 'textarea', label: 'Features (one per line)' },
                            { name: 'isFeatured', type: 'toggle', label: 'Featured Plan' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Pricing Tables')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'columns', type: 'range', label: 'Columns', min: 1, max: 4, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 80, step: 4, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'style',
                label: 'Card Style',
                fields: [
                    { name: 'accentColor', type: 'color', label: 'Accent Color', responsive: true },
                    { name: 'cardBackgroundColor', type: 'color', label: 'Card Background', responsive: true },
                    { name: 'featuredCardBackgroundColor', type: 'color', label: 'Featured Card Background', responsive: true }
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
}
