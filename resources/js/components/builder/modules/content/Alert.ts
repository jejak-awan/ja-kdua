import type { ModuleDefinition } from '@/types/builder';
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
    linkSettings,
    orderSettings,
    adminLabelSettings,
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Alert Module Definition
 */
const AlertModule: ModuleDefinition = {
    name: 'alert',
    title: 'Alert',
    icon: 'AlertCircle',
    category: 'content',

    children: null,

    defaults: {
        variant: 'info',
        title: '',
        message: 'This is an informational alert message.',
        dismissible: false,
        showIcon: true,
        // Spacing
        padding: { top: 16, bottom: 16, left: 20, right: 20, unit: 'px' },
        margin: { top: 0, bottom: 16, left: 0, right: 0, unit: 'px' },
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        border: {
            radius: { tl: 6, tr: 6, bl: 6, br: 6, linked: true },
            styles: {
                all: { width: 0, color: '#333333', style: 'solid' },
                top: { width: 0, color: '#333333', style: 'solid' },
                right: { width: 0, color: '#333333', style: 'solid' },
                bottom: { width: 0, color: '#333333', style: 'solid' },
                left: { width: 4, color: '', style: 'solid' }
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
                id: 'alert',
                label: 'Alert',
                fields: [
                    {
                        name: 'variant',
                        type: 'select',
                        label: 'Alert Type',
                        options: [
                            { value: 'info', label: 'Info' },
                            { value: 'success', label: 'Success' },
                            { value: 'warning', label: 'Warning' },
                            { value: 'error', label: 'Error' }
                        ]
                    },
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title (optional)',
                        responsive: true
                    },
                    {
                        name: 'message',
                        type: 'textarea',
                        label: 'Message',
                        responsive: true
                    },
                    {
                        name: 'dismissible',
                        type: 'toggle',
                        label: 'Dismissible'
                    },
                    {
                        name: 'showIcon',
                        type: 'toggle',
                        label: 'Show Icon'
                    }
                ]
            },
            linkSettings,
            backgroundSettings,
            orderSettings,
            adminLabelSettings('Alert')
        ],
        design: [
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
                }))
            },
            {
                id: 'messageTypography',
                label: 'Message Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `message_${f.name}`,
                    label: `Message ${f.label}`
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
            scrollEffectsSettings,
            interactionsSettings,
            conditionsSettings,
            attributesSettings,
            cssSettings
        ]
    }
};

export default AlertModule;
