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
 * Pricing Feature Module Definition
 */
const PricingFeatureModule: ModuleDefinition = {
    name: 'pricing_feature',
    title: 'Feature',
    icon: 'Check',
    category: 'internal',

    children: null,

    defaults: {
        text: 'New Feature',
        included: true,
        strikeThrough: false,
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'main',
                label: 'Feature',
                fields: [
                    { name: 'text', type: 'text', label: 'Feature Text', responsive: true },
                    { name: 'included', type: 'toggle', label: 'Included (Green Check)', responsive: true },
                    { name: 'strikeThrough', type: 'toggle', label: 'Excluded (Strikethrough)', responsive: true }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Feature')
        ],
        design: [
            {
                id: 'typography',
                label: 'Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default PricingFeatureModule;
