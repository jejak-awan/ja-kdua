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
 * Person Module Definition (Divi 5 Reference - similar to TeamMember but simpler)
 */
export default {
    name: 'person',
    title: 'Person',
    icon: 'User',
    category: 'content',

    children: null,

    defaults: {
        image: '',
        name: 'John Doe',
        position: 'CEO & Founder',
        bio: 'A brief description about this person and their role in the company.',
        // Social Links
        facebook: '',
        twitter: '',
        linkedin: '',
        instagram: '',
        // Layout
        imagePosition: 'top',
        alignment: 'center',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 24, bottom: 24, left: 24, right: 24, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 8, tr: 8, bl: 8, br: 8, linked: true }, styles: { all: { width: 0, color: '#e0e0e0', style: 'solid' }, top: { width: 0, color: '#e0e0e0', style: 'solid' }, right: { width: 0, color: '#e0e0e0', style: 'solid' }, bottom: { width: 0, color: '#e0e0e0', style: 'solid' }, left: { width: 0, color: '#e0e0e0', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'person',
                label: 'Person',
                fields: [
                    { name: 'image', type: 'upload', label: 'Photo', responsive: true },
                    { name: 'name', type: 'text', label: 'Name', responsive: true },
                    { name: 'position', type: 'text', label: 'Position', responsive: true },
                    { name: 'bio', type: 'textarea', label: 'Short Bio', responsive: true }
                ]
            },
            {
                id: 'social',
                label: 'Social Links',
                fields: [
                    { name: 'facebook', type: 'text', label: 'Facebook URL', responsive: true },
                    { name: 'twitter', type: 'text', label: 'Twitter URL', responsive: true },
                    { name: 'linkedin', type: 'text', label: 'LinkedIn URL', responsive: true },
                    { name: 'instagram', type: 'text', label: 'Instagram URL', responsive: true }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'imagePosition', type: 'select', label: 'Image Position', responsive: true, options: [{ value: 'top', label: 'Top' }, { value: 'left', label: 'Left' }, { value: 'right', label: 'Right' }] },
                    { name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [{ value: 'left', label: 'Left', icon: 'AlignLeft' }, { value: 'center', label: 'Center', icon: 'AlignCenter' }, { value: 'right', label: 'Right', icon: 'AlignRight' }] }
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
                id: 'positionTypography',
                label: 'Position Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `position_${f.name}`,
                    label: `Position ${f.label}`
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
