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
 * Hero Module Definition
 */
export default {
    name: 'hero',
    title: 'Hero / Header',
    icon: 'Layout',
    category: 'content',

    children: null,

    defaults: {
        title: 'Hero Heading',
        subtitle: 'Engaging subtitle goes here.',
        content: 'Add some descriptive text here to elaborate on your value proposition.',
        // Buttons
        buttonText: 'Get Started',
        buttonUrl: '#',
        button2Text: 'Learn More',
        button2Url: '#',
        showButton1: true,
        showButton2: true,
        // Layout
        minHeight: 400,
        alignment: 'center', // text-align
        contentWidth: 800,
        // Background
        background: { color: '#3b5998', image: 'https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&w=1600&q=80', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 60, bottom: 60, left: 20, right: 20, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'text', label: 'Subtitle' },
                    { name: 'content', type: 'richtext', label: 'Content' }
                ]
            },
            {
                id: 'buttons',
                label: 'Buttons',
                fields: [
                    { name: 'showButton1', type: 'toggle', label: 'Show Primary Button' },
                    { name: 'buttonText', type: 'text', label: 'Primary Text', visible: s => s.showButton1 },
                    { name: 'buttonUrl', type: 'text', label: 'Primary URL', visible: s => s.showButton1 },
                    { name: 'showButton2', type: 'toggle', label: 'Show Secondary Button' },
                    { name: 'button2Text', type: 'text', label: 'Secondary Text', visible: s => s.showButton2 },
                    { name: 'button2Url', type: 'text', label: 'Secondary URL', visible: s => s.showButton2 }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Hero / Header')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'contentWidth', type: 'range', label: 'Content Max Width', min: 400, max: 1600, step: 50, unit: 'px' },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
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
                id: 'bodyTypography',
                label: 'Body Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `body_${f.name}`,
                    label: `Body ${f.label}`
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
