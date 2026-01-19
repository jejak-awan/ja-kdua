/**
 * Common Settings Definitions
 * Reusable property groups for all modules
 */

// Background
export const backgroundSettings = {
    id: 'background',
    label: 'Background',
    presets: true,
    fields: [
        {
            name: 'background',
            type: 'background',
            label: 'Background'
        }
    ]
}

// Spacing
export const spacingSettings = {
    id: 'spacing',
    label: 'Spacing',
    presets: true,
    fields: [
        {
            name: 'padding',
            type: 'spacing',
            label: 'Padding',
            units: ['px', '%', 'em', 'rem', 'vh', 'vw'],
            responsive: true
        },
        {
            name: 'margin',
            type: 'spacing',
            label: 'Margin',
            units: ['px', '%', 'em', 'rem', 'vh', 'vw', 'auto'],
            responsive: true
        }
    ]
}

// Border
export const borderSettings = {
    id: 'border',
    label: 'Border',
    presets: true,
    fields: [
        {
            name: 'border',
            type: 'border',
            label: 'Border'
        }
    ]
}

// Box Shadow
export const boxShadowSettings = {
    id: 'boxShadow',
    label: 'Box Shadow',
    presets: true,
    fields: [
        {
            name: 'boxShadow',
            type: 'shadow',
            label: 'Box Shadow'
        }
    ]
}

// Typography (Generic)
export const typographySettings = {
    id: 'typography',
    label: 'Typography',
    presets: true,
    fields: [
        {
            name: 'font_family',
            type: 'font',
            label: 'Font Family',
            default: ''
        },
        {
            name: 'font_weight',
            type: 'select',
            label: 'Font Weight',
            options: [
                { label: 'Default', value: '' },
                { label: 'Thin (100)', value: '100' },
                { label: 'Light (300)', value: '300' },
                { label: 'Regular (400)', value: '400' },
                { label: 'Medium (500)', value: '500' },
                { label: 'Semi Bold (600)', value: '600' },
                { label: 'Bold (700)', value: '700' },
                { label: 'Extra Bold (800)', value: '800' },
                { label: 'Black (900)', value: '900' }
            ],
            default: ''
        },
        {
            name: 'font_size',
            type: 'range',
            label: 'Font Size',
            min: 8,
            max: 100,
            unit: 'px',
            responsive: true,
            default: ''
        },
        {
            name: 'line_height',
            type: 'range',
            label: 'Line Height',
            min: 0.5,
            max: 3,
            step: 0.1,
            unit: 'em',
            responsive: true,
            default: ''
        },
        {
            name: 'letter_spacing',
            type: 'range',
            label: 'Letter Spacing',
            min: -5,
            max: 10,
            step: 0.1,
            unit: 'px',
            responsive: true,
            default: ''
        },
        {
            name: 'text_align',
            type: 'buttonGroup',
            label: 'Text Alignment',
            options: [
                { value: 'left', icon: 'AlignLeft' },
                { value: 'center', icon: 'AlignCenter' },
                { value: 'right', icon: 'AlignRight' },
                { value: 'justify', icon: 'AlignJustify' }
            ],
            responsive: true,
            default: ''
        },
        {
            name: 'text_color',
            type: 'color',
            label: 'Text Color',
            default: ''
        },
        {
            name: 'text_shadow',
            type: 'shadow',
            label: 'Text Shadow'
        }
    ]
}

