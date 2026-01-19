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
    loopSettings,
    orderSettings,
    adminLabelSettings,
    linkSettings,
    layoutSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
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
    defaultChildren: ['accordion_item'],

    // Default settings
    defaults: {
        // Behavior
        allowMultiple: false,
        // Toggle Icon
        toggleIcon: 'plus',
        iconPosition: 'right',
        iconColor: '#333333',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        headerBackgroundColor: '#f7f7f7',
        contentBackgroundColor: '#ffffff',
        // Spacing
        itemGap: 24,
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        headerPadding: { top: 15, bottom: 15, left: 20, right: 20, unit: 'px' },
        contentPadding: { top: 20, bottom: 20, left: 20, right: 20, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 1, color: '#e5e5e5', style: 'solid' }
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
                label: 'Elements',
                fields: [
                    {
                        name: 'module_manager',
                        type: 'children_manager',
                        label: 'Accordion Items'
                    }
                ]
            },
            {
                id: 'toggleIcon',
                label: 'Toggle Icon',
                fields: [
                    {
                        name: 'toggleIcon',
                        type: 'icon',
                        label: 'Icon Style',
                        default: 'plus',
                        responsive: true
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Accordion')
        ],
        design: [
            layoutSettings,
            {
                id: 'icon_design',
                label: 'Icon',
                fields: [
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
                    },
                    {
                        name: 'iconSize',
                        type: 'range',
                        label: 'Icon Size',
                        min: 10,
                        max: 50,
                        unit: 'px',
                        default: 14,
                        responsive: true
                    }
                ]
            },
            {
                id: 'toggle_design',
                label: 'Toggle',
                fields: [
                    {
                        name: 'headerBackgroundColor',
                        type: 'color',
                        label: 'Header Background'
                    },
                    {
                        name: 'openHeaderBackgroundColor',
                        type: 'color',
                        label: 'Open Header Background'
                    }
                ]
            },
            {
                id: 'text_design',
                label: 'Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
                }))
            },
            {
                id: 'title_text_design',
                label: 'Title Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `header_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'closed_title_text_design',
                label: 'Closed Title Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `closed_header_${f.name}`,
                    label: `Closed Title ${f.label}`
                }))
            },
            {
                id: 'body_text_design',
                label: 'Body Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Body ${f.label}`
                }))
            },
            sizingSettings,
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            attributesSettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            visibilitySettings,
            transitionSettings,
            positionSettings,
            scrollEffectsSettings
        ]
    }
}
