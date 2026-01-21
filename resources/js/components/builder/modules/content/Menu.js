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
 * Menu Module Definition (Divi 5 Reference)
 */
export default {
    name: 'menu',
    title: 'Menu',
    icon: 'Menu',
    category: 'content',

    children: null,

    defaults: {
        menuId: '',
        style: 'horizontal',
        alignment: 'left',
        // Logo
        showLogo: false,
        logoImage: '',
        logoPosition: 'left',
        // Mobile
        mobileBreakpoint: 980,
        mobileToggleStyle: 'hamburger',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        menuBackgroundColor: '',
        // Spacing
        padding: { top: 16, bottom: 16, left: 0, right: 0, unit: 'px' },
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
                id: 'menu',
                label: 'Menu',
                fields: [
                    { name: 'menuId', type: 'text', label: 'Menu ID/Slug' },
                    { name: 'style', type: 'select', label: 'Style', responsive: true, options: [{ value: 'horizontal', label: 'Horizontal' }, { value: 'vertical', label: 'Vertical' }, { value: 'dropdown', label: 'Dropdown' }] }
                ]
            },
            {
                id: 'logo',
                label: 'Logo',
                fields: [
                    { name: 'showLogo', type: 'toggle', label: 'Show Logo', responsive: true },
                    { name: 'logoImage', type: 'upload', label: 'Logo Image' },
                    { name: 'logoPosition', type: 'select', label: 'Position', responsive: true, options: [{ value: 'left', label: 'Left' }, { value: 'center', label: 'Center' }, { value: 'right', label: 'Right' }] }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Menu')
        ],
        design: [
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    { name: 'alignment', type: 'buttonGroup', label: 'Alignment', responsive: true, options: [{ value: 'left', label: 'Left', icon: 'AlignLeft' }, { value: 'center', label: 'Center', icon: 'AlignCenter' }, { value: 'right', label: 'Right', icon: 'AlignRight' }] }
                ]
            },
            {
                id: 'menuTypography',
                label: 'Menu Typography',
                fields: [
                    ...typographySettings.fields.map(f => ({
                        ...f,
                        name: `menu_${f.name}`,
                        label: `Menu ${f.label}`
                    })),
                    { name: 'menu_colorHover', type: 'color', label: 'Hover Color', responsive: true }
                ]
            },
            {
                id: 'dropdownStyle',
                label: 'Dropdown Style',
                fields: [
                    { name: 'menuBackgroundColor', type: 'color', label: 'Dropdown Background', responsive: true }
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
            { id: 'mobile', label: 'Mobile', fields: [{ name: 'mobileBreakpoint', type: 'range', label: 'Breakpoint', min: 768, max: 1200, step: 20, unit: 'px' }] },
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
