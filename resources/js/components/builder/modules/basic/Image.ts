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
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Image Module Definition
 */
const ImageModule: ModuleDefinition = {
    name: 'image',
    title: 'Image',
    icon: 'Image',
    category: 'basic',

    children: null,

    defaults: {
        src: '',
        alt: '',
        title: '',
        showCaption: false,
        caption: '',
        alignment: 'left',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        width: '100%', // Divi refinement
        forceFullwidth: false
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'src', type: 'image', label: 'Image Source' },
                    { name: 'alt', type: 'text', label: 'Alt Text' },
                    { name: 'title', type: 'text', label: 'Title Text' },
                    { name: 'showCaption', type: 'toggle', label: 'Show Caption', default: false },
                    { name: 'caption', type: 'text', label: 'Caption', show_if: { field: 'showCaption', value: true } },
                    { name: 'link_url', type: 'text', label: 'Link URL' },
                    { name: 'link_target', type: 'select', label: 'Link Target', options: [{ label: 'Same Window', value: '_self' }, { label: 'New Tab', value: '_blank' }], default: '_self' }
                ]
            },
            adminLabelSettings('Image')
        ],
        design: [
            {
                id: 'alignment',
                label: 'Alignment',
                fields: [
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Image Alignment',
                        options: [
                            { value: 'left', icon: 'AlignLeft' },
                            { value: 'center', icon: 'AlignCenter' },
                            { value: 'right', icon: 'AlignRight' }
                        ],
                        responsive: true
                    },
                    { name: 'forceFullwidth', type: 'toggle', label: 'Force Fullwidth', default: false }
                ]
            },
            {
                id: 'styling_presets',
                label: 'Premium Effects',
                fields: [
                    {
                        name: 'mask_shape',
                        type: 'select',
                        label: 'Image Mask Shape',
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Circle', value: 'circle' },
                            { label: 'Squircle', value: 'squircle' },
                            { label: 'Diamond', value: 'diamond' },
                            { label: 'Triangle', value: 'triangle' },
                            { label: 'Organic Blob', value: 'blob1' }
                        ],
                        responsive: true
                    },
                    {
                        name: 'hover_effect',
                        type: 'select',
                        label: 'Hover Animation',
                        options: [
                            { label: 'None', value: 'none' },
                            { label: 'Zoom In', value: 'zoom' },
                            { label: 'Tilt 3D', value: 'tilt' },
                            { label: 'Lift & Shadow', value: 'lift' },
                            { label: 'Grayscale to Color', value: 'reveal' }
                        ],
                        responsive: true
                    }
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default ImageModule;
