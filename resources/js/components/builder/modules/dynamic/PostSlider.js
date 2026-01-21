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
 * Post Slider Module Definition
 */
export default {
    name: 'postslider',
    title: 'Post Slider',
    icon: 'Newspaper',
    category: 'dynamic',

    children: null,

    defaults: {
        postsPerSlide: 1,
        totalPosts: 5,
        title: 'Featured Posts',
        // Navigation
        showArrows: true,
        showDots: true,
        autoplay: true,
        autoplaySpeed: 5000,
        // Display
        showImage: true,
        showExcerpt: true,
        showMeta: true,
        // Layout
        height: 500,
        overlayEnabled: true,
        overlayColor: 'rgba(0,0,0,0.4)',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'settings',
                label: 'Settings',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true }
                ]
            },
            {
                id: 'query',
                label: 'Query',
                fields: [
                    { name: 'totalPosts', type: 'range', label: 'Number of Posts', min: 1, max: 10, step: 1, responsive: true }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    { name: 'showImage', type: 'toggle', label: 'Show Image' },
                    { name: 'showExcerpt', type: 'toggle', label: 'Show Excerpt' },
                    { name: 'showMeta', type: 'toggle', label: 'Show Meta' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Post Slider')
        ],
        design: [
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    { name: 'showArrows', type: 'toggle', label: 'Show Arrows' },
                    { name: 'showDots', type: 'toggle', label: 'Show Dots' },
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay' },
                    { name: 'autoplaySpeed', type: 'range', label: 'Autoplay Speed', min: 2000, max: 10000, step: 500, unit: 'ms', responsive: true }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'height', type: 'range', label: 'Height', min: 300, max: 800, step: 50, unit: 'px', responsive: true },
                    { name: 'overlayEnabled', type: 'toggle', label: 'Enable Overlay' },
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color' }
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
                id: 'metaTypography',
                label: 'Meta Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `meta_${f.name}`,
                    label: `Meta ${f.label}`
                }))
            },
            {
                id: 'excerptTypography',
                label: 'Excerpt Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `excerpt_${f.name}`,
                    label: `Excerpt ${f.label}`
                }))
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background' },
                    ...typographySettings.fields.map(f => ({
                        ...f,
                        name: `button_${f.name}`,
                        label: `Button ${f.label}`
                    }))
                ]
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
