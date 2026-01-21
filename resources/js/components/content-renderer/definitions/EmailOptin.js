import { Mail } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'newsletter',
    label: 'Email Optin',
    icon: Mail,
    description: 'Newsletter signup form to capture emails.',
    component: defineAsyncComponent(() => import('@/shared/blocks/NewsletterBlock.vue')),
    settings: [
        { key: 'title', type: 'text', label: 'Title', default: 'Subscribe to Our Newsletter' },
        { key: 'description', type: 'textarea', label: 'Description', default: 'Get the latest updates and news delivered to your inbox.' },
        { key: 'button_text', type: 'text', label: 'Button Text', default: 'Subscribe' },
        { key: 'success_message', type: 'text', label: 'Success Message', default: 'Thank you for subscribing!' },
        { key: 'placeholder', type: 'text', label: 'Email Placeholder', default: 'Enter your email' },
        { key: 'show_name', type: 'boolean', label: 'Show Name Field', default: false },
        { key: 'name_placeholder', type: 'text', label: 'Name Placeholder', default: 'Your name' },
        {
            key: 'provider',
            type: 'select',
            label: 'Service Provider',
            options: [
                { label: 'MailChimp', value: 'mailchimp' },
                { label: 'ConvertKit', value: 'convertkit' },
                { label: 'MailerLite', value: 'mailerlite' },
                { label: 'ActiveCampaign', value: 'activecampaign' }
            ],
            default: 'mailchimp'
        },
        { key: 'list_id', type: 'text', label: 'List ID', default: '' },
        { key: 'redirect_url', type: 'text', label: 'Redirect URL (Optional)', default: '' },
        { key: 'image', type: 'image', label: 'Image', default: '' },
        {
            key: 'image_position',
            type: 'select',
            label: 'Image Position',
            options: [
                { label: 'Top', value: 'top' },
                { label: 'Left', value: 'left' },
                { label: 'Right', value: 'right' },
                { label: 'Bottom', value: 'bottom' }
            ],
            default: 'top'
        },
        {
            key: 'layout',
            type: 'select',
            label: 'Layout',
            options: [
                { label: 'Stacked', value: 'stacked' },
                { label: 'Inline', value: 'inline' }
            ],
            default: 'stacked'
        },
        {
            key: 'style',
            type: 'select',
            label: 'Style',
            options: [
                { label: 'Card', value: 'card' },
                { label: 'Gradient', value: 'gradient' },
                { label: 'Minimal', value: 'minimal' }
            ],
            default: 'card'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' },
                { label: 'Right', value: 'right' }
            ],
            default: 'center'
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Padding',
            options: [
                { label: 'None', value: '' },
                { label: 'Small', value: 'py-8' },
                { label: 'Medium', value: 'py-16' },
                { label: 'Large', value: 'py-24' }
            ],
            default: 'py-16'
        },
        { key: 'bgColor', type: 'color', label: 'Background Color', default: '' }
    ],
    defaultSettings: {
        title: 'Subscribe to Our Newsletter',
        description: 'Get the latest updates and news delivered to your inbox.',
        button_text: 'Subscribe',
        success_message: 'Thank you for subscribing!',
        placeholder: 'Enter your email',
        show_name: false,
        name_placeholder: 'Your name',
        layout: 'stacked',
        style: 'card',
        alignment: 'center',
        padding: 'py-16',
        bgColor: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
