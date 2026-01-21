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
 * Divider Module Definition
 */
export default {
    name: 'divider',
    title: 'Divider',
    icon: 'Minus',
    category: 'basic',

    children: null,

    defaults: {
        visible: true,
        lineStyle: 'solid',
        lineWeight: 1,
        lineColor: '#cccccc',
        lineWidth: '100%',
        alignment: 'center',
        padding: { top: 20, bottom: 20, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' }
    },

    settings: {
        content: [
            {
                id: 'divider',
                label: 'Divider',
                fields: [
                    { name: 'visible', type: 'toggle', label: 'Show Divider Line' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Divider')
        ],
        design: [
            {
                id: 'style',
                label: 'Style',
                fields: [
                    {
                        name: 'lineStyle', type: 'select', label: 'Line Style', responsive: true, options: [
                            { value: 'solid', label: 'Solid' },
                            { value: 'dashed', label: 'Dashed' },
                            { value: 'dotted', label: 'Dotted' },
                            { value: 'double', label: 'Double' }
                        ]
                    },
                    { name: 'lineWeight', type: 'range', label: 'Line Weight', min: 1, max: 20, step: 1, unit: 'px', responsive: true },
                    { name: 'lineColor', type: 'color', label: 'Line Color', responsive: true },
                    { name: 'lineWidth', type: 'text', label: 'Width (e.g., 100%, 200px)', responsive: true },
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
}
