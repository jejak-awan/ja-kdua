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
 * Author Box Module Definition
 */
export default {
    name: 'author',
    title: 'Author Box',
    icon: 'UserCircle',
    category: 'content',

    children: null,

    defaults: {
        name: 'John Doe',
        title: 'Content Writer',
        bio: 'John is a passionate writer with over 10 years of experience in creating engaging content. He loves to share his knowledge and insights with readers.',
        image: '',
        // Social
        socialLinks: [{ platform: 'twitter', url: '#' }, { platform: 'linkedin', url: '#' }],
        showSocial: true,
        // Layout
        layout: 'horizontal',
        imageSize: 100,
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
                id: 'author',
                label: 'Author Info',
                fields: [
                    { name: 'name', type: 'text', label: 'Name' },
                    { name: 'title', type: 'text', label: 'Title/Role' },
                    { name: 'bio', type: 'textarea', label: 'Bio' },
                    { name: 'image', type: 'upload', label: 'Photo' }
                ]
            },
            {
                id: 'social',
                label: 'Social Links',
                fields: [
                    { name: 'showSocial', type: 'toggle', label: 'Show Social Links' },
                    { name: 'socialLinks', type: 'textarea', label: 'Links (JSON)' }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'layout', type: 'select', label: 'Layout', options: [{ value: 'horizontal', label: 'Horizontal' }, { value: 'vertical', label: 'Vertical' }], responsive: true },
                    { name: 'imageSize', type: 'range', label: 'Image Size', min: 60, max: 150, step: 10, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'nameTypography',
                label: 'Name Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `name_${f.name}`,
                    label: `Name ${f.label}`
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
            {
                id: 'bioTypography',
                label: 'Bio Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `bio_${f.name}`,
                    label: `Bio ${f.label}`
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
