import type { ModuleDefinition } from '@/types/builder';
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
const DividerModule: ModuleDefinition = {
    name: 'divider',
    title: 'Divider',
    icon: 'Minus',
    category: 'basic',

    children: null,

    defaults: {
        visible: true,
        pattern: 'classic', // classic, waves, zigzag, dots
        lineWeight: 2,
        lineColor: '#cccccc',
        lineWidth: '100%',
        alignment: 'center',
        use_gradient: false,
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
                label: 'Premium Line Style',
                fields: [
                    {
                        name: 'pattern', type: 'select', label: 'Line Pattern', responsive: true, options: [
                            { value: 'classic', label: 'Classic Solid' },
                            { value: 'waves', label: 'Modern Waves' },
                            { value: 'zigzag', label: 'Sharp Zig-zag' },
                            { value: 'dots', label: 'Elegant Dots' },
                            { value: 'dashed', label: 'Standard Dashed' }
                        ]
                    },
                    { name: 'lineWeight', type: 'range', label: 'Thickness', min: 1, max: 20, step: 1, unit: 'px', responsive: true },
                    { name: 'lineColor', type: 'color', label: 'Line Color', responsive: true, show_if: { field: 'use_gradient', value: false } },
                    { name: 'use_gradient', type: 'toggle', label: 'Enable Gradient Line' },
                    { name: 'gradient', type: 'gradient', label: 'Line Gradient', show_if: { field: 'use_gradient', value: true } },
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
};

export default DividerModule;
