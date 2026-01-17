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
    typographySettings
} from '../commonSettings';

/**
 * Share Buttons Module Definition
 */
export default {
    name: 'sharebuttons',
    title: 'Share Buttons',
    icon: 'Share2',
    category: 'content',

    children: ['share_network'],

    defaults: {
        style: 'filled',
        shape: 'rounded',
        size: 'medium',
        showLabels: false,
        label: 'Share:',
        // Layout
        alignment: 'left',
        gap: 8,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 16, bottom: 16, left: 0, right: 0, unit: 'px' },
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
                id: 'platforms',
                label: 'Platforms',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Networks' },
                    { name: 'showLabels', type: 'toggle', label: 'Show Labels', responsive: true },
                    { name: 'label', type: 'text', label: 'Prefix Label', responsive: true }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'style',
                label: 'Style',
                fields: [
                    { name: 'style', type: 'select', label: 'Style', responsive: true, options: [{ value: 'filled', label: 'Filled' }, { value: 'outline', label: 'Outline' }, { value: 'minimal', label: 'Minimal' }] },
                    { name: 'shape', type: 'select', label: 'Shape', responsive: true, options: [{ value: 'rounded', label: 'Rounded' }, { value: 'circle', label: 'Circle' }, { value: 'square', label: 'Square' }] },
                    { name: 'size', type: 'select', label: 'Size', responsive: true, options: [{ value: 'small', label: 'Small' }, { value: 'medium', label: 'Medium' }, { value: 'large', label: 'Large' }] }
                ]
            },
            {
                id: 'labelTypography',
                label: 'Label Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `label_${f.name}`,
                    label: `Label ${f.label}`
                }))
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [{ value: 'left', label: 'Left', icon: 'AlignLeft' }, { value: 'center', label: 'Center', icon: 'AlignCenter' }, { value: 'right', label: 'Right', icon: 'AlignRight' }] },
                    { name: 'gap', type: 'range', label: 'Gap', min: 4, max: 20, step: 2, unit: 'px', responsive: true }
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
