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
 * Portfolio Module Definition
 */
export default {
    name: 'portfolio',
    title: 'Portfolio',
    icon: 'Grid3x3',
    category: 'dynamic',

    children: null,

    defaults: {
        itemsPerPage: 9,
        columns: 3,
        // Filter
        showFilter: true,
        filterStyle: 'buttons',
        // Display
        showTitle: true,
        showCategory: true,
        hoverEffect: 'overlay',
        // Layout
        gap: 20,
        imageAspectRatio: '1:1',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'query',
                label: 'Query',
                fields: [
                    { name: 'itemsPerPage', type: 'range', label: 'Items Per Page', min: 3, max: 24, step: 3 }
                ]
            },
            {
                id: 'filter',
                label: 'Filter',
                fields: [
                    { name: 'showFilter', type: 'toggle', label: 'Show Filter' },
                    { name: 'filterStyle', type: 'select', label: 'Filter Style', options: [{ value: 'buttons', label: 'Buttons' }, { value: 'dropdown', label: 'Dropdown' }] }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    { name: 'showTitle', type: 'toggle', label: 'Show Title' },
                    { name: 'showCategory', type: 'toggle', label: 'Show Category' }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'columns', type: 'range', label: 'Columns', min: 2, max: 5, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 40, step: 4, unit: 'px', responsive: true },
                    { name: 'imageAspectRatio', type: 'select', label: 'Aspect Ratio', options: [{ value: '1:1', label: 'Square' }, { value: '4:3', label: '4:3' }, { value: '16:9', label: '16:9' }] }
                ]
            },
            {
                id: 'hover',
                label: 'Hover Effect',
                fields: [
                    { name: 'hoverEffect', type: 'select', label: 'Hover Effect', options: [{ value: 'overlay', label: 'Overlay' }, { value: 'zoom', label: 'Zoom' }, { value: 'grayscale', label: 'Grayscale' }] },
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color' }
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
                id: 'categoryTypography',
                label: 'Category Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `category_${f.name}`,
                    label: `Category ${f.label}`
                }))
            },
            {
                id: 'filterTypography',
                label: 'Filter Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `filter_${f.name}`,
                    label: `Filter ${f.label}`
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
