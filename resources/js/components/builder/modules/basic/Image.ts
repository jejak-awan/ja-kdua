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
 * Image Module Definition
 */
const ImageModule: ModuleDefinition = {
    name: 'image',
    title: 'Image',
    icon: 'Image',
    category: 'basic',

    children: null,

    defaults: {
        url: '',
        alt: '',
        caption: '',
        linkUrl: '',
        linkNewTab: false,
        lightbox: false,
        objectFit: 'cover',
        alignment: 'center',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false }
    },

    settings: {
        content: [
            {
                id: 'image',
                label: 'Image',
                fields: [
                    { name: 'url', type: 'upload', label: 'Image', allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], responsive: true },
                    { name: 'alt', type: 'text', label: 'Alt Text', responsive: true },
                    { name: 'caption', type: 'text', label: 'Caption', responsive: true }
                ]
            },
            {
                id: 'link',
                label: 'Link',
                fields: [
                    { name: 'lightbox', type: 'toggle', label: 'Open in Lightbox' },
                    { name: 'linkUrl', type: 'text', label: 'Link URL' },
                    { name: 'linkNewTab', type: 'toggle', label: 'Open in New Tab' }
                ]
            },
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Image')
        ],
        design: [
            {
                id: 'imageStyle',
                label: 'Image Style',
                fields: [
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    {
                        name: 'objectFit', type: 'select', label: 'Object Fit', responsive: true, options: [
                            { value: 'cover', label: 'Cover' },
                            { value: 'contain', label: 'Contain' },
                            { value: 'fill', label: 'Fill' },
                            { value: 'none', label: 'None' }
                        ]
                    }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'overlayEnabled', type: 'toggle', label: 'Enable Hover Overlay', responsive: true },
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color', responsive: true }
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default ImageModule;
