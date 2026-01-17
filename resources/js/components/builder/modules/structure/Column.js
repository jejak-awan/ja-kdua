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
 * Column Module Definition
 */
export default {
    name: 'column',
    title: 'Column',
    icon: 'Square',
    category: 'structure',

    // Allowed children - any content module
    children: ['*'],

    // Parent constraint
    parent: ['row'],

    // Default settings
    defaults: {
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 15, bottom: 15, left: 15, right: 15, unit: 'px' },
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
        verticalAlignment: 'top',
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    // Settings panel configuration
    settings: {
        content: [
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    {
                        name: 'module_manager',
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
                        label: 'Column Link URL',
                        description: 'Input your destination URL here.'
                    },
                    {
                        name: 'link_target',
                        type: 'select',
                        label: 'Column Link Target',
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
            adminLabelSettings('Column')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'verticalAlignment',
                        type: 'buttonGroup',
                        label: 'Vertical Alignment',
                        responsive: true,
                        options: [
                            { value: 'top', label: 'Top', icon: 'AlignStartVertical' },
                            { value: 'center', label: 'Center', icon: 'AlignCenterVertical' },
                            { value: 'bottom', label: 'Bottom', icon: 'AlignEndVertical' }
                        ]
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
