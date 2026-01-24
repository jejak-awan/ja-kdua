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
 * Heading Module Definition
 */
const HeadingModule: ModuleDefinition = {
    name: 'heading',
    title: 'Heading',
    icon: 'Heading',
    category: 'basic',

    children: null,

    defaults: {
        text: 'Your Heading Here',
        subtitle: '',
        tag: 'h2',
        size: 'large',
        decoration: 'none',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false }
    },

    settings: {
        content: [
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    { name: 'text', type: 'text', label: 'Heading Text', responsive: true },
                    { name: 'subtitle', type: 'text', label: 'Subtitle', responsive: true },
                    {
                        name: 'tag', type: 'select', label: 'HTML Tag', options: [
                            { value: 'h1', label: 'H1' },
                            { value: 'h2', label: 'H2' },
                            { value: 'h3', label: 'H3' },
                            { value: 'h4', label: 'H4' },
                            { value: 'h5', label: 'H5' },
                            { value: 'h6', label: 'H6' }
                        ]
                    },
                    {
                        name: 'size', type: 'select', label: 'Size', responsive: true, options: [
                            { value: 'small', label: 'Small' },
                            { value: 'medium', label: 'Medium' },
                            { value: 'large', label: 'Large' },
                            { value: 'xlarge', label: 'Extra Large' },
                            { value: 'display', label: 'Display' }
                        ]
                    },
                    {
                        name: 'decoration', type: 'select', label: 'Decoration', options: [
                            { value: 'none', label: 'None' },
                            { value: 'underline', label: 'Underline' },
                            { value: 'highlight', label: 'Highlight' },
                            { value: 'gradient', label: 'Gradient Text' }
                        ]
                    }
                ]
            },
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Heading')
        ],
        design: [
            {
                id: 'typography',
                label: 'Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: f.name === 'text_align' ? 'alignment' : f.name
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

export default HeadingModule;
