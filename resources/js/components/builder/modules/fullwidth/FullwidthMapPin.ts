import type { ModuleDefinition } from '@/types/builder';
import {
    adminLabelSettings,
    visibilitySettings,
    cssSettings,
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings
} from '../commonSettings';

/**
 * Fullwidth Map Pin Module Definition
 */
const FullwidthMapPinModule: ModuleDefinition = {
    name: 'fullwidthmap_pin',
    title: 'Map Pin',
    icon: 'MapPin',
    category: 'internal',

    children: null,

    defaults: {
        title: 'New Pin',
        content: 'Location details...',
        lat: '40.7128',
        lng: '-74.0060'
    },

    settings: {
        content: [
            {
                id: 'main',
                label: 'Pin Details',
                fields: [
                    { name: 'title', type: 'text', label: 'Title' },
                    { name: 'content', type: 'richtext', label: 'Description' },
                    { name: 'lat', type: 'text', label: 'Latitude' },
                    { name: 'lng', type: 'text', label: 'Longitude' }
                ]
            },
            adminLabelSettings('Pin')
        ],
        design: [],
        advanced: [
            visibilitySettings,
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
};

export default FullwidthMapPinModule;
