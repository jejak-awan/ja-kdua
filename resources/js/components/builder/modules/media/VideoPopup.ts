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
 * Video Popup Module Definition
 */
const VideoPopupModule: ModuleDefinition = {
    name: 'videopopup',
    title: 'Video Popup',
    icon: 'PlayCircle',
    category: 'media',

    children: null,

    defaults: {
        videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        thumbnailImage: '',
        // Button
        buttonStyle: 'icon',
        buttonText: 'Watch Video',
        // Styling
        iconSize: 80,
        iconColor: '#ffffff',
        iconBackgroundColor: 'rgba(32, 89, 234, 0.9)',
        overlayColor: 'rgba(0, 0, 0, 0.4)',
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
                id: 'video',
                label: 'Video',
                fields: [
                    { name: 'videoUrl', type: 'text', label: 'Video URL' },
                    { name: 'thumbnailImage', type: 'upload', label: 'Thumbnail Image' }
                ]
            },
            {
                id: 'button',
                label: 'Button',
                fields: [
                    { name: 'buttonStyle', type: 'select', label: 'Style', options: [{ value: 'icon', label: 'Icon Only' }, { value: 'text', label: 'With Text' }] },
                    { name: 'buttonText', type: 'text', label: 'Button Text', show_if: { field: 'buttonStyle', value: 'text' } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Video Popup')
        ],
        design: [
            {
                id: 'playButton',
                label: 'Play Button',
                fields: [
                    { name: 'iconSize', type: 'range', label: 'Size', min: 40, max: 120, step: 10, unit: 'px', responsive: true },
                    { name: 'iconColor', type: 'color', label: 'Color', responsive: true },
                    { name: 'iconBackgroundColor', type: 'color', label: 'Background', responsive: true }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color', responsive: true }
                ]
            },
            {
                id: 'buttonTypography',
                label: 'Button Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `button_${f.name}`,
                    label: `Button ${f.label}`
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

export default VideoPopupModule;
