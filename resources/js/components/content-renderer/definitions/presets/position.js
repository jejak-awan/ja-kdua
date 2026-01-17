/**
 * Position Property Presets
 * Positioning, z-index, and layout settings
 */

import {
    AlignStartVertical, AlignCenterVertical, AlignEndVertical,
    AlignStartHorizontal, AlignCenterHorizontal, AlignEndHorizontal,
    LayoutGrid, Rows, Columns as ColumnsIcon
} from 'lucide-vue-next';

export const positionModeOptions = [
    { label: 'Static', value: 'static' },
    { label: 'Relative', value: 'relative' },
    { label: 'Absolute', value: 'absolute' },
    { label: 'Fixed', value: 'fixed' },
    { label: 'Sticky', value: 'sticky' }
];

export const displayOptions = [
    { label: 'Block', value: 'block' },
    { label: 'Inline Block', value: 'inline-block' },
    { label: 'Flex', value: 'flex' },
    { label: 'Inline Flex', value: 'inline-flex' },
    { label: 'Grid', value: 'grid' },
    { label: 'Hidden', value: 'none' }
];

export const flexDirectionOptions = [
    { label: 'Row', value: 'row', icon: Rows },
    { label: 'Column', value: 'column', icon: ColumnsIcon }
];

export const justifyContentOptions = [
    { label: 'Start', value: 'flex-start', icon: AlignStartHorizontal },
    { label: 'Center', value: 'center', icon: AlignCenterHorizontal },
    { label: 'End', value: 'flex-end', icon: AlignEndHorizontal },
    { label: 'Between', value: 'space-between' },
    { label: 'Around', value: 'space-around' },
    { label: 'Evenly', value: 'space-evenly' }
];

export const alignItemsOptions = [
    { label: 'Start', value: 'flex-start', icon: AlignStartVertical },
    { label: 'Center', value: 'center', icon: AlignCenterVertical },
    { label: 'End', value: 'flex-end', icon: AlignEndVertical },
    { label: 'Stretch', value: 'stretch' },
    { label: 'Baseline', value: 'baseline' }
];

export const overflowOptions = [
    { label: 'Visible', value: 'visible' },
    { label: 'Hidden', value: 'hidden' },
    { label: 'Scroll', value: 'scroll' },
    { label: 'Auto', value: 'auto' }
];

/**
 * Position settings
 */
export const positionSettings = [
    { type: 'header', label: 'Position', tab: 'advanced' },
    {
        key: 'position',
        type: 'select',
        label: 'Position Mode',
        options: positionModeOptions,
        default: 'static',
        tab: 'advanced'
    },
    {
        key: 'zIndex',
        type: 'slider',
        label: 'Z-Index',
        min: -10,
        max: 100,
        step: 1,
        default: 0,
        tab: 'advanced'
    },
    {
        key: 'top',
        type: 'text',
        label: 'Top',
        default: 'auto',
        condition: (settings) => ['absolute', 'fixed', 'sticky'].includes(settings.position),
        tab: 'advanced'
    },
    {
        key: 'right',
        type: 'text',
        label: 'Right',
        default: 'auto',
        condition: (settings) => ['absolute', 'fixed'].includes(settings.position),
        tab: 'advanced'
    },
    {
        key: 'bottom',
        type: 'text',
        label: 'Bottom',
        default: 'auto',
        condition: (settings) => ['absolute', 'fixed'].includes(settings.position),
        tab: 'advanced'
    },
    {
        key: 'left',
        type: 'text',
        label: 'Left',
        default: 'auto',
        condition: (settings) => ['absolute', 'fixed'].includes(settings.position),
        tab: 'advanced'
    }
];

/**
 * Sizing settings
 */
export const sizingSettings = [
    { type: 'header', label: 'Size', tab: 'style' },
    {
        key: 'width',
        type: 'text',
        label: 'Width',
        default: 'auto',
        placeholder: 'auto, 100%, 200px',
        tab: 'style'
    },
    {
        key: 'minWidth',
        type: 'text',
        label: 'Min Width',
        default: '0',
        tab: 'style'
    },
    {
        key: 'maxWidth',
        type: 'text',
        label: 'Max Width',
        default: 'none',
        tab: 'style'
    },
    {
        key: 'height',
        type: 'text',
        label: 'Height',
        default: 'auto',
        placeholder: 'auto, 100%, 200px',
        tab: 'style'
    },
    {
        key: 'minHeight',
        type: 'text',
        label: 'Min Height',
        default: '0',
        tab: 'style'
    },
    {
        key: 'maxHeight',
        type: 'text',
        label: 'Max Height',
        default: 'none',
        tab: 'style'
    }
];

/**
 * Flexbox container settings
 */
export const flexSettings = [
    { type: 'header', label: 'Flex Layout', tab: 'style' },
    {
        key: 'display',
        type: 'toggle_group',
        label: 'Display',
        options: [
            { label: 'Block', value: 'block' },
            { label: 'Flex', value: 'flex' },
            { label: 'Grid', value: 'grid', icon: LayoutGrid }
        ],
        default: 'block',
        tab: 'style'
    },
    {
        key: 'flexDirection',
        type: 'toggle_group',
        label: 'Direction',
        options: flexDirectionOptions,
        default: 'row',
        condition: (settings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'justifyContent',
        type: 'toggle_group',
        label: 'Justify',
        options: justifyContentOptions.slice(0, 4), // Only show first 4 with icons
        default: 'flex-start',
        condition: (settings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'alignItems',
        type: 'toggle_group',
        label: 'Align',
        options: alignItemsOptions.slice(0, 4),
        default: 'flex-start',
        condition: (settings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'flexWrap',
        type: 'boolean',
        label: 'Wrap Items',
        default: false,
        condition: (settings) => settings.display === 'flex',
        tab: 'style'
    },
    {
        key: 'gap',
        type: 'slider',
        label: 'Gap',
        min: 0,
        max: 80,
        step: 4,
        unit: 'px',
        default: 0,
        condition: (settings) => ['flex', 'grid'].includes(settings.display),
        tab: 'style'
    }
];

/**
 * Overflow settings
 */
export const overflowSettings = [
    {
        key: 'overflow',
        type: 'select',
        label: 'Overflow',
        options: overflowOptions,
        default: 'visible',
        tab: 'advanced'
    }
];

/**
 * All layout-related settings
 */
export const layoutSettings = [
    ...flexSettings,
    ...sizingSettings,
    ...positionSettings,
    ...overflowSettings
];

/**
 * Position defaults
 */
export const positionDefaults = {
    position: 'static',
    zIndex: 0,
    top: 'auto',
    right: 'auto',
    bottom: 'auto',
    left: 'auto',
    display: 'block',
    flexDirection: 'row',
    justifyContent: 'flex-start',
    alignItems: 'flex-start',
    flexWrap: false,
    gap: 0,
    width: 'auto',
    minWidth: '0',
    maxWidth: 'none',
    height: 'auto',
    minHeight: '0',
    maxHeight: 'none',
    overflow: 'visible'
};
