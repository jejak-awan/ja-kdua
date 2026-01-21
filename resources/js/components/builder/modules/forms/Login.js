import {
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    animationSettings,
    visibilitySettings,
    positionSettings,
    transitionSettings,
    cssSettings,
    adminLabelSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Login Module Definition
 */
export default {
    name: 'login',
    title: 'Login Form',
    icon: 'LogIn',
    category: 'forms',

    children: null,

    defaults: {
        title: 'Login',
        subtitle: 'Enter your credentials to access your account',
        // Fields
        showLabels: true,
        usernameLabel: 'Email Address',
        passwordLabel: 'Password',
        usernamePlaceholder: 'name@example.com',
        passwordPlaceholder: '••••••••',
        buttonText: 'Sign In',
        // Options
        showRememberMe: true,
        showForgotPassword: true,
        forgotPasswordText: 'Forgot password?',
        forgotPasswordUrl: '#',
        // Redirect
        redirectUrl: '',

        // Background
        background_color: '#ffffff',
        background_image: '',
        background_repeat: 'no-repeat',
        background_position: 'center',
        background_size: 'cover',

        // Typography Defaults
        title_fontFamily: 'inherit',
        title_fontSize: 24,
        title_fontWeight: 'bold',
        title_lineHeight: 1.2,
        title_letterSpacing: 0,
        title_color: '#333333',
        title_textAlign: 'center',
        title_textDecoration: 'none',
        title_textTransform: 'none',

        subtitle_fontFamily: 'inherit',
        subtitle_fontSize: 16,
        subtitle_fontWeight: 'normal',
        subtitle_lineHeight: 1.5,
        subtitle_letterSpacing: 0,
        subtitle_color: '#666666',
        subtitle_textAlign: 'center',
        subtitle_textDecoration: 'none',
        subtitle_textTransform: 'none',

        label_fontFamily: 'inherit',
        label_fontSize: 14,
        label_fontWeight: 'normal',
        label_lineHeight: 1.5,
        label_letterSpacing: 0,
        label_color: '#333333',
        label_textAlign: 'left',
        label_textDecoration: 'none',
        label_textTransform: 'none',

        input_fontFamily: 'inherit',
        input_fontSize: 16,
        input_fontWeight: 'normal',
        input_lineHeight: 1.5,
        input_letterSpacing: 0,
        input_color: '#333333',
        input_textAlign: 'left',
        input_textDecoration: 'none',
        input_textTransform: 'none',

        button_fontFamily: 'inherit',
        button_fontSize: 16,
        button_fontWeight: 'bold',
        button_lineHeight: 1.5,
        button_letterSpacing: 0,
        button_color: '#ffffff',
        button_textAlign: 'center',
        button_textDecoration: 'none',
        button_textTransform: 'none',
        button_backgroundColor: '#2059ea',

        // Spacing
        padding_top: 32,
        padding_bottom: 32,
        padding_left: 32,
        padding_right: 32,
        padding_unit: 'px',
        margin_top: 30,
        margin_bottom: 30,
        margin_left: 0,
        margin_right: 0,
        margin_unit: 'px',

        // Border
        border_radius_tl: 8,
        border_radius_tr: 8,
        border_radius_bl: 8,
        border_radius_br: 8,
        border_radius_linked: true,
        border_styles_all_width: 1,
        border_styles_all_color: '#e0e0e0',
        border_styles_all_style: 'solid',
        border_styles_top_width: 1,
        border_styles_top_color: '#e0e0e0',
        border_styles_top_style: 'solid',
        border_styles_right_width: 1,
        border_styles_right_color: '#e0e0e0',
        border_styles_right_style: 'solid',
        border_styles_bottom_width: 1,
        border_styles_bottom_color: '#e0e0e0',
        border_styles_bottom_style: 'solid',
        border_styles_left_width: 1,
        border_styles_left_color: '#e0e0e0',
        border_styles_left_style: 'solid',

        // Box Shadow
        boxShadow_preset: 'none',
        boxShadow_horizontal: 0,
        boxShadow_vertical: 4,
        boxShadow_blur: 12,
        boxShadow_spread: 0,
        boxShadow_color: 'rgba(0,0,0,0.1)',
        boxShadow_inset: false,

        // Sizing
        width: '100%',
        height: 'auto',
        minHeight: 'auto',
        maxHeight: 'none',

        // Animation
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1',

        // Visibility
        visibility_desktop: true,
        visibility_tablet: true,
        visibility_mobile: true,

        // Position
        position_type: 'static',
        position_top: 0,
        position_right: 0,
        position_bottom: 0,
        position_left: 0,
        position_unit: 'px',
        position_zIndex: 0,

        // Transition
        transition_property: 'all',
        transition_duration: 300,
        transition_easing: 'ease',

        // CSS
        cssClass: '',
        cssId: '',

        // Admin Label
        adminLabel: 'Login'
    },

    settings: {
        content: [
            {
                id: 'text',
                label: 'Text',
                fields: [
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title'
                    },
                    {
                        name: 'subtitle',
                        type: 'text',
                        label: 'Subtitle'
                    },
                    {
                        name: 'buttonText',
                        type: 'text',
                        label: 'Button Text'
                    }
                ]
            },
            {
                id: 'fields',
                label: 'Fields',
                fields: [
                    {
                        name: 'showLabels',
                        type: 'toggle',
                        label: 'Show Labels'
                    },
                    {
                        name: 'usernameLabel',
                        type: 'text',
                        label: 'Username Label'
                    },
                    {
                        name: 'usernamePlaceholder',
                        type: 'text',
                        label: 'Username Placeholder'
                    },
                    {
                        name: 'passwordLabel',
                        type: 'text',
                        label: 'Password Label'
                    },
                    {
                        name: 'passwordPlaceholder',
                        type: 'text',
                        label: 'Password Placeholder'
                    }
                ]
            },
            {
                id: 'options',
                label: 'Options',
                fields: [
                    {
                        name: 'showRememberMe',
                        type: 'toggle',
                        label: 'Show Remember Me'
                    },
                    {
                        name: 'showForgotPassword',
                        type: 'toggle',
                        label: 'Show Forgot Password'
                    },
                    {
                        name: 'forgotPasswordText',
                        type: 'text',
                        label: 'Forgot Password Text'
                    },
                    {
                        name: 'forgotPasswordUrl',
                        type: 'text',
                        label: 'Forgot Password URL'
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Login')
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
                id: 'subtitleTypography',
                label: 'Subtitle Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `subtitle_${f.name}`,
                    label: `Subtitle ${f.label}`
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
                id: 'inputTypography',
                label: 'Input Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `input_${f.name}`,
                    label: `Input ${f.label}`
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
