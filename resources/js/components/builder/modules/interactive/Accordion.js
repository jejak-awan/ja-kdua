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
 * Accordion Module Definition
 */
export default {
    name: 'accordion',
    title: 'Accordion',
    icon: 'List',
    category: 'interactive',

    children: ['accordion_item'],

    // Default settings
    defaults: {
        // Behavior
        allowMultiple: false,
        // Toggle Icon
        toggleIcon: 'chevron',
        iconPosition: 'right',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        itemGap: 8,
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
        // Animation
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'items',
                label: 'Items',
                fields: [
                    {
                        name: 'module_manager',
                        type: 'children_manager',
                        label: 'Accordion Items'
                    }
                ]
            },
            {
                id: 'behavior',
                label: 'Behavior',
                fields: [
                    {
                        name: 'allowMultiple',
                        type: 'toggle',
                        label: 'Allow Multiple Open'
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
                            { value: 'arrow', label: 'Arrow' },
                            { value: 'none', label: 'None' }
                        ]
                    },
                    {
                        name: 'iconPosition',
                        type: 'buttonGroup',
                        label: 'Icon Position',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ]
                    },
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color'
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
                        label: 'Header Background'
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
                        label: 'Content Background'
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
            {
                id: 'spacing_custom',
                label: 'Module Spacing',
                fields: [
                    {
                        name: 'itemGap',
                        type: 'range',
                        label: 'Gap Between Items',
                        min: 0,
                        max: 48,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
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
            cssSettings
        ]
    }
}
