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
 * Featured Image Module Definition
 */
const FeaturedImageModule: ModuleDefinition = {
    name: 'featuredimage',
    title: 'Featured Image',
    icon: 'Image',
    category: 'dynamic',

    children: null,

    defaults: {
        aspectRatio: '16:9',
        objectFit: 'cover',
        showCaption: false,
        caption: 'Featured image caption',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 24, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
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
                id: 'display', label: 'Display', fields: [
                    { name: 'showCaption', type: 'toggle', label: 'Show Caption', responsive: true },
                    { name: 'caption', type: 'text', label: 'Caption', responsive: true, show_if: { field: 'showCaption', value: true } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Featured Image')
        ],
        design: [
            {
                id: 'imageStyle',
                label: 'Image Style',
                fields: [
                    { name: 'aspectRatio', type: 'select', label: 'Aspect Ratio', responsive: true, options: [{ value: '16:9', label: '16:9' }, { value: '4:3', label: '4:3' }, { value: '3:2', label: '3:2' }, { value: '1:1', label: '1:1' }, { value: 'original', label: 'Original' }] },
                    { name: 'objectFit', type: 'select', label: 'Object Fit', responsive: true, options: [{ value: 'cover', label: 'Cover' }, { value: 'contain', label: 'Contain' }] }
                ]
            },
            {
                id: 'captionTypography',
                label: 'Caption Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `caption_${f.name}`,
                    label: `Caption ${f.label}`
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

export default FeaturedImageModule;
