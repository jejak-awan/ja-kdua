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
 * Related Posts Module Definition
 */
export default {
    name: 'relatedposts',
    title: 'Related Posts',
    icon: 'LayoutGrid',
    category: 'dynamic',

    children: null,

    defaults: {
        title: 'Related Posts',
        postsCount: 3,
        columns: 3,
        // Display
        showImage: true,
        showExcerpt: true,
        showMeta: true,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 32, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'settings',
                label: 'Settings',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'postsCount', type: 'range', label: 'Number of Posts', min: 2, max: 6, step: 1, responsive: true }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    { name: 'showImage', type: 'toggle', label: 'Show Image', responsive: true },
                    { name: 'showExcerpt', type: 'toggle', label: 'Show Excerpt', responsive: true },
                    { name: 'showMeta', type: 'toggle', label: 'Show Meta', responsive: true }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'columns', type: 'range', label: 'Columns', min: 2, max: 4, step: 1, responsive: true }
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
                id: 'postTitleTypography',
                label: 'Post Title Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `post_title_${f.name}`,
                    label: `Post Title ${f.label}`
                }))
            },
            {
                id: 'metaTypography',
                label: 'Meta Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `meta_${f.name}`,
                    label: `Meta ${f.label}`
                }))
            },
            {
                id: 'excerptTypography',
                label: 'Excerpt Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `excerpt_${f.name}`,
                    label: `Excerpt ${f.label}`
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
