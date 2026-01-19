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
 * Tab Item Module Definition
 */
export default {
    name: 'tab_item',
    title: 'Tab',
    icon: 'Maximize',
    category: 'internal',

    children: null,

    defaults: {
        title: 'New Tab',
        content: '<p>Tab content goes here...</p>',
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
                label: 'Tab Content',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'content', type: 'richtext', label: 'Content' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Tab')
        ],
        design: [
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
