import { Sparkles } from 'lucide-vue-next';
import { defineAsyncComponent } from 'vue';

export default {
    name: 'icon',
    label: 'Icon',
    icon: Sparkles,
    description: 'Display a single icon with styling options.',
    component: defineAsyncComponent(() => import('@/components/builder/blocks/IconBlock.vue')),
    settings: [
        {
            key: 'icon',
            type: 'select',
            label: 'Icon',
            options: [
                { label: '⭐ Star', value: 'star' },
                { label: '❤️ Heart', value: 'heart' },
                { label: '✓ Check', value: 'check' },
                { label: '✕ X', value: 'x' },
                { label: '➕ Plus', value: 'plus' },
                { label: '➖ Minus', value: 'minus' },
                { label: '→ Arrow Right', value: 'arrow-right' },
                { label: '← Arrow Left', value: 'arrow-left' },
                { label: '↑ Arrow Up', value: 'arrow-up' },
                { label: '↓ Arrow Down', value: 'arrow-down' },
                { label: '✉️ Mail', value: 'mail' },
                { label: '📞 Phone', value: 'phone' },
                { label: '📍 Map Pin', value: 'map-pin' },
                { label: '🌐 Globe', value: 'globe' },
                { label: '📅 Calendar', value: 'calendar' },
                { label: '⏰ Clock', value: 'clock' },
                { label: '👤 User', value: 'user' },
                { label: '👥 Users', value: 'users' },
                { label: '⚙️ Settings', value: 'settings' },
                { label: '🏠 Home', value: 'home' },
                { label: '🔍 Search', value: 'search' },
                { label: '🔔 Bell', value: 'bell' },
                { label: '🔖 Bookmark', value: 'bookmark' },
                { label: '📷 Camera', value: 'camera' },
                { label: '⬇️ Download', value: 'download' },
                { label: '⬆️ Upload', value: 'upload' },
                { label: '📤 Share', value: 'share' },
                { label: '👁️ Eye', value: 'eye' },
                { label: '✏️ Edit', value: 'edit' },
                { label: '🗑️ Trash', value: 'trash' },
                { label: '🔗 Link', value: 'link' },
                { label: '▶️ Play', value: 'play' },
                { label: '⏸️ Pause', value: 'pause' },
                { label: '🔊 Volume', value: 'volume' },
                { label: '🎤 Mic', value: 'mic' },
                { label: '🖼️ Image', value: 'image' },
                { label: '🎬 Video', value: 'video' },
                { label: '📄 File', value: 'file' },
                { label: '📁 Folder', value: 'folder' },
                { label: '☁️ Cloud', value: 'cloud' },
                { label: '🗄️ Database', value: 'database' },
                { label: '💻 Code', value: 'code' },
                { label: '🛡️ Shield', value: 'shield' },
                { label: '🔒 Lock', value: 'lock' },
                { label: '🔑 Key', value: 'key' },
                { label: '🏆 Trophy', value: 'trophy' },
                { label: '⚡ Zap', value: 'zap' },
                { label: '☀️ Sun', value: 'sun' },
                { label: '🌙 Moon', value: 'moon' },
                { label: '✨ Sparkles', value: 'sparkles' },
                { label: '🔥 Flame', value: 'flame' }
            ],
            default: 'star'
        },
        {
            key: 'size',
            type: 'select',
            label: 'Size',
            options: [
                { label: 'Small', value: 'small' },
                { label: 'Medium', value: 'medium' },
                { label: 'Large', value: 'large' },
                { label: 'Extra Large', value: 'xlarge' }
            ],
            default: 'medium'
        },
        {
            key: 'shape',
            type: 'select',
            label: 'Background Shape',
            options: [
                { label: 'None', value: 'none' },
                { label: 'Circle', value: 'circle' },
                { label: 'Rounded', value: 'rounded' },
                { label: 'Square', value: 'square' }
            ],
            default: 'none'
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
            key: 'iconColor',
            type: 'color',
            label: 'Icon Color',
            default: ''
        },
        {
            key: 'iconBgColor',
            type: 'color',
            label: 'Background Color',
            default: ''
        },
        {
            key: 'padding',
            type: 'select',
            label: 'Section Padding',
            options: [
                { label: 'None', value: 'py-0' },
                { label: 'Small', value: 'py-4' },
                { label: 'Medium', value: 'py-8' },
                { label: 'Large', value: 'py-12' }
            ],
            default: 'py-8'
        }
    ],
    defaultSettings: {
        icon: 'star',
        size: 'medium',
        shape: 'none',
        alignment: 'center',
        iconColor: '',
        iconBgColor: '',
        padding: 'py-8',
        bgColor: 'transparent',
        animation: '',
        visibility: { mobile: true, tablet: true, desktop: true }
    }
};
