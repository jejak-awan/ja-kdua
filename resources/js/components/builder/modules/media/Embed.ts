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
 * Embed Module Definition
 */
const EmbedModule: ModuleDefinition = {
    name: 'embed',
    title: 'Embed',
    icon: 'Code2',
    category: 'media',

    children: null,

    defaults: {
        embedCode: '',
        embedUrl: '',
        embedType: 'code',
        // Sizing
        aspectRatio: '16:9',
        maxWidth: '100%',
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
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'embed',
                label: 'Embed',
                fields: [
                    { name: 'embedType', type: 'select', label: 'Type', options: [{ value: 'code', label: 'Embed Code' }, { value: 'url', label: 'URL (iframe)' }] },
                    { name: 'embedCode', type: 'textarea', label: 'Embed Code', show_if: { field: 'embedType', value: 'code' } },
                    { name: 'embedUrl', type: 'text', label: 'Embed URL', show_if: { field: 'embedType', value: 'url' } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Embed')
        ],
        design: [
            {
                id: 'display',
                label: 'Display',
                fields: [
                    { name: 'aspectRatio', type: 'select', label: 'Aspect Ratio', responsive: true, options: [{ value: '16:9', label: '16:9' }, { value: '4:3', label: '4:3' }, { value: '1:1', label: '1:1' }, { value: 'auto', label: 'Auto' }] }
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

export default EmbedModule;
