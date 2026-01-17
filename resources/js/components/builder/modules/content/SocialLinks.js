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
    cssSettings
} from '../commonSettings';

/**
 * Social Links Module Definition
 */
export default {
    name: 'sociallinks',
    title: 'Social Links',
    icon: 'Share2',
    category: 'content',

    children: ['sociallink_item'],

    defaults: {
        // Style
        style: 'icon-only',
        size: 24,
        color: '#333333',
        hoverColor: '#2059ea',
        hoverBackgroundColor: '',
        // Layout
        alignment: 'center',
        gap: 16,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'links',
                label: 'Social Links',
                fields: [
                    {
                        name: 'module_manager',
                        type: 'children_manager',
                        label: 'Social Networks'
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'style',
                label: 'Style',
                fields: [
                    {
                        name: 'style',
                        type: 'select',
                        label: 'Display Style',
                        responsive: true,
                        options: [
                            { value: 'icon-only', label: 'Icon Only' },
                            { value: 'icon-circle', label: 'Icon with Circle' },
                            { value: 'icon-square', label: 'Icon with Square' }
                        ]
                    },
                    {
                        name: 'size',
                        type: 'range',
                        label: 'Icon Size',
                        min: 16,
                        max: 48,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 8,
                        max: 32,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'iconColors',
                label: 'Icon Colors',
                fields: [
                    {
                        name: 'color',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true
                    },
                    {
                        name: 'hoverColor',
                        type: 'color',
                        label: 'Hover Color',
                        responsive: true
                    },
                    {
                        name: 'hoverBackgroundColor',
                        type: 'color',
                        label: 'Hover Background',
                        responsive: true
                    }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
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
