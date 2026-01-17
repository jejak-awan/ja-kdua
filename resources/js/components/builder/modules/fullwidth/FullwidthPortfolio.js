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
 * Fullwidth Portfolio Module Definition (Divi 5 Reference)
 */
export default {
    name: 'fullwidthportfolio',
    title: 'Fullwidth Portfolio',
    icon: 'LayoutGrid',
    category: 'fullwidth',

    children: null,

    defaults: {
        postsCount: 8,
        // Display
        showTitle: true,
        showMeta: true,
        carouselMode: true,
        // Navigation
        showArrows: true,
        autoplay: true,
        autoplaySpeed: 4000,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
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
                    { name: 'postsCount', type: 'range', label: 'Number of Projects', min: 4, max: 16, step: 2 },
                    { name: 'showTitle', type: 'toggle', label: 'Show Title' },
                    { name: 'showMeta', type: 'toggle', label: 'Show Meta' },
                    { name: 'carouselMode', type: 'toggle', label: 'Carousel Mode' }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    { name: 'showArrows', type: 'toggle', label: 'Show Arrows' },
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay' },
                    { name: 'autoplaySpeed', type: 'range', label: 'Speed', min: 2000, max: 8000, step: 500, unit: 'ms' }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
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
                id: 'metaTypography',
                label: 'Meta Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `meta_${f.name}`,
                    label: `Meta ${f.label}`
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
