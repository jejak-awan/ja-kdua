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
 * CTA (Call to Action) Module Definition
 */
export default {
    name: 'cta',
    title: 'Call to Action',
    icon: 'Megaphone',
    category: 'content',

    // No children
    children: null,

    // Default settings
    defaults: {
        // Content
        title: 'Take Action Today',
        content: 'This is a call to action block. Add compelling text to encourage users to take the next step.',
        // Button
        buttonText: 'Get Started',
        buttonUrl: '#',
        buttonTarget: '_self',
        // Layout
        layout: 'stacked',
        alignment: 'center',
        // Colors
        background: { color: '#2059ea', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        textColor: '#ffffff',
        buttonBackgroundColor: '#ffffff',
        buttonTextColor: '#2059ea',
        // Title
        titleFontSize: 28,
        contentFontSize: 16,
        // Spacing
        padding: { top: 40, bottom: 40, left: 40, right: 40, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        // Animation
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    // Settings panel configuration
    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title',
                        responsive: true
                    },
                    {
                        name: 'content',
                        type: 'textarea',
                        label: 'Content',
                        responsive: true
                    }
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
                    },
                    {
                        name: 'buttonTarget',
                        type: 'select',
                        label: 'Link Target',
                        options: [
                            { value: '_self', label: 'Same Window' },
                            { value: '_blank', label: 'New Tab' }
                        ]
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout',
                        responsive: true,
                        options: [
                            { value: 'stacked', label: 'Stacked (Vertical)' },
                            { value: 'inline', label: 'Inline (Horizontal)' }
                        ]
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
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
                        label: 'Button Background'
                    },
                    {
                        name: 'buttonTextColor',
                        type: 'color',
                        label: 'Button Text'
                    }
                ]
            },
            {
                id: 'buttonTypography',
                label: 'Button Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `button_${f.name}`,
                    label: `Button ${f.label}`
                }))
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
                id: 'contentTypography',
                label: 'Content Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Content ${f.label}`
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
