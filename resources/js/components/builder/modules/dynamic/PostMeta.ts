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
 * Post Meta Module Definition
 */
const PostMetaModule: ModuleDefinition = {
    name: 'postmeta',
    title: 'Post Meta',
    icon: 'Info',
    category: 'dynamic',

    children: null,

    defaults: {
        showAuthor: true,
        showDate: true,
        showCategory: true,
        showReadTime: true,
        showComments: false,
        separator: '•',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 24, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'display',
                label: 'Display',
                fields: [
                    { name: 'showAuthor', type: 'toggle', label: 'Show Author', responsive: true },
                    { name: 'showDate', type: 'toggle', label: 'Show Date', responsive: true },
                    { name: 'showCategory', type: 'toggle', label: 'Show Category', responsive: true },
                    { name: 'showReadTime', type: 'toggle', label: 'Show Read Time', responsive: true },
                    { name: 'showComments', type: 'toggle', label: 'Show Comments', responsive: true },
                    { name: 'separator', type: 'text', label: 'Separator', responsive: true }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Post Meta')
        ],
        design: [
            {
                id: 'typography',
                label: 'Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: f.name,
                    label: f.label
                }))
            },
            {
                id: 'linkTypography',
                label: 'Link Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `link_${f.name}`,
                    label: `Link ${f.label}`
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

export default PostMetaModule;
