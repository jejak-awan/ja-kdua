import {
    adminLabelSettings,
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings
} from '../commonSettings';

/**
 * Social Link Item Module Definition
 */
export default {
    name: 'sociallink_item',
    title: 'Social Link',
    icon: 'Share2',
    category: 'internal',

    children: null,

    defaults: {
        network: 'facebook',
        url: '#',
        // Optional override colors
        useCustomColor: false,
        iconColor: '#ffffff', // if custom
        backgroundColor: '#3b5998', // if custom
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
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'main',
                label: 'Social Network',
                fields: [
                    {
                        name: 'network',
                        type: 'select',
                        label: 'Network',
                        responsive: true,
                        options: [
                            { value: 'facebook', label: 'Facebook', icon: 'Facebook' },
                            { value: 'twitter', label: 'Twitter / X', icon: 'Twitter' },
                            { value: 'instagram', label: 'Instagram', icon: 'Instagram' },
                            { value: 'linkedin', label: 'LinkedIn', icon: 'Linkedin' },
                            { value: 'youtube', label: 'YouTube', icon: 'Youtube' },
                            { value: 'pinterest', label: 'Pinterest' },
                            { value: 'tiktok', label: 'TikTok' },
                            { value: 'email', label: 'Email', icon: 'Mail' },
                            { value: 'phone', label: 'Phone', icon: 'Phone' },
                            { value: 'rss', label: 'RSS', icon: 'Rss' },
                            { value: 'custom', label: 'Custom' }
                        ]
                    },
                    { name: 'url', type: 'text', label: 'Link URL', responsive: true }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Network')
        ],
        design: [
            {
                id: 'colors',
                label: 'Custom Colors',
                fields: [
                    { name: 'useCustomColor', type: 'toggle', label: 'Use Custom Colors', responsive: true },
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color',
                        responsive: true,
                        visible: (settings) => settings.useCustomColor
                    },
                    {
                        name: 'backgroundColor',
                        type: 'color',
                        label: 'Background Color',
                        responsive: true,
                        visible: (settings) => settings.useCustomColor
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
        advanced: []
    }
}