// Animation (Moved from settings.js for consistency)
export const animationSettings = {
    id: 'animation',
    label: 'Animation',
    presets: true,
    fields: [
        {
            name: 'animation_effect',
            type: 'select',
            label: 'Animation Effect',
            options: [
                { label: 'None', value: '' },
                { label: 'Fade In', value: 'animate-fade' },
                { label: 'Fade In Up', value: 'animate-fade-up' },
                { label: 'Fade In Down', value: 'animate-fade-down' },
                { label: 'Fade In Left', value: 'animate-fade-left' },
                { label: 'Fade In Right', value: 'animate-fade-right' },
                { label: 'Zoom In', value: 'animate-zoom' },
                { label: 'Bounce In', value: 'animate-bounce-in' },
                { label: 'Flip X', value: 'animate-flip-x' }
            ],
            default: ''
        },
        {
            name: 'animation_duration',
            type: 'range',
            label: 'Duration (ms)',
            min: 0,
            max: 3000,
            step: 100,
            default: 1000,
            show_if: { field: 'animation_effect', operator: '!=', value: '' }
        },
        {
            name: 'animation_delay',
            type: 'range',
            label: 'Delay (ms)',
            min: 0,
            max: 2000,
            step: 50,
            default: 0,
            show_if: { field: 'animation_effect', operator: '!=', value: '' }
        },
        {
            name: 'animation_repeat',
            type: 'select',
            label: 'Repeat',
            options: [
                { label: 'Once', value: '1' },
                { label: 'Infinite', value: 'infinite' }
            ],
            default: '1',
            show_if: { field: 'animation_effect', operator: '!=', value: '' }
        }
    ]
}

// CSS ID & Class Settings
export const cssSettings = {
    id: 'css',
    label: 'Custom CSS',
    fields: [
        {
            name: 'cssId',
            type: 'text',
            label: 'CSS ID'
        },
        {
            name: 'cssClass',
            type: 'text',
            label: 'CSS Class'
        }
    ]
}

// Sizing
export const sizingSettings = {
    id: 'sizing',
    label: 'Sizing',
    presets: true,
    fields: [
        {
            name: 'width',
            type: 'range',
            label: 'Width',
            min: 0,
            max: 100,
            unit: '%',
            responsive: true,
            default: ''
        },
        {
            name: 'max_width',
            type: 'range',
            label: 'Max Width',
            min: 0,
            max: 100,
            unit: '%',
            responsive: true,
            default: ''
        },
        {
            name: 'height',
            type: 'range',
            label: 'Height',
            min: 0,
            max: 1000,
            unit: 'px',
            responsive: true,
            default: ''
        },
        {
            name: 'min_height',
            type: 'range',
            label: 'Min Height',
            min: 0,
            max: 1000,
            unit: 'px',
            responsive: true,
            default: ''
        }
    ]
}

// Filters
export const filterSettings = {
    id: 'filters',
    label: 'Filters',
    presets: true,
    fields: [
        {
            name: 'opacity',
            type: 'range',
            label: 'Opacity',
            min: 0,
            max: 100,
            unit: '%',
            default: 100,
            responsive: true
        },
        {
            name: 'blur',
            type: 'range',
            label: 'Blur',
            min: 0,
            max: 50,
            unit: 'px',
            default: 0,
            responsive: true
        },
        {
            name: 'brightness',
            type: 'range',
            label: 'Brightness',
            min: 0,
            max: 200,
            unit: '%',
            default: 100,
            responsive: true
        },
        {
            name: 'contrast',
            type: 'range',
            label: 'Contrast',
            min: 0,
            max: 200,
            unit: '%',
            default: 100,
            responsive: true
        },
        {
            name: 'grayscale',
            type: 'range',
            label: 'Grayscale',
            min: 0,
            max: 100,
            unit: '%',
            default: 0,
            responsive: true
        },
        {
            name: 'sepia',
            type: 'range',
            label: 'Sepia',
            min: 0,
            max: 100,
            unit: '%',
            default: 0,
            responsive: true
        },
        {
            name: 'saturate',
            type: 'range',
            label: 'Saturate',
            min: 0,
            max: 200,
            unit: '%',
            default: 100,
            responsive: true
        },
        {
            name: 'hue_rotate',
            type: 'range',
            label: 'Hue Rotate',
            min: 0,
            max: 360,
            unit: 'deg',
            default: 0,
            responsive: true
        },
        {
            name: 'invert',
            type: 'range',
            label: 'Invert',
            min: 0,
            max: 100,
            unit: '%',
            default: 0,
            responsive: true
        },
        {
            name: 'blend_mode',
            type: 'select',
            label: 'Blend Mode',
            options: [
                { label: 'Normal', value: 'normal' },
                { label: 'Multiply', value: 'multiply' },
                { label: 'Screen', value: 'screen' },
                { label: 'Overlay', value: 'overlay' },
                { label: 'Darken', value: 'darken' },
                { label: 'Lighten', value: 'lighten' },
                { label: 'Color Dodge', value: 'color-dodge' },
                { label: 'Color Burn', value: 'color-burn' },
                { label: 'Hard Light', value: 'hard-light' },
                { label: 'Soft Light', value: 'soft-light' },
                { label: 'Difference', value: 'difference' },
                { label: 'Exclusion', value: 'exclusion' },
                { label: 'Hue', value: 'hue' },
                { label: 'Saturation', value: 'saturation' },
                { label: 'Color', value: 'color' },
                { label: 'Luminosity', value: 'luminosity' }
            ],
            default: 'normal'
        }
    ]
}

