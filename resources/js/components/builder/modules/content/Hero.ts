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
 * Hero Module Definition
 */
const HeroModule: ModuleDefinition = {
    name: 'hero',
    title: 'Hero / Header',
    icon: 'Layout',
    category: 'content',

    children: null,

    defaults: {
        title: 'Build the Future of the Web\nwith Infinite Precision.',
        subtitle: 'The world\'s most flexible visual builder for creators who demand pixel-perfect execution.',
        content: '',
        // Buttons
        buttonText: 'Get Started for Free',
        buttonUrl: '#',
        button2Text: 'Learn More',
        button2Url: '#',
        showButton1: true,
        showButton2: true,
        // Layout
        minHeight: 700,
        alignment: 'center',
        contentWidth: 1200,
        // Background - Modified to support gradient keys
        gradientStart: '#4f46e5',
        gradientEnd: '#7c3aed',
        gradientDirection: 'to bottom right',
        overlayColor: 'rgba(0, 0, 0, 0.4)',
        overlayOpacity: 40,
        background: { color: 'transparent', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 120, bottom: 120, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },

        // Title Typography Defaults
        title_font_size: 72,
        title_font_weight: '900',
        title_color: '#ffffff',
        title_text_shadow: { horizontal: 0, vertical: 4, blur: 20, color: 'rgba(0,0,0,0.2)' },

        // Subtitle Typography Defaults
        subtitle_font_size: 22,
        subtitle_color: 'rgba(255, 255, 255, 0.9)',

        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' }
            }
        },
        animation_effect: 'animate-fade-up',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'text', label: 'Subtitle' },
                    { name: 'content', type: 'richtext', label: 'Content' }
                ]
            },
            {
                id: 'buttons',
                label: 'Buttons',
                fields: [
                    { name: 'showButton1', type: 'toggle', label: 'Show Primary Button' },
                    { name: 'buttonText', type: 'text', label: 'Primary Text', show_if: { field: 'showButton1', value: true } },
                    { name: 'buttonUrl', type: 'text', label: 'Primary URL', show_if: { field: 'showButton1', value: true } },
                    { name: 'showButton2', type: 'toggle', label: 'Show Secondary Button' },
                    { name: 'button2Text', type: 'text', label: 'Secondary Text', show_if: { field: 'showButton2', value: true } },
                    { name: 'button2Url', type: 'text', label: 'Secondary URL', show_if: { field: 'showButton2', value: true } }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Hero / Header')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'contentWidth', type: 'range', label: 'Content Max Width', min: 400, max: 1600, step: 50, unit: 'px' },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
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
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
                }))
            },
            {
                id: 'bodyTypography',
                label: 'Body Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `body_${f.name}`,
                    label: `Body ${f.label}`
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

export default HeroModule;
