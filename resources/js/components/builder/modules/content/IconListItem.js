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
 * Icon List Item Module Definition
 */
export default {
    name: 'iconlist_item',
    title: 'Icon List Item',
    icon: 'List',
    category: 'internal', // Internal category hides it from main library if not allowed

    children: null,

    defaults: {
        icon: 'Check',
        text: 'List Item',
        link_url: '',
        link_target: '_self',
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
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'text', type: 'text', label: 'Text' },
                    { name: 'icon', type: 'text', label: 'Icon (Lucide)' }
                ]
            },
            {
                id: 'link',
                label: 'Link',
                fields: [
                    { name: 'link_url', type: 'text', label: 'URL' },
                    {
                        name: 'link_target',
                        type: 'select',
                        label: 'Target',
                        options: [
                            { value: '_self', label: 'Same Window' },
                            { value: '_blank', label: 'New Window' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Item')
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
