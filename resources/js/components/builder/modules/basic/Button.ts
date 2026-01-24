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
    linkSettings,
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
const ButtonModule: ModuleDefinition = {
    name: 'button',
    title: 'Button',
    icon: 'MousePointer',
    category: 'basic',

    children: null,

    defaults: {
        text: 'Click Here',
        link_url: '#',
        link_target: '_self',
        alignment: 'left',
        variant: 'solid', // solid, outline, ghost, glass, gradient
        color: '#ffffff',
        use_icon: false,
        icon: 'ArrowRight',
        icon_position: 'right',
        icon_size: 16,
        hover_effect: 'lift', // none, lift, zoom, pulse, shine, sweep
        background: { color: '#111827', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 12, bottom: 12, left: 30, right: 30, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: 'auto'
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'text', type: 'text', label: 'Button Text', responsive: true },
                    linkSettings,
                    {
                        name: 'use_icon',
                        type: 'toggle',
                        label: 'Show Icon',
                        default: false
                    },
                    {
                        name: 'icon',
                        type: 'icon',
                        label: 'Select Icon',
                        show_if: { field: 'use_icon', value: true }
                    },
                    {
                        name: 'icon_position',
                        type: 'buttonGroup',
                        label: 'Icon Position',
                        options: [
                            { label: 'Left', value: 'left', icon: 'AlignLeft' },
                            { label: 'Right', value: 'right', icon: 'AlignRight' }
                        ],
                        default: 'right',
                        show_if: { field: 'use_icon', value: true }
                    }
                ]
            },
            adminLabelSettings('Button')
        ],
        design: [
            {
                id: 'variants',
                label: 'Presets & Variants',
                fields: [
                    {
                        name: 'variant',
                        type: 'select',
                        label: 'Button Style',
                        options: [
                            { label: 'Solid', value: 'solid' },
                            { label: 'Outline', value: 'outline' },
                            { label: 'Ghost', value: 'ghost' },
                            { label: 'Glassmorphism', value: 'glass' },
                            { label: 'Gradient', value: 'gradient' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'hover_effect',
                        type: 'select',
                        label: 'Hover Animation',
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Lift Up', value: 'lift' },
                            { label: 'Zoom In', value: 'zoom' },
                            { label: 'Pulse Glow', value: 'pulse' },
                            { label: 'Shine Flash', value: 'shine' },
                            { label: 'Slide Sweep', value: 'sweep' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'alignment',
                label: 'Alignment',
                fields: [
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Button Alignment',
                        options: [
                            { value: 'left', icon: 'AlignLeft' },
                            { value: 'center', icon: 'AlignCenter' },
                            { value: 'right', icon: 'AlignRight' }
                        ],
                        responsive: true
                    }
                ]
            },
            {
                id: 'premium_styles',
                label: 'Premium Extras',
                fields: [
                    { name: 'enable_glass', type: 'toggle', label: 'Enable Glass Effect', show_if: { field: 'variant', value: 'glass' } },
                    { name: 'glass_blur', type: 'range', label: 'Glass Blur', min: 0, max: 20, unit: 'px', show_if: { field: 'variant', value: 'glass' } },
                    { name: 'glass_opacity', type: 'range', label: 'Glass Opacity', min: 0, max: 100, unit: '%', show_if: { field: 'variant', value: 'glass' } },
                    { name: 'use_gradient', type: 'toggle', label: 'Enable Gradient', show_if: { field: 'variant', value: 'gradient' } },
                    { name: 'gradient', type: 'gradient', label: 'Button Gradient', show_if: { field: 'variant', value: 'gradient' } }
                ]
            },
            {
                id: 'typography',
                label: 'Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: f.name === 'text_align' ? 'button_text_align' : f.name
                }))
            },
            backgroundSettings,
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
};

export default ButtonModule;
