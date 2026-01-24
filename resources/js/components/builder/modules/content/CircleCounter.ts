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
    cssSettings,
    typographySettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
} from '../commonSettings';

/**
 * Circle Counter Module Definition
 */
const CircleCounterModule: ModuleDefinition = {
    name: 'circlecounter',
    title: 'Circle Counter',
    icon: 'Circle',
    category: 'content',

    children: null,

    defaults: {
        value: 75,
        title: 'Progress',
        showValue: true,
        // Circle
        size: 150,
        thickness: 10,
        // Colors
        color: '#2059ea',
        trackColor: '#e0e0e0',
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
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
                id: 'counter',
                label: 'Counter',
                fields: [
                    {
                        name: 'value',
                        type: 'range',
                        label: 'Value',
                        min: 0,
                        max: 100,
                        step: 1,
                        unit: '%',
                        responsive: true
                    },
                    {
                        name: 'title',
                        type: 'text',
                        label: 'Title',
                        responsive: true
                    },
                    {
                        name: 'showValue',
                        type: 'toggle',
                        label: 'Show Value',
                        responsive: true
                    }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Circle Counter')
        ],
        design: [
            {
                id: 'circle',
                label: 'Circle',
                fields: [
                    {
                        name: 'size',
                        type: 'range',
                        label: 'Size',
                        min: 80,
                        max: 300,
                        step: 10,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'thickness',
                        type: 'range',
                        label: 'Thickness',
                        min: 4,
                        max: 24,
                        step: 2,
                        unit: 'px',
                        responsive: true
                    },
                    {
                        name: 'color',
                        type: 'color',
                        label: 'Color',
                        responsive: true
                    },
                    {
                        name: 'trackColor',
                        type: 'color',
                        label: 'Track Color',
                        responsive: true
                    }
                ]
            },
            {
                id: 'percentageTypography',
                label: 'Percentage Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `value_${f.name}`,
                    label: `Value ${f.label}`
                }))
            },
            {
                id: 'titleTypography',
                label: 'Title Typography',
                fields: typographySettings.fields!.map(f => ({
                    ...f,
                    name: `title_${f.name}`,
                    label: `Title ${f.label}`
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
};

export default CircleCounterModule;
