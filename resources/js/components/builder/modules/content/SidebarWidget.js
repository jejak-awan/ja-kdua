import {
    adminLabelSettings,
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings
} from '../commonSettings';

/**
 * Sidebar Widget Module Definition
 */
export default {
    name: 'sidebar_widget',
    title: 'Widget',
    icon: 'LayoutTemplate',
    category: 'internal',

    children: null,

    defaults: {
        widgetType: 'search',
        title: '', // Optional override
        // Widget Specifics can be expanded
        count: 5, // number of posts/tags/categories
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
                id: 'main',
                label: 'Widget Settings',
                fields: [
                    {
                        name: 'widgetType',
                        type: 'select',
                        label: 'Widget Type',
                        options: [
                            { value: 'search', label: 'Search Box' },
                            { value: 'categories', label: 'Categories List' },
                            { value: 'recentposts', label: 'Recent Posts' },
                            { value: 'tags', label: 'Tag Cloud' },
                            { value: 'text', label: 'Text / HTML' } // Added generic text widget
                        ]
                    },
                    { name: 'title', type: 'text', label: 'Title (Optional)', description: 'Leave empty to use default widget title.' },
                    {
                        name: 'count',
                        type: 'range',
                        label: 'Count',
                        min: 3,
                        max: 20,
                        step: 1,
                        visible: s => ['categories', 'recentposts', 'tags'].includes(s.widgetType)
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Widget')
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
        advanced: []
    }
}
