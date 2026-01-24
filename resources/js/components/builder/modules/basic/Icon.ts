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
    orderSettings,
    adminLabelSettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Icon Module Definition
 */
export default {
    name: 'icon',
    title: 'Icon',
    icon: 'Star',
    category: 'basic',

    children: null,

    defaults: {
        icon: 'Star',
        size: 48,
        iconColor: '#333333',
        shape: 'none',
        alignment: 'center',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false }
    },

    settings: {
        content: [
            {
                id: 'icon',
                label: 'Icon',
                fields: [
                    { name: 'icon', type: 'icon', label: 'Select Icon', responsive: true },
                    { name: 'size', type: 'range', label: 'Size', min: 16, max: 128, step: 4, unit: 'px', responsive: true },
                    { name: 'iconColor', type: 'color', label: 'Icon Color', responsive: true },
                    {
                        name: 'shape', type: 'select', label: 'Shape', options: [
                            { value: 'none', label: 'None' },
                            { value: 'circle', label: 'Circle' },
                            { value: 'rounded', label: 'Rounded' },
                            { value: 'square', label: 'Square' }
                        ]
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Icon')
        ],
        design: [
            {
                id: 'alignment',
                label: 'Alignment',
                fields: [
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
}