// Transform
export const transformSettings = {
    id: 'transform',
    label: 'Transform',
    presets: true,
    fields: [
        {
            name: 'transform_scale',
            type: 'range',
            label: 'Scale (%)',
            min: 0,
            max: 200,
            unit: '%',
            default: 100,
            responsive: true
        },
        {
            name: 'transform_translate_x',
            type: 'range',
            label: 'Translate X',
            min: -500,
            max: 500,
            unit: 'px',
            default: 0,
            responsive: true
        },
        {
            name: 'transform_translate_y',
            type: 'range',
            label: 'Translate Y',
            min: -500,
            max: 500,
            unit: 'px',
            default: 0,
            responsive: true
        },
        {
            name: 'transform_rotate',
            type: 'range',
            label: 'Rotate X (deg)',
            min: 0,
            max: 360,
            unit: 'deg',
            default: 0,
            responsive: true
        },
        {
            name: 'transform_rotate_y',
            type: 'range',
            label: 'Rotate Y (deg)',
            min: 0,
            max: 360,
            unit: 'deg',
            default: 0,
            responsive: true
        },
        {
            name: 'transform_rotate_z',
            type: 'range',
            label: 'Rotate Z (deg)',
            min: 0,
            max: 360,
            unit: 'deg',
            default: 0,
            responsive: true
        },
        {
            name: 'transform_skew_x',
            type: 'range',
            label: 'Skew X (deg)',
            min: -180,
            max: 180,
            unit: 'deg',
            default: 0,
            responsive: true
        },
        {
            name: 'transform_skew_y',
            type: 'range',
            label: 'Skew Y (deg)',
            min: -180,
            max: 180,
            unit: 'deg',
            default: 0,
            responsive: true
        }
    ]
}

// Visibility
export const visibilitySettings = {
    id: 'visibility',
    label: 'Visibility',
    fields: [
        {
            name: 'visibility_desktop',
            type: 'toggle',
            label: 'Show on Desktop',
            default: true
        },
        {
            name: 'visibility_tablet',
            type: 'toggle',
            label: 'Show on Tablet',
            default: true
        },
        {
            name: 'visibility_mobile',
            type: 'toggle',
            label: 'Show on Mobile',
            default: true
        },
        {
            name: 'overflow',
            type: 'select',
            label: 'Overflow',
            options: [
                { label: 'Default', value: 'visible' },
                { label: 'Hidden', value: 'hidden' },
                { label: 'Scroll', value: 'scroll' },
                { label: 'Auto', value: 'auto' }
            ],
            default: 'visible',
            responsive: true
        }
    ]
}

// Position
export const positionSettings = {
    id: 'position',
    label: 'Position',
    fields: [
        {
            name: 'position',
            type: 'select',
            label: 'Position',
            options: [
                { label: 'Default (Relative)', value: 'relative' },
                { label: 'Absolute', value: 'absolute' },
                { label: 'Fixed', value: 'fixed' }
            ],
            default: 'relative',
            responsive: true
        },
        {
            name: 'z_index',
            type: 'number',
            label: 'Z Index',
            default: '',
            responsive: true
        }
    ]
}

