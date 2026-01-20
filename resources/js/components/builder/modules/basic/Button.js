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
    linkSettings,
    loopSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Button Module Definition
 */
export default {
    name: 'button',
    title: 'Button',
    icon: 'MousePointer',
    category: 'basic',

    children: null,

    defaults: {
        text: 'Click Here',
        url: '#',
        target: '_self',
        alignment: 'left',
        size: 'medium',
        fullWidth: false,
        background: { color: '#2059ea', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 12, bottom: 12, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 4, tr: 4, bl: 4, br: 4, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false }
    },

    settings: {
        content: [
            {
                id: 'button',
                label: 'Button',
                fields: [
                    { name: 'text', type: 'text', label: 'Button Text', responsive: true },
                    { name: 'link_url', type: 'text', label: 'Link URL', responsive: true },
                    {
                        name: 'link_target', type: 'select', label: 'Link Target', options: [
                            { value: '_self', label: 'In The Current Tab' },
                            { value: '_blank', label: 'In The New Tab' }
                        ]
                    }
                ]
            },
            {
                id: 'icon',
                label: 'Icon',
                fields: [
                    { name: 'icon', type: 'icon', label: 'Select Icon', responsive: true },
                    {
                        name: 'iconPosition', type: 'buttonGroup', label: 'Icon Position', options: [
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Button')
        ],
        design: [
            {
                id: 'typography',
                label: 'Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: f.name === 'text_align' ? 'alignment' : f.name
                }))
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    {
                        name: 'size', type: 'select', label: 'Size', options: [
                            { value: 'small', label: 'Small' },
                            { value: 'medium', label: 'Medium' },
                            { value: 'large', label: 'Large' }
                        ],
                        responsive: true
                    },
                    { name: 'fullWidth', type: 'toggle', label: 'Full Width', responsive: true }
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
}
