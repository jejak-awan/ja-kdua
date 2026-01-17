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
    loopSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings
} from '../commonSettings';

/**
 * Row Module Definition
 */
export default {
    name: 'row',
    title: 'Row',
    icon: 'Columns',
    category: 'structure',

    // Allowed children
    children: ['column'],

    // Parent constraint
    parent: ['section'],

    // Default settings
    defaults: {
        columns: '1-1', // Column structure: 1, 1-1, 1-1-1, 2-1, 1-2, etc
        gutterWidth: 30,
        equalizeColumns: false,
        maxWidth: '',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 30, bottom: 30, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1',
        link_target: '_self'
    },

    // Settings panel configuration
    settings: {
        content: [
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    {
                        name: 'column_manager',
                        type: 'children_manager',
                        label: ''
                    }
                ]
            },
            {
                id: 'link',
                label: 'Link',
                fields: [
                    {
                        name: 'link_url',
                        type: 'text',
                        label: 'Row Link URL',
                        description: 'Input your destination URL here.'
                    },
                    {
                        name: 'link_target',
                        type: 'select',
                        label: 'Row Link Target',
                        options: [
                            { label: 'In The Current Tab', value: '_self' },
                            { label: 'In The New Tab', value: '_blank' }
                        ],
                        default: '_self'
                    }
                ]
            },
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Row')
        ],
        design: [
            {
                id: 'sizing',
                label: 'Sizing',
                fields: [
                    {
                        name: 'maxWidth',
                        type: 'number',
                        label: 'Max Width',
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'gutterWidth',
                        type: 'range',
                        label: 'Gutter Width',
                        min: 0,
                        max: 80,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'equalizeColumns',
                        type: 'toggle',
                        label: 'Equalize Column Heights'
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
            loopSettings,
            cssSettings
        ]
    }
}
