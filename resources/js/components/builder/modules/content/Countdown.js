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
 * Countdown Timer Module Definition
 */
export default {
    name: 'countdown',
    title: 'Countdown Timer',
    icon: 'Clock',
    category: 'content',

    children: null,

    defaults: {
        targetDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        targetTime: '00:00',
        // Display
        showDays: true,
        showHours: true,
        showMinutes: true,
        showSeconds: true,
        // Labels
        daysLabel: 'Days',
        hoursLabel: 'Hours',
        minutesLabel: 'Minutes',
        secondsLabel: 'Seconds',
        // Layout
        alignment: 'center',
        gap: 24,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        itemBackgroundColor: '#f5f5f5',
        itemBorderRadius: 8,
        itemPadding: 20,
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
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
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'target',
                label: 'Target Date',
                fields: [
                    {
                        name: 'targetDate',
                        type: 'text',
                        label: 'Date (YYYY-MM-DD)'
                    },
                    {
                        name: 'targetTime',
                        type: 'text',
                        label: 'Time (HH:MM)'
                    }
                ]
            },
            {
                id: 'display',
                label: 'Display',
                fields: [
                    {
                        name: 'showDays',
                        type: 'toggle',
                        label: 'Show Days'
                    },
                    {
                        name: 'showHours',
                        type: 'toggle',
                        label: 'Show Hours'
                    },
                    {
                        name: 'showMinutes',
                        type: 'toggle',
                        label: 'Show Minutes'
                    },
                    {
                        name: 'showSeconds',
                        type: 'toggle',
                        label: 'Show Seconds'
                    }
                ]
            },
            {
                id: 'labels',
                label: 'Labels',
                fields: [
                    {
                        name: 'daysLabel',
                        type: 'text',
                        label: 'Days Label'
                    },
                    {
                        name: 'hoursLabel',
                        type: 'text',
                        label: 'Hours Label'
                    },
                    {
                        name: 'minutesLabel',
                        type: 'text',
                        label: 'Minutes Label'
                    },
                    {
                        name: 'secondsLabel',
                        type: 'text',
                        label: 'Seconds Label'
                    }
                ]
            },
            backgroundSettings
        ],
        design: [
            {
                id: 'numberTypography',
                label: 'Number Typography',
                fields: typographySettings.fields.map(f => ({
                    ...f,
                    name: `number_${f.name}`,
                    label: `Number ${f.label}`
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
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'alignment',
                        type: 'buttonGroup',
                        label: 'Alignment',
                        responsive: true,
                        options: [
                            { value: 'left', label: 'Left', icon: 'AlignLeft' },
                            { value: 'center', label: 'Center', icon: 'AlignCenter' },
                            { value: 'right', label: 'Right', icon: 'AlignRight' }
                        ]
                    },
                    {
                        name: 'gap',
                        type: 'range',
                        label: 'Gap',
                        min: 8,
                        max: 48,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
                ]
            },
            {
                id: 'itemStyle',
                label: 'Item Style',
                fields: [
                    {
                        name: 'itemBackgroundColor',
                        type: 'color',
                        label: 'Item Background'
                    },
                    {
                        name: 'itemBorderRadius',
                        type: 'range',
                        label: 'Border Radius',
                        min: 0,
                        max: 24,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'itemPadding',
                        type: 'range',
                        label: 'Item Padding',
                        min: 8,
                        max: 48,
                        step: 4,
                        unit: 'px',
                        responsive: true
                    }
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
