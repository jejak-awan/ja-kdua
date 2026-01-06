import { User } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'person',
    label: 'Person (Bio)',
    icon: User,
    description: 'Display a profile with name, position, bio, and social links.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/PersonBlock.vue')),
    settings: [
        { key: 'name', type: 'text', label: 'Name', tab: 'content' },
        { key: 'position', type: 'text', label: 'Position / Job Title', tab: 'content' },
        { key: 'bio', type: 'textarea', label: 'Biography', tab: 'content' },
        { key: 'image_url', type: 'image', label: 'Image URL', tab: 'content' },
        {
            key: 'image_shape',
            type: 'select',
            label: 'Image Shape',
            options: [
                { label: 'Circle', value: 'circle' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Square', value: 'square' }
            ],
            tab: 'style'
        },
        {
            key: 'alignment',
            type: 'select',
            label: 'Alignment',
            options: [
                { label: 'Left', value: 'left' },
                { label: 'Center', value: 'center' }
            ],
            tab: 'style'
        },
        { key: 'facebook_url', type: 'text', label: 'Facebook URL', tab: 'content' },
        { key: 'twitter_url', type: 'text', label: 'Twitter URL', tab: 'content' },
        { key: 'linkedin_url', type: 'text', label: 'LinkedIn URL', tab: 'content' }
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
