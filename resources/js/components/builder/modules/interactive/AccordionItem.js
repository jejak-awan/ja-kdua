import {
    adminLabelSettings,
    backgroundSettings,
    spacingSettings,
    borderSettings,
    boxShadowSettings,
    sizingSettings,
    filterSettings,
    transformSettings,
    animationSettings,
    linkSettings,
    loopSettings,
    orderSettings,
    layoutSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    visibilitySettings,
    transitionSettings,
    positionSettings,
    cssSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Accordion Item Module Definition
 */
export default {
    name: 'accordion_item',
    title: 'Accordion Item',
    icon: 'List',
    category: 'internal',

    children: null,

    defaults: {
        title: 'Your Title Goes Here',
        content: '<p>Your content goes here. Edit or remove this text inline or in the module Content settings. You can also style every aspect of this content in the module Design settings and even apply custom CSS to this text in the module Advanced settings.</p>',
        open: false,
        // Layout
        layout_type: 'flex',
        direction: 'column',
        justify_content: 'flex-start',
        align_items: 'stretch',
        flex_wrap: 'nowrap',
        align_content: 'flex-start',
        gap_x: '0px',
        gap_y: '0px',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        openHeaderBackgroundColor: '',
        openHeaderTextColor: '',
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
            styles: {
                all: { width: 0, color: '#e5e5e5', style: 'solid' }
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
                id: 'text',
                label: 'Text',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'content', type: 'richtext', label: 'Content' }
                ]
            },
            linkSettings,
            {
                id: 'elements',
                label: 'Elements',
                fields: [
                    { name: 'open', type: 'toggle', label: 'Open by Default' }
                ]
            },
            backgroundSettings,
            loopSettings,
            orderSettings,
            adminLabelSettings('Accordion Item')
        ],
        design: [
            layoutSettings,
            {
                id: 'icon_design',
                label: 'Icon',
                fields: [
                    {
                        name: 'iconColor',
                        type: 'color',
                        label: 'Icon Color'
                    }
                ]
            },
            {
                id: 'toggle_design',
                label: 'Toggle',
                fields: [
                    {
                        name: 'openHeaderBackgroundColor',
                        type: 'color',
                        label: 'Open Toggle Background Color'
                    },
                    {
                        name: 'headerBackgroundColor',
                        type: 'color',
                        label: 'Closed Toggle Background Color'
                    }
                ]
            },
            {
                id: 'text_design',
                label: 'Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `text_${f.name}`,
                    label: `Text ${f.label}`
                }))
            },
            {
                id: 'title_text_design',
                label: 'Title Text',
                fields: [
                    {
                        name: 'openHeaderTextColor',
                        type: 'color',
                        label: 'Open Title Text Color'
                    },
                    ...typographySettings.fields.map(f => ({
                        ...f,
                        name: `header_${f.name}`,
                        label: `Title ${f.label}`
                    }))
                ]
            },
            {
                id: 'closed_title_text_design',
                label: 'Closed Title Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `closed_header_${f.name}`,
                    label: `Closed Title ${f.label}`
                }))
            },
            {
                id: 'body_text_design',
                label: 'Body Text',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `content_${f.name}`,
                    label: `Body ${f.label}`
                }))
            },
            sizingSettings,
            spacingSettings,
            borderSettings,
            boxShadowSettings,
            filterSettings,
            transformSettings,
            animationSettings
        ],
        advanced: [
            attributesSettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            visibilitySettings,
            transitionSettings,
            positionSettings,
            scrollEffectsSettings
        ]
    }
}
