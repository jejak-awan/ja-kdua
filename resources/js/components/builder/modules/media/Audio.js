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
 * Audio Player Module Definition
 */
export default {
    name: 'audio',
    title: 'Audio Player',
    icon: 'Music',
    category: 'media',

    children: null,

    defaults: {
        audioUrl: '',
        title: 'Audio Track',
        artist: '',
        // Options
        autoplay: false,
        loop: false,
        showDownload: false,
        // Background
        background: { color: '#1a1a1a', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 16, bottom: 16, left: 20, right: 20, unit: 'px' },
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
                id: 'audio',
                label: 'Audio',
                fields: [
                    {
                        name: 'audioUrl',
                        type: 'upload',
                        label: 'Audio File',
                        allowedExtensions: ['mp3', 'wav', 'ogg', 'm4a']
                    },
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title'
                    },
                    {
                        name: 'artist',
                        type: 'text',
                        label: 'Artist'
                    }
                ]
            },
            {
                id: 'options',
                label: 'Options',
                fields: [
                    {
                        name: 'autoplay',
                        type: 'toggle',
                        label: 'Autoplay'
                    },
                    {
                        name: 'loop',
                        type: 'toggle',
                        label: 'Loop'
                    },
                    {
                        name: 'showDownload',
                        type: 'toggle',
                        label: 'Show Download Button'
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'playerStyle',
                label: 'Player Style',
                fields: [
                    {
                        name: 'accentColor',
                        type: 'color',
                        label: 'Accent Color',
                        responsive: true
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
                id: 'artistTypography',
                label: 'Artist Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `artist_${f.name}`,
                    label: `Artist ${f.label}`
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