// Transition
export const transitionSettings = {
    id: 'transitions',
    label: 'Transitions',
    fields: [
        {
            name: 'transition_duration',
            type: 'range',
            label: 'Transition Duration (ms)',
            min: 0,
            max: 5000,
            step: 50,
            unit: 'ms',
            default: 0
        },
        {
            name: 'transition_delay',
            type: 'range',
            label: 'Transition Delay (ms)',
            min: 0,
            max: 5000,
            step: 50,
            unit: 'ms',
            default: 0
        },
        {
            name: 'transition_curve',
            type: 'select',
            label: 'Transition Speed Curve',
            options: [
                { label: 'Ease', value: 'ease' },
                { label: 'Ease In', value: 'ease-in' },
                { label: 'Ease Out', value: 'ease-out' },
                { label: 'Ease In Out', value: 'ease-in-out' },
                { label: 'Linear', value: 'linear' }
            ],
            default: 'ease'
        }
    ]
}

// Loop
export const loopSettings = {
    id: 'loop',
    label: 'Loop',
    fields: [
        {
            name: 'loop_enable',
            type: 'toggle',
            label: 'Loop Element',
            default: false
        },
        {
            name: 'query_type',
            type: 'select',
            label: 'Query Type',
            options: [
                { label: 'Post Type', value: 'post_type' },
                { label: 'Terms', value: 'terms' },
                { label: 'Users', value: 'users' }
            ],
            default: 'post_type',
            show_if: { field: 'loop_enable', value: true }
        },

        // --- Post Type Mode ---
        {
            name: 'post_type',
            type: 'select',
            label: 'Post Type',
            multiple: true,
            searchable: true,
            options: [
                { label: 'Posts', value: 'post' },
                { label: 'Pages', value: 'page' },
                { label: 'Portfolio', value: 'portfolio' }
            ],
            default: ['post'],
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'include_terms',
            type: 'select',
            label: 'Only Include Posts With Specific Terms',
            multiple: true,
            searchable: true,
            options: 'dynamic:terms',
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'exclude_terms',
            type: 'select',
            label: 'Exclude Posts With Specific Terms',
            multiple: true,
            searchable: true,
            options: 'dynamic:terms',
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'include_posts',
            type: 'select',
            label: 'Only Include Specific Posts',
            multiple: true,
            searchable: true,
            options: 'dynamic:posts',
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'exclude_posts',
            type: 'select',
            label: 'Exclude Specific Posts',
            multiple: true,
            searchable: true,
            options: 'dynamic:posts',
            show_if: { field: 'query_type', value: 'post_type' }
        },

        // --- Terms Mode ---
        {
            name: 'terms',
            type: 'select',
            label: 'Terms',
            multiple: true,
            searchable: true,
            options: 'dynamic:terms',
            show_if: { field: 'query_type', value: 'terms' }
        },

        // --- Users Mode ---
        {
            name: 'users',
            type: 'select',
            label: 'Users',
            multiple: true,
            searchable: true,
            options: 'dynamic:users',
            show_if: { field: 'query_type', value: 'users' }
        },

        // --- Common Query Options ---
        {
            name: 'meta_query',
            type: 'meta_query',
            label: 'Meta Query',
            show_if: { field: 'loop_enable', value: true }
        },
        {
            name: 'order_by',
            type: 'select',
            label: 'Order By',
            options: [
                { label: 'Publish Date', value: 'date' },
                { label: 'Title', value: 'title' },
                { label: 'Random', value: 'rand' },
                { label: 'ID', value: 'ID' },
                { label: 'Modified Date', value: 'modified' },
                { label: 'Comment Count', value: 'comment_count' }
            ],
            default: 'date',
            show_if: { field: 'loop_enable', value: true }
        },
        {
            name: 'order',
            type: 'select',
            label: 'Order',
            options: [
                { label: 'Descending', value: 'DESC' },
                { label: 'Ascending', value: 'ASC' }
            ],
            default: 'DESC',
            show_if: { field: 'loop_enable', value: true }
        },

        // --- Pagination / Limits ---
        {
            name: 'posts_per_page',
            type: 'number',
            label: 'Posts Per Page',
            default: 10,
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'terms_per_page',
            type: 'number',
            label: 'Terms Per Page',
            default: 10,
            show_if: { field: 'query_type', value: 'terms' }
        },
        {
            name: 'users_per_page',
            type: 'number',
            label: 'Users Per Page',
            default: 10,
            show_if: { field: 'query_type', value: 'users' }
        },
        {
            name: 'offset',
            type: 'number',
            label: 'Post Offset',
            default: 0,
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'term_offset',
            type: 'number',
            label: 'Term Offset',
            default: 0,
            show_if: { field: 'query_type', value: 'terms' }
        },
        {
            name: 'user_offset',
            type: 'number',
            label: 'User Offset',
            default: 0,
            show_if: { field: 'query_type', value: 'users' }
        },

        // --- Additional Post Options ---
        {
            name: 'exclude_current',
            type: 'toggle',
            label: 'Exclude Current Post',
            default: false,
            show_if: { field: 'query_type', value: 'post_type' }
        },
        {
            name: 'ignore_sticky',
            type: 'toggle',
            label: 'Ignore Sticky Posts',
            default: false,
            show_if: { field: 'query_type', value: 'post_type' }
        }
    ]
}

