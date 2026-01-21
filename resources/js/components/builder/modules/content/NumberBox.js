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
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
} from '../commonSettings';

/**
 * Number Box Module Definition
 */
export default {
    name: 'numberbox',
    title: 'Number Box',
    icon: 'Hash',
    category: 'content',

    children: null,

    defaults: {
        number: '01',
        title: 'Step Title',
        description: 'Brief description of this step or number point.',
        // Layout
        layout: 'horizontal',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 20, bottom: 20, left: 20, right: 20, unit: 'px' },
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
                id: 'content',
                label: 'Content',
                fields: [
                    { name: 'number', type: 'text', label: 'Number' },
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'description', type: 'textarea', label: 'Description' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Number Box')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'layout', type: 'select', label: 'Layout', options: [{ value: 'horizontal', label: 'Horizontal' }, { value: 'vertical', label: 'Vertical' }] }
                ]
            },
            {
                id: 'numberTypography',
                label: 'Number Typography',
                fields: [
                    { name: 'number_backgroundColor', type: 'color', label: 'Background Color' },
                    ...typographySettings.fields.map(f => ({
                        ...f,
                        name: `number_${f.name}`,
                        label: `Number ${f.label}`
                    }))
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
                id: 'descriptionTypography',
                label: 'Description Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `description_${f.name}`,
                    label: `Description ${f.label}`
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
}
