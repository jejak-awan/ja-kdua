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
 * Before/After Comparison Module Definition
 */
const BeforeAfterModule: ModuleDefinition = {
    name: 'beforeafter',
    title: 'Before/After',
    icon: 'SplitSquareHorizontal',
    category: 'media',

    children: null,

    defaults: {
        beforeImage: '',
        afterImage: '',
        beforeLabel: 'Before',
        afterLabel: 'After',
        showLabels: true,
        // Slider
        sliderPosition: 50,
        sliderColor: '#ffffff',
        sliderWidth: 4,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'images',
                label: 'Images',
                fields: [
                    { name: 'beforeImage', type: 'upload', label: 'Before Image' },
                    { name: 'afterImage', type: 'upload', label: 'After Image' },
                    { name: 'beforeLabel', type: 'text', label: 'Before Label' },
                    { name: 'afterLabel', type: 'text', label: 'After Label' },
                    { name: 'showLabels', type: 'toggle', label: 'Show Labels' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Before/After')
        ],
        design: [
            {
                id: 'slider',
                label: 'Slider',
                fields: [
                    { name: 'sliderPosition', type: 'range', label: 'Initial Position', min: 0, max: 100, step: 5, unit: '%', responsive: true },
                    { name: 'sliderColor', type: 'color', label: 'Slider Color' },
                    { name: 'sliderWidth', type: 'range', label: 'Slider Width', min: 2, max: 8, step: 1, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'labelTypography',
                label: 'Label Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `label_${f.name}`,
                    label: `Label ${f.label}`
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

export default BeforeAfterModule;
