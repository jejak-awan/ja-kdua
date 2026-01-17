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
 * Filterable Portfolio Module Definition (Divi 5 Reference)
 */
export default {
    name: 'filterableportfolio',
    title: 'Filterable Portfolio',
    icon: 'Filter',
    category: 'dynamic',

    children: null,

    defaults: {
        postsCount: 12,
        columns: 4,
        // Filter
        showFilter: true,
        filterStyle: 'tabs',
        filterAlignment: 'center',
        allLabel: 'All',
        // Display
        showTitle: true,
        showCategories: true,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Styling
        hoverEffect: 'zoom',
        overlayColor: 'rgba(32, 89, 234, 0.8)',
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'postsCount', type: 'range', label: 'Number of Projects', min: 4, max: 24, step: 4 },
                    { name: 'showTitle', type: 'toggle', label: 'Show Title' },
                    { name: 'showCategories', type: 'toggle', label: 'Show Categories' }
                ]
            },
            {
                id: 'filter',
                label: 'Filter',
                fields: [
                    { name: 'showFilter', type: 'toggle', label: 'Show Filter' },
                    { name: 'filterStyle', type: 'select', label: 'Style', options: [{ value: 'tabs', label: 'Tabs' }, { value: 'buttons', label: 'Buttons' }, { value: 'dropdown', label: 'Dropdown' }] },
                    { name: 'filterAlignment', type: 'buttonGroup', label: 'Alignment', options: [{ value: 'left', label: 'Left', icon: 'AlignLeft' }, { value: 'center', label: 'Center', icon: 'AlignCenter' }, { value: 'right', label: 'Right', icon: 'AlignRight' }] },
                    { name: 'allLabel', type: 'text', label: '"All" Label' }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'columns', type: 'range', label: 'Columns', min: 2, max: 6, step: 1, responsive: true }
                ]
            },
            {
                id: 'hover',
                label: 'Hover Effect',
                fields: [
                    { name: 'hoverEffect', type: 'select', label: 'Effect', options: [{ value: 'zoom', label: 'Zoom' }, { value: 'fade', label: 'Fade' }, { value: 'slide', label: 'Slide Up' }] },
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
