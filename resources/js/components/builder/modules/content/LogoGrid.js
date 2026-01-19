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
    typographySettings
} from '../commonSettings';

/**
 * Logo Grid Module Definition
 */
export default {
    name: 'logogrid',
    title: 'Logo Grid',
    icon: 'Grid',
    category: 'content',

    children: ['logo_grid_item'],

    defaults: {
        title: 'Trusted by',
        showTitle: true,
        // Layout
        columns: 4,
        gap: 32,
        logoSize: 120,
        grayscale: true,
        hoverColor: true,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 32, bottom: 32, left: 0, right: 0, unit: 'px' },
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
                id: 'logos',
                label: 'Logos',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Logos' },
                    { name: 'showTitle', type: 'toggle', label: 'Show Title' },
                    { name: 'title', type: 'text', label: 'Title' }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'columns', type: 'range', label: 'Columns', min: 2, max: 8, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 16, max: 64, step: 8, unit: 'px' },
                    { name: 'logoSize', type: 'range', label: 'Logo Size', min: 60, max: 200, step: 10, unit: 'px' }
                ]
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'effects',
                label: 'Effects',
                fields: [
                    { name: 'grayscale', type: 'toggle', label: 'Grayscale' },
                    { name: 'hoverColor', type: 'toggle', label: 'Color on Hover' }
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
