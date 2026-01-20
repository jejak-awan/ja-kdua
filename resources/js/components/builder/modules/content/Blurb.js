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
    linkSettings,
    loopSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Blurb Module Definition
 * Icon/Image + Title + Content layout
 */
export default {
    name: 'blurb',
    title: 'Blurb',
    icon: 'LayoutList',
    category: 'content',

    // No children
    children: null,

    // Default settings
    defaults: {
        // Content
        title: 'Your Title Here',
        content: 'Add your content here. This is a blurb module with icon, title, and text.',
        // Media Type
        mediaType: 'icon',
        icon: 'Star',
        image: '',
        // Layout
        iconPosition: 'top',
        alignment: 'center',
        // Icon Styling
        iconSize: 48,
        iconColor: '#2059ea',
        iconBackgroundColor: '',
        iconBackgroundShape: 'none',
        // Title Styling
        // Content Styling
        // Link
        linkUrl: '',
        linkTarget: '_self',
        // Spacing
        padding: { top: 20, bottom: 20, left: 20, right: 20, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
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
                id: 'media',
                label: 'Media',
                fields: [
                    {
                        name: 'mediaType',
                        type: 'buttonGroup',
                        label: 'Media Type',
                        options: [
                            { value: 'icon', label: 'Icon' },
                            { value: 'image', label: 'Image' },
                            { value: 'none', label: 'None' }
                        ]
                    },
                    {
                        name: 'icon',
                        type: 'icon',
                        label: 'Select Icon',
                        responsive: true
                    },
                    {
                        name: 'image',
                        type: 'upload',
                        label: 'Image',
                        responsive: true
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Blurb')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'iconPosition',
                        type: 'select',
                        label: 'Icon/Image Position',
                        responsive: true,
                        options: [
                            { value: 'top', label: 'Top' },
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ]
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Text Alignment',
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
                id: 'iconStyle',
                label: 'Icon Style',
                fields: [
                    {
                        name: 'iconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 24,
                        max: 128,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    },
                    {
                        name: 'iconBackgroundColor',
                        type: 'color',
                        label: 'Icon Background',
                        responsive: true
                    },
                    {
                        name: 'iconBackgroundShape',
                        type: 'select',
                        label: 'Icon Background Shape',
                        responsive: true,
                        options: [
                            { value: 'none', label: 'None' },
                            { value: 'circle', label: 'Circle' },
                            { value: 'square', label: 'Square' },
                            { value: 'rounded', label: 'Rounded' }
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
}
