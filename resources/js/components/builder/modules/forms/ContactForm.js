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
 * Contact Form Module Definition
 */
export default {
    name: 'contactform',
    title: 'Contact Form',
    icon: 'Mail',
    category: 'forms',

    children: ['contact_field'],

    defaults: {
        title: 'Contact Us',
        subtitle: "We'd love to hear from you. Send us a message!",
        buttonText: 'Send Message',
        successMessage: 'Thank you! Your message has been sent.',
        // Email
        recipientEmail: '',
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
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
        boxShadow: { preset: 'none', horizontal: 0, vertical: 4, blur: 12, spread: 0, color: 'rgba(0,0,0,0.1)', inset: false },
        animation_effect: '', animation_duration: 1000, animation_delay: 0, animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'General',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'subtitle', type: 'text', label: 'Subtitle' },
                    { name: 'buttonText', type: 'text', label: 'Button Text' },
                    { name: 'successMessage', type: 'text', label: 'Success Message' }
                ]
            },
            {
                id: 'fields',
                label: 'Form Fields',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Fields' }
                ]
            },
            {
                id: 'email',
                label: 'Email Settings',
                fields: [
                    { name: 'recipientEmail', type: 'text', label: 'Recipient Email' }
                ]
            },
            backgroundSettings
        ],
        design: [
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
                id: 'labelTypography',
                label: 'Label Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `label_${f.name}`,
                    label: `Label ${f.label}`
                }))
            },
            {
                id: 'fieldTypography',
                label: 'Field Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `field_${f.name}`,
                    label: `Field ${f.label}`
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
            {
                id: 'buttonStyles',
                label: 'Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background' },
                    { name: 'buttonTextColor', type: 'color', label: 'Button Text Color' }
                ]
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
