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
    conditionsSettings,
    interactionsSettings,
    scrollEffectsSettings,
    attributesSettings,
    adminLabelSettings,
} from '../commonSettings';

/**
 * Fullwidth Map Module Definition (Divi 5 Reference)
 */
export default {
    name: 'fullwidthmap',
    title: 'Fullwidth Map',
    icon: 'MapPin',
    category: 'fullwidth',

    children: ['fullwidthmap_pin'],

    defaults: {
        address: 'New York, NY, USA',
        lat: 40.7128,
        lng: -74.0060,
        zoom: 14,
        mapType: 'roadmap',
        // Size - fullwidth specific
        height: 500,
        // Marker
        showMarker: true,
        markerTitle: 'Our Location',
        // Controls
        showZoomControl: true,
        showStreetView: false,
        draggable: true,
        // Styling
        grayscale: false,
        // Background
        background: { color: '', image: '', repeat: 'no-repeat', position: 'center', size: 'cover' },
        // Spacing
        padding: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        margin: { top: 0, bottom: 0, left: 0, right: 0, unit: 'px' },
        border: { radius: { tl: 0, tr: 0, bl: 0, br: 0, linked: true }, styles: { all: { width: 0, color: '#333333', style: 'solid' }, top: { width: 0, color: '#333333', style: 'solid' }, right: { width: 0, color: '#333333', style: 'solid' }, bottom: { width: 0, color: '#333333', style: 'solid' }, left: { width: 0, color: '#333333', style: 'solid' } } },
        boxShadow: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false },
        animation_effect: '',
        animation_duration: 1000,
        animation_delay: 0,
        animation_repeat: '1'
    },

    settings: {
        content: [
            {
                id: 'location',
                label: 'Location',
                fields: [
                    { name: 'address', type: 'text', label: 'Address' },
                    { name: 'lat', type: 'text', label: 'Latitude' },
                    { name: 'lng', type: 'text', label: 'Longitude' }
                ]
            },
            {
                id: 'marker',
                label: 'Marker',
                fields: [
                    { name: 'showMarker', type: 'toggle', label: 'Show Marker' },
                    { name: 'markerTitle', type: 'text', label: 'Marker Title' }
                ]
            },
            {
                id: 'pins',
                label: 'Additional Pins',
                fields: [
                    { name: 'module_manager', type: 'children_manager', label: 'Pins' }
                ]
            },
            backgroundSettings,
            adminLabelSettings('Fullwidth Map')
        ],
        design: [
            {
                id: 'mapSettings',
                label: 'Map Settings',
                fields: [
                    { name: 'zoom', type: 'range', label: 'Zoom Level', min: 1, max: 20, step: 1 },
                    {
                        name: 'mapType', type: 'select', label: 'Map Type', options: [
                            { value: 'roadmap', label: 'Roadmap' },
                            { value: 'satellite', label: 'Satellite' },
                            { value: 'hybrid', label: 'Hybrid' },
                            { value: 'terrain', label: 'Terrain' }
                        ]
                    },
                    { name: 'height', type: 'range', label: 'Height', min: 300, max: 800, step: 50, unit: 'px', responsive: true }
                ]
            },
            {
                id: 'controls',
                label: 'Controls',
                fields: [
                    { name: 'showZoomControl', type: 'toggle', label: 'Zoom Control' },
                    { name: 'showStreetView', type: 'toggle', label: 'Street View' },
                    { name: 'draggable', type: 'toggle', label: 'Draggable' },
                    { name: 'grayscale', type: 'toggle', label: 'Grayscale Style' }
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
            cssSettings,
            conditionsSettings,
            interactionsSettings,
            scrollEffectsSettings,
            attributesSettings
        ]
    }
}
