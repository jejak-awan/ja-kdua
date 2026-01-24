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
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
} from '../commonSettings';

/**
 * Feature Box Module Definition
 */
const FeatureModule: ModuleDefinition = {
    name: 'feature',
    title: 'Feature Box',
    icon: 'Sparkles',
    category: 'content',

    children: null,

    defaults: {
        icon: 'Zap',
        title: 'Feature Title',
        description: 'A brief description of this amazing feature and what it does for the user.',
        // Layout
        layout: 'top',
        alignment: 'center',
        // Icon Styling
        iconSize: 48,
        iconColor: '#2059ea',
        iconBackgroundColor: 'rgba(32, 89, 234, 0.1)',
        iconBorderRadius: 50,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 0, color: '#e0e0e0', style: 'solid' }, top: { width: 0, color: '#e0e0e0', style: 'solid' }, right: { width: 0, color: '#e0e0e0', style: 'solid' }, bottom: { width: 0, color: '#e0e0e0', style: 'solid' }, left: { width: 0, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'icon', type: 'iconPicker', label: 'Icon' },
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'description', type: 'textarea', label: 'Description' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Feature Box')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'layout', type: 'select', label: 'Icon Position', options: [{ value: 'top', label: 'Top' }, { value: 'left', label: 'Left' }, { value: 'right', label: 'Right' }], responsive: true },
                    { name: 'alignment', type: 'buttonGroup', label: 'Alignment', options: [{ value: 'left', label: 'Left', icon: 'AlignLeft' }, { value: 'center', label: 'Center', icon: 'AlignCenter' }], responsive: true }
                ]
            },
            {
                id: 'icon',
                label: 'Icon Style',
                fields: [
                    { name: 'iconSize', type: 'range', label: 'Size', min: 24, max: 80, step: 4, unit: 'px', responsive: true },
                    { name: 'iconColor', type: 'color', label: 'Color', responsive: true },
                    { name: 'iconBackgroundColor', type: 'color', label: 'Background', responsive: true },
                    { name: 'iconBorderRadius', type: 'range', label: 'Roundness', min: 0, max: 50, step: 5, unit: '%', responsive: true }
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'descriptionTypography',
                label: 'Description Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `description_${f.name}`,
                    label: `Description ${f.label}`
                }))
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

export default FeatureModule;
