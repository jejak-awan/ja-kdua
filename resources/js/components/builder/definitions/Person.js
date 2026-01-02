import { User } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'person',
    label: 'Person (Bio)',
    icon: User,
    description: 'Display a profile with name, position, bio, and social links.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/PersonBlock.vue')),
    settings: [
        { key: 'name', type: 'text', label: 'Name' },
        { key: 'position', type: 'text', label: 'Position / Job Title' },
        { key: 'bio', type: 'textarea', label: 'Biography' },
        { key: 'image_url', type: 'text', label: 'Image URL' },
        {
            key: 'image_shape',
            type: 'select',
            label: 'Image Shape',
            options: [
                { label: 'Circle', value: 'circle' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Square', value: 'square' }
            ]
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' }
            ]
        },
        { key: 'facebook_url', type: 'text', label: 'Facebook URL' },
        { key: 'twitter_url', type: 'text', label: 'Twitter URL' },
        { key: 'linkedin_url', type: 'text', label: 'LinkedIn URL' }
    ],
    defaultSettings: {
        name: 'John Doe',
        position: 'CEO & Founder',
        bio: 'Write a short biography about this person here.',
        image_url: 'https://i.pravatar.cc/300?u=johndoe',
        image_shape: 'circle',
        alignment: 'center'
    }
};
