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
    loopSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Text Module Definition
 */
const TextModule: ModuleDefinition = {
    name: 'text',
    title: 'Text',
    icon: 'Type',
    category: 'basic',

    children: null,

    defaults: {
        content: '<p>Your text goes here. Edit this text to add your own content.</p>',
        showTitle: false,
        title: '',
        titleTag: 'h2',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: '100%' // Divi Refinement
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'showTitle', type: 'toggle', label: 'Show Title', default: false },
                    { name: 'title', type: 'text', label: 'Title', show_if: { field: 'showTitle', value: true } },
                    {
                        name: 'titleTag',
                        type: 'select',
                        label: 'Title Tag',
                        options: [
                            { value: 'h1', label: 'H1' },
                            { value: 'h2', label: 'H2' },
                            { value: 'h3', label: 'H3' },
                            { value: 'h4', label: 'H4' }
                        ],
                        default: 'h2',
                        show_if: { field: 'showTitle', value: true }
                    },
                    { name: 'content', type: 'richtext', label: 'Content' }
                ]
            },
            linkSettings,
            backgroundSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Text')
        ],
        design: [
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

export default TextModule;
