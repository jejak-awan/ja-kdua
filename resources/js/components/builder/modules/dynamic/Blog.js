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
 * Blog Module Definition
 */
export default {
    name: 'blog',
    title: 'Blog Posts',
    icon: 'FileText',
    category: 'dynamic',

    children: null,

    defaults: {
        postsPerPage: 6,
        columns: 3,
        showImage: true,
        showExcerpt: true,
        showDate: true,
        showAuthor: true,
        showCategory: true,
        // Filter
        category: '',
        orderBy: 'date',
        order: 'desc',
        // Layout
        layout: 'grid',
        gap: 24,
        // Image
        imageAspectRatio: '16:9',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 1, color: '#e0e0e0', style: 'solid' },
                top: { width: 1, color: '#e0e0e0', style: 'solid' },
                right: { width: 1, color: '#e0e0e0', style: 'solid' },
                bottom: { width: 1, color: '#e0e0e0', style: 'solid' },
                left: { width: 1, color: '#e0e0e0', style: 'solid' }
            }
        },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'query',
                label: 'Query',
                fields: [
                    {
                        name: 'postsPerPage',
                        type: 'range',
                        label: 'Posts Per Page',
                        min: 1,
                        max: 12,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'category',
                        type: 'text',
                        label: 'Category Filter'
                    },
                    {
                        name: 'orderBy',
                        type: 'select',
                        label: 'Order By',
                        options: [
                            { value: 'date', label: 'Date' },
                            { value: 'title', label: 'Title' },
                            { value: 'views', label: 'Views' }
                        ]
                    },
                    {
                        name: 'order',
                        type: 'select',
                        label: 'Order',
                        options: [
                            { value: 'desc', label: 'Descending' },
                            { value: 'asc', label: 'Ascending' }
                        ]
                    }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    {
                        name: 'showImage',
                        type: 'toggle',
                        label: 'Show Featured Image'
                    },
                    {
                        name: 'showExcerpt',
                        type: 'toggle',
                        label: 'Show Excerpt'
                    },
                    {
                        name: 'showDate',
                        type: 'toggle',
                        label: 'Show Date'
                    },
                    {
                        name: 'showAuthor',
                        type: 'toggle',
                        label: 'Show Author'
                    },
                    {
                        name: 'showCategory',
                        type: 'toggle',
                        label: 'Show Category'
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'layout',
                        type: 'select',
                        label: 'Layout Style',
                        responsive: true,
                        options: [
                            { value: 'grid', label: 'Grid' },
                            { value: 'list', label: 'List' }
                        ]
                    },
                    {
                        name: 'columns',
                        type: 'range',
                        label: 'Columns',
                        min: 1,
                        max: 4,
                        step: 1,
                        responsive: true
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 8,
                        max: 48,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'imageAspectRatio',
                        type: 'select',
                        label: 'Image Aspect Ratio',
                        options: [
                            { value: '16:9', label: '16:9' },
                            { value: '4:3', label: '4:3' },
                            { value: '1:1', label: '1:1' }
                        ]
                    }
                ]
            },
            {
                id: 'cardStyle',
                label: 'Card Style',
                fields: [
                    {
                        name: 'cardBackgroundColor',
                        type: 'color',
                        label: 'Card Background'
                    }
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
