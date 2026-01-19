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
 * Video Slider Module Definition (Divi 5 Reference)
 */
export default {
    name: 'videoslider',
    title: 'Video Slider',
    icon: 'Film',
    category: 'media',

    children: ['video_slide_item'],

    defaults: {
        // Navigation
        showArrows: true,
        showDots: true,
        showThumbnails: true,
        thumbnailPosition: 'bottom',
        autoplay: false,
        autoplaySpeed: 5000,
        // Layout
        aspectRatio: '16:9',
        slidesPerView: 1,
        gap: 20,
        // Video Settings
        videoAutoplay: false,
        videoMuted: true,
        videoLoop: false,
        showControls: true,
        // Overlay
        showPlayButton: true,
        playButtonSize: 80,
        playButtonColor: '#ffffff',
        overlayColor: 'rgba(0,0,0,0.3)',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 0, color: '#333333', style: 'solid' }
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
                id: 'videos',
                label: 'Videos',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Videos' }
                ]
            },
            {
                id: 'videoSettings',
                label: 'Video Settings',
                fields: [
                    { name: 'videoAutoplay', type: 'toggle', label: 'Autoplay Video' },
                    { name: 'videoMuted', type: 'toggle', label: 'Muted' },
                    { name: 'videoLoop', type: 'toggle', label: 'Loop' },
                    { name: 'showControls', type: 'toggle', label: 'Show Controls' }
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
                    { name: 'showDots', type: 'toggle', label: 'Show Dots' },
                    { name: 'showThumbnails', type: 'toggle', label: 'Show Thumbnails' },
                    {
                        name: 'thumbnailPosition', type: 'select', label: 'Thumbnail Position', options: [
                            { value: 'bottom', label: 'Bottom' },
                            { value: 'left', label: 'Left' },
                            { value: 'right', label: 'Right' }
                        ]
                    },
                    { name: 'autoplay', type: 'toggle', label: 'Autoplay Slider' },
                    { name: 'autoplaySpeed', type: 'range', label: 'Speed', min: 2000, max: 10000, step: 500, unit: 'ms' }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'aspectRatio', type: 'select', label: 'Aspect Ratio', options: [
                            { value: '16:9', label: '16:9' },
                            { value: '4:3', label: '4:3' },
                            { value: '21:9', label: '21:9' },
                            { value: '1:1', label: '1:1' }
                        ]
                    },
                    { name: 'slidesPerView', type: 'range', label: 'Slides Per View', min: 1, max: 4, step: 1, responsive: true },
                    { name: 'gap', type: 'range', label: 'Gap', min: 0, max: 50, step: 5, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    { name: 'showPlayButton', type: 'toggle', label: 'Show Play Button' },
                    { name: 'playButtonSize', type: 'range', label: 'Button Size', min: 40, max: 120, step: 10, unit: 'px', responsive: true },
                    { name: 'playButtonColor', type: 'color', label: 'Button Color', responsive: true },
                    { name: 'overlayColor', type: 'color', label: 'Overlay Color', responsive: true }
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
