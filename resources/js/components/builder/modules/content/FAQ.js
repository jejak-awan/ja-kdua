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
 * FAQ Module Definition
 */
export default {
    name: 'faq',
    title: 'FAQ',
    icon: 'HelpCircle',
    category: 'content',

    children: ['faq_item'],

    defaults: {
        layout: 'accordion',
        allowMultiple: false,
        // Background
        background: { color: '#ffffff', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        // Border
        border: {
            radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true },
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
                id: 'items',
                label: 'FAQ Items',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'FAQ Items' }
                ]
            },
            {
                id: 'behavior',
                label: 'Behavior',
                fields: [
                    { name: 'layout', type: 'select', label: 'Layout', responsive: true, options: [{ value: 'accordion', label: 'Accordion' }, { value: 'list', label: 'List (Always Open)' }] },
                    { name: 'allowMultiple', type: 'toggle', label: 'Allow Multiple Open', responsive: true }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'questionTypography',
                label: 'Question Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `question_${f.name}`,
                    label: `Question ${f.label}`
                }))
            },
            {
                id: 'answerTypography',
                label: 'Answer Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `answer_${f.name}`,
                    label: `Answer ${f.label}`
                }))
            },
            {
                id: 'faqStyle',
                label: 'FAQ Style',
                fields: [
                    { name: 'accentColor', type: 'color', label: 'Icon Color', responsive: true },
                    { name: 'itemBorderColor', type: 'color', label: 'Item Border Color', responsive: true }
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