// Order Settings
export const orderSettings = {
    id: 'order',
    label: 'Order',
    fields: [
        {
            name: 'order',
            type: 'select',
            label: 'Order',
            options: [
                { label: 'Descending', value: 'DESC' },
                { label: 'Ascending', value: 'ASC' }
            ],
            default: 'DESC',
            responsive: true
        }
    ]
}

// Admin Label Settings
export const adminLabelSettings = (defaultLabel = 'Module') => ({
    id: 'admin_label',
    label: 'Admin Label',
    fields: [
        {
            name: 'admin_label',
            type: 'text',
            label: 'Admin Label',
            placeholder: defaultLabel,
            default: defaultLabel
        }
    ]
})

// Link Settings
export const linkSettings = {
    id: 'link',
    label: 'Link',
    fields: [
        {
            name: 'link_url',
            type: 'text',
            label: 'Link URL'
        },
        {
            name: 'link_target',
            type: 'select',
            label: 'Link Target',
            options: [
                { label: 'Same Window', value: '_self' },
                { label: 'New Tab', value: '_blank' }
            ],
            default: '_self'
        }
    ]
}

// Layout Settings
export const layoutSettings = {
    id: 'layout',
    label: 'Layout',
    fields: [
        {
            name: 'layout_type',
            type: 'select',
            label: 'Layout Type',
            options: [
                { label: 'Standard', value: 'standard' },
                { label: 'Stacked', value: 'stacked' }
            ],
            default: 'standard',
            responsive: true
        }
    ]
}

// Conditions Settings
export const conditionsSettings = {
    id: 'conditions',
    label: 'Conditions',
    fields: [
        {
            name: 'condition_enable',
            type: 'toggle',
            label: 'Enable Conditions',
            default: false
        }
    ]
}

// Interactions Settings
export const interactionsSettings = {
    id: 'interactions',
    label: 'Interactions',
    fields: [
        {
            name: 'interaction_on_click',
            type: 'select',
            label: 'On Click',
            options: [
                { label: 'None', value: '' },
                { label: 'Link', value: 'link' },
                { label: 'Popup', value: 'popup' }
            ],
            default: ''
        }
    ]
}

// Scroll Effects Settings
export const scrollEffectsSettings = {
    id: 'scroll_effects',
    label: 'Scroll Effects',
    fields: [
        {
            name: 'scroll_enable',
            type: 'toggle',
            label: 'Enable Scroll Effects',
            default: false
        }
    ]
}

// Attributes Settings
export const attributesSettings = {
    id: 'attributes',
    label: 'Attributes',
    fields: [
        {
            name: 'custom_attributes',
            type: 'key_value',
            label: 'Custom Attributes'
        }
    ]
}
