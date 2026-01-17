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
    cssSettings
} from '../commonSettings';

/**
 * Logo Module Definition
 */
export default {
    name: 'logo',
    title: 'Logo',
    icon: 'Image',
    category: 'basic',

    children: null,

    defaults: {
        image: '',
        altText: 'Logo',
        link: '/',
        openInNewTab: false,
        maxWidth: 200,
        height: 'auto',
        alignment: 'left',
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false }
    },

    settings: {
        content: [
            {
                id: 'logo',
                label: 'Logo',
                fields: [
                    { name: 'image', type: 'upload', label: 'Logo Image' },
                    { name: 'altText', type: 'text', label: 'Alt Text' },
                    { name: 'link', type: 'text', label: 'Link URL' },
                    { name: 'openInNewTab', type: 'toggle', label: 'Open in New Tab' }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'size',
                label: 'Size',
                fields: [
                    { name: 'maxWidth', type: 'range', label: 'Max Width', min: 50, max: 400, step: 10, unit: 'px', responsive: true },
                    {
                        name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
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
            cssSettings
        ]
    }
}
