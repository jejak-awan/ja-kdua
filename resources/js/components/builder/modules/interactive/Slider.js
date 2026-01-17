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
 * Slider Module Definition
 */
export default {
    name: 'slider',
    title: 'Slider',
    icon: 'Layers',
    category: 'interactive',

    children: ['slide_item'],

    defaults: {
        // Behavior
        autoplay: true,
        autoplaySpeed: 5000,
        loop: true,
        pauseOnHover: true,
        // Navigation
        showArrows: true,
        showDots: true,
        arrowStyle: 'default',
        // Layout
        height: 400,
        slideTransition: 'slide',
        // Overlay
        overlayEnabled: true,
        overlayColor: 'rgba(0,0,0,0.4)',
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'slides',
                label: 'Slides',
                fields: [
                    {
                        name: 'module_manager',
                        type: 'children_manager',
                        label: 'Slides'
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'behavior',
                label: 'Behavior',
                fields: [
                    {
                        name: 'autoplay',
                        type: 'toggle',
                        label: 'Autoplay'
                    },
                    {
                        name: 'autoplaySpeed',
                        type: 'range',
                        label: 'Autoplay Speed',
                        min: 1000,
                        max: 10000,
                        step: 500,
                        unit: 'ms'
                    },
                    {
                        name: 'loop',
                        type: 'toggle',
                        label: 'Loop'
                    },
                    {
                        name: 'pauseOnHover',
                        type: 'toggle',
                        label: 'Pause on Hover'
                    }
                ]
            },
            {
                id: 'navigation',
                label: 'Navigation',
                fields: [
                    {
                        name: 'showArrows',
                        type: 'toggle',
                        label: 'Show Arrows'
                    },
                    {
                        name: 'showDots',
                        type: 'toggle',
                        label: 'Show Dots'
                    },
                    {
                        name: 'slideTransition',
                        type: 'select',
                        label: 'Transition',
                        options: [
                            { value: 'slide', label: 'Slide' },
                            { value: 'fade', label: 'Fade' }
                        ]
                    },
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Text Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    }
                ]
            },
            {
                id: 'overlay',
                label: 'Overlay',
                fields: [
                    {
                        name: 'overlayEnabled',
                        type: 'toggle',
                        label: 'Enable Overlay'
                    },
                    {
                        name: 'overlayColor',
                        type: 'color',
                        label: 'Overlay Color'
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
                id: 'contentTypography',
                label: 'Content Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Content ${f.label}`
                }))
            },
            {
                id: 'buttonTypography',
                label: 'Button Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `button_${f.name}`,
                    label: `Button ${f.label}`
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
