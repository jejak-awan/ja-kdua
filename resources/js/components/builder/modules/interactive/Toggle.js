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
 * Toggle Module Definition (Single collapsible)
 */
export default {
    name: 'toggle',
    title: 'Toggle',
    icon: 'ChevronDown',
    category: 'interactive',

    children: null,

    defaults: {
        title: 'Toggle Title',
        content: 'Toggle content goes here. This content is hidden by default and shows when the toggle is clicked.',
        defaultOpen: false,
        // Toggle Icon
        toggleIcon: 'chevron',
        iconPosition: 'right',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 4, tr: 4, bl: 4, br: 4, linked: true },
            styles: {
                all: { width: 1, color: '#e0e0e0', style: 'solid' },
                top: { width: 1, color: '#e0e0e0', style: 'solid' },
                right: { width: 1, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 1, color: '#e0e0e0', style: 'solid' },
                left: { width: 1, color: '#e0e0e0', style: 'solid' }
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
                        label: 'Content'
                    },
                    {
                        name: 'defaultOpen',
                        type: 'toggle',
                        label: 'Open by Default',
                        responsive: true
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'toggleIcon',
                label: 'Toggle Icon',
                fields: [
                    {
                        name: 'toggleIcon',
                        type: 'select',
                        label: 'Icon Style',
                        options: [
                            { value: 'chevron', label: 'Chevron' },
                            { value: 'plus', label: 'Plus/Minus' },
                            { value: 'none', label: 'None' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'iconPosition',
                        type: 'buttonGroup',
                        label: 'Icon Position',
                        options: [
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    }
                ]
            },
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
                        name: 'headerPadding',
                        type: 'spacing',
                        label: 'Header Padding',
                        responsive: true
                    }
                ]
            },
            {
                id: 'headerTypography',
                label: 'Header Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `header_${f.name}`,
                    label: `Header ${f.label}`
                }))
            },
            {
                id: 'contentStyle',
                label: 'Content Style',
                fields: [
                    {
                        name: 'contentBackgroundColor',
                        type: 'color',
                        label: 'Content Background',
                        responsive: true
                    },
                    {
                        name: 'contentPadding',
                        type: 'spacing',
                        label: 'Content Padding',
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
