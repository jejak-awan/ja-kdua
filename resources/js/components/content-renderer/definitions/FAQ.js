export default {
    type: 'faq',
    name: 'FAQ',
    component: 'FAQBlock',
    settings: [
        { name: 'items', type: 'repeater' },
        { name: 'layout', type: 'string' },
        { name: 'allowMultiple', type: 'boolean' },
        { name: 'accentColor', type: 'string' },
        { name: 'itemBorderColor', type: 'string' }
    ]
};
