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
 * Signup Form Module Definition
 */
export default {
    name: 'signup',
    title: 'Signup Form',
    icon: 'UserPlus',
    category: 'forms',

    children: null,

    defaults: {
        title: 'Create Account',
        subtitle: 'Join us today and get started',
        // Fields
        showName: true,
        namePlaceholder: 'Full Name',
        emailPlaceholder: 'Email Address',
        passwordPlaceholder: 'Password',
        confirmPasswordPlaceholder: 'Confirm Password',
        buttonText: 'Sign Up',
        // Terms
        showTerms: true,
        termsText: 'I agree to the Terms of Service',
        termsUrl: '#',
        // Login Link
        showLoginLink: true,
        loginText: 'Already have an account?',
        loginLinkText: 'Login',
        loginUrl: '#',
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 32, bottom: 32, left: 32, right: 32, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title', responsive: true },
                    { name: 'subtitle', type: 'text', label: 'Subtitle', responsive: true },
                    { name: 'buttonText', type: 'text', label: 'Button Text', responsive: true }
                ]
            },
            {
                id: 'fields',
                label: 'Fields',
                fields: [
                    { name: 'showName', type: 'toggle', label: 'Show Name Field', responsive: true },
                    { name: 'namePlaceholder', type: 'text', label: 'Name Placeholder', responsive: true },
                    { name: 'emailPlaceholder', type: 'text', label: 'Email Placeholder', responsive: true },
                    { name: 'passwordPlaceholder', type: 'text', label: 'Password Placeholder', responsive: true }
                ]
            },
            {
                id: 'terms',
                label: 'Terms & Login',
                fields: [
                    { name: 'showTerms', type: 'toggle', label: 'Show Terms Checkbox', responsive: true },
                    { name: 'termsText', type: 'text', label: 'Terms Text', responsive: true },
                    { name: 'showLoginLink', type: 'toggle', label: 'Show Login Link', responsive: true },
                    { name: 'loginText', type: 'text', label: 'Login Text', responsive: true }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
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
                id: 'inputTypography',
                label: 'Input Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `input_${f.name}`,
                    label: `Input ${f.label}`
                }))
            },
            {
                id: 'buttonStyle',
                label: 'Button Style',
                fields: [
                    { name: 'buttonBackgroundColor', type: 'color', label: 'Button Background', responsive: true },
                    ...typographySettings.fields.map(f => ({
                        ...f,
                        name: `button_${f.name}`,
                        label: `Button ${f.label}`
                    }))
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
