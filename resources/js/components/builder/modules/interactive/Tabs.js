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
 * Tabs Module Definition
 */
export default {
    name: 'tabs',
    title: 'Tabs',
    icon: 'RectangleHorizontal',
    category: 'interactive',

    // Has tab children
    children: ['tab_item'],

    // Default settings
    defaults: {
        activeTab: '',
        // Layout
        tabPosition: 'top',
        tabAlignment: 'left',
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
                id: 'tabs',
                label: 'Tabs',
                fields: [
                    {
                        name: 'module_manager',
                        type: 'children_manager',
                        label: 'Tabs'
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
                        name: 'tabPosition',
                        type: 'select',
                        label: 'Tab Position',
                        responsive: true,
                        options: [
                            { value: 'top', label: 'Top' },
                            { value: 'left', label: 'Left' },
                            { value: 'bottom', label: 'Bottom' }
                        ]
                    },
                    {
                        name: 'tabAlignment',
                        type: 'buttonGroup',
                        label: 'Tab Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' },
                            { value: 'fill', label: 'Fill', icon: 'Maximize2' }
                        ]
                    }
                ]
            },
            {
                id: 'tabStyle',
                label: 'Tab Style',
                fields: [
                    {
                        name: 'tabBackgroundColor',
                        type: 'color',
                        label: 'Tab Background',
                        responsive: true
                    },
                    {
                        name: 'tabActiveBackgroundColor',
                        type: 'color',
                        label: 'Active Tab Background',
                        responsive: true
                    }
                ]
            },
            {
                id: 'tabTypography',
                label: 'Tab Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `tab_${f.name}`,
                    label: `Tab ${f.label}`
                }))
            },
            {
                id: 'tabActiveTypography',
                label: 'Active Tab Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `tab_active_${f.name}`,
                    label: `Active Tab ${f.label}`
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
