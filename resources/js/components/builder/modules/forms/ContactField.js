import { adminLabelSettings } from '../commonSettings';

/**
 * Contact Field Module Definition
 */
export default {
    name: 'contact_field',
    title: 'Field',
    icon: 'Type',
    category: 'internal',

    children: null,

    defaults: {
        fieldId: 'field_1',
        type: 'text',
        label: 'New Field',
        placeholder: 'Enter text...',
        required: false,
        width: '100%',
        options: '' // For select/radio/checkbox
    },

    settings: {
        content: [
            {
                id: 'main',
                label: 'Field Settings',
                fields: [
                    { name: 'fieldId', type: 'text', label: 'Field ID (Unique)', description: 'Used for form submission data key.' },
                    {
                        name: 'type',
                        type: 'select',
                        label: 'Type',
                        options: [
                            { value: 'text', label: 'Text Input' },
                            { value: 'email', label: 'Email' },
                            { value: 'textarea', label: 'Message / Textarea' },
                            { value: 'checkbox', label: 'Checkbox' },
                            { value: 'radio', label: 'Radio Buttons' },
                            { value: 'select', label: 'Select Dropdown' }
                        ]
                    },
                    { name: 'label', type: 'text', label: 'Label' },
                    { name: 'placeholder', type: 'text', label: 'Placeholder' },
                    { name: 'required', type: 'toggle', label: 'Required' },
                    {
                        name: 'options',
                        type: 'textarea',
                        label: 'Options',
                        description: 'One option per line (for Select, Radio, Checkbox).',
                        visible: (settings) => ['select', 'radio', 'checkbox'].includes(settings.type)
                    }
                ]
            },
            {
                id: 'layout',
                label: 'Layout',
                fields: [
                    {
                        name: 'width',
                        type: 'select',
                        label: 'Width',
                        options: [
                            { value: '100%', label: 'Full Width (100%)' },
                            { value: '50%', label: 'Half Width (50%)' },
                            { value: '33.33%', label: 'One Third (33%)' }
                        ]
                    }
                ]
            },
            adminLabelSettings('Field')
        ],
        design: [],
        advanced: []
    }
}
