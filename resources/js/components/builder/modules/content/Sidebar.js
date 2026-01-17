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
 * Sidebar Module Definition
 */
export default {
    name: 'sidebar',
    title: 'Sidebar',
    icon: 'PanelRight',
    category: 'content',

    children: ['sidebar_widget'],

    defaults: {
        children: [],
        showTitle: true,
        // Background
        background: { color: '#f9f9f9', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#e0e0e0', style: 'solid' },
                top: { width: 0, color: '#e0e0e0', style: 'solid' },
                right: { width: 0, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 0, color: '#e0e0e0', style: 'solid' },
                left: { width: 0, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'widgets',
                label: 'Widgets',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Widgets' },
                    { name: 'showTitle', type: 'toggle', label: 'Show Widget Titles', responsive: true }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'typography',
                label: 'Widget Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `widget_${f.name}`,
                    label: `Widget ${f.label}`
                }))
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
