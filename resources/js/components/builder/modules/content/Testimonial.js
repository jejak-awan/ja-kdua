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
 * Testimonial Module Definition
 */
export default {
    name: 'testimonial',
    title: 'Testimonial',
    icon: 'Quote',
    category: 'content',

    children: null,

    defaults: {
        content: '"This is an amazing product! It has completely transformed how we work and has exceeded all our expectations."',
        authorName: 'John Doe',
        authorTitle: 'CEO, Company Inc.',
        authorImage: '',
        // Layout
        layout: 'default',
        alignment: 'center',
        // Quote Icon
        showQuoteIcon: true,
        quoteIconColor: '#e0e0e0',
        quoteIconSize: 48,
        // Background
        background: { color: '#f9f9f9', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'testimonial',
                label: 'Testimonial',
                fields: [
                    {
                        name: 'content',
                        type: 'textarea',
                        label: 'Testimonial Text',
                        responsive: true
                    },
                    {
                        name: 'authorName',
                        type: 'text',
                        label: 'Author Name',
                        responsive: true
                    },
                    {
                        name: 'authorTitle',
                        type: 'text',
                        label: 'Author Title/Company',
                        responsive: true
                    },
                    {
                        name: 'authorImage',
                        type: 'upload',
                        label: 'Author Photo'
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
                        label: 'Layout Style',
                        responsive: true,
                        options: [
                            { value: 'default', label: 'Default' },
                            { value: 'card', label: 'Card' },
                            { value: 'minimal', label: 'Minimal' }
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
                id: 'quoteIcon',
                label: 'Quote Icon',
                fields: [
                    {
                        name: 'showQuoteIcon',
                        type: 'toggle',
                        label: 'Show Quote Icon',
                        responsive: true
                    },
                    {
                        name: 'quoteIconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    },
                    {
                        name: 'quoteIconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 24,
                        max: 96,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
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
            {
                id: 'authorNameTypography',
                label: 'Author Name Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `author_name_${f.name}`,
                    label: `Author Name ${f.label}`
                }))
            },
            {
                id: 'authorTitleTypography',
                label: 'Author Title Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `author_title_${f.name}`,
                    label: `Author Title ${f.label}`
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
