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
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
} from '../commonSettings';

/**
 * Spacer Module Definition
 */
export default {
    name: 'spacer',
    title: 'Spacer',
    icon: 'ArrowUpDown',
    category: 'basic',

    children: null,

    defaults: {
        height: 50,
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' }
    },

    settings: {
        content: [
            {
                id: 'spacer',
                label: 'Spacer',
                fields: [
                    {
                        name: 'height',
                        type: 'range',
                        label: 'Height',
                        min: 0,
                        max: 500,
                        step: 10,
                        unit: 'px',
                        showInput: true,
                        responsive: true
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Spacer')
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
        advanced: [
            visibilitySettings,
            positionSettings,
            transitionSettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
}
