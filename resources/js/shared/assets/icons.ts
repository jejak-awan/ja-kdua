import type { IconCategory } from '@/types/assets';
import * as LucideIcons from 'lucide-vue-next'

// Filter out internal Lucide tools
export const allIcons = Object.keys(LucideIcons).filter(k =>
    k.length > 2 &&
    k !== 'createLucideIcon' &&
    !k.startsWith('Icon') &&
    typeof (LucideIcons as any)[k] === 'object'
) as string[]

export const commonIcons: string[] = [
    // Navigation & Direction
    'ChevronUp', 'ChevronDown', 'ChevronLeft', 'ChevronRight',
    'ChevronsUp', 'ChevronsDown', 'ChevronsLeft', 'ChevronsRight',
    'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight',
    'ArrowUpCircle', 'ArrowDownCircle', 'ArrowLeftCircle', 'ArrowRightCircle',
    'MoveUp', 'MoveDown', 'MoveLeft', 'MoveRight',

    // Basic Actions
    'Plus', 'Minus', 'X', 'Check', 'Search', 'Settings', 'Home', 'User',
    'Bell', 'Calendar', 'Camera', 'Video', 'Mail', 'Trash2', 'Edit3',
    'ExternalLink', 'Link', 'Share2', 'Download', 'Upload', 'RefreshCw',

    // Shapes & Symbols
    'Circle', 'Square', 'Triangle', 'Star', 'Heart', 'Shield', 'HelpCircle',
    'AlertCircle', 'Info', 'CheckCircle2', 'PlusCircle', 'XCircle',

    // Interface
    'Menu', 'Grid', 'Layout', 'Columns', 'Rows', 'Maximize2', 'Minimize2',
    'Eye', 'EyeOff', 'Lock', 'Unlock', 'Key', 'Flag', 'MapPin', 'Clock'
]

export const categories: IconCategory[] = [
    {
        id: 'navigation',
        label: 'Navigation',
        icons: ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ChevronUp', 'ChevronDown', 'ChevronLeft', 'ChevronRight', 'ChevronsUp', 'ChevronsDown', 'ChevronsLeft', 'ChevronsRight', 'MoveUp', 'MoveDown', 'MoveLeft', 'MoveRight', 'ArrowBigUp', 'ArrowBigDown', 'ArrowUpCircle', 'ArrowDownCircle']
    },
    {
        id: 'communication',
        label: 'Communication',
        icons: ['Mail', 'MessageSquare', 'MessageCircle', 'Phone', 'Send', 'Share2', 'AtSign', 'Paperclip', 'Bell', 'Languages']
    },
    {
        id: 'media',
        label: 'Media',
        icons: ['Image', 'Video', 'Music', 'Play', 'Pause', 'Square', 'Film', 'Camera', 'Mic', 'Headphones', 'Speaker', 'Volume2']
    },
    {
        id: 'interface',
        label: 'Interface',
        icons: ['Menu', 'Grid', 'Layout', 'Settings', 'Search', 'Filter', 'Maximize2', 'Minimize2', 'Layers', 'GripHorizontal', 'MoreHorizontal', 'Sliders']
    },
    {
        id: 'actions',
        label: 'Actions',
        icons: ['Plus', 'Minus', 'X', 'Check', 'Trash2', 'Edit3', 'Save', 'Copy', 'RefreshCw', 'Download', 'Upload', 'Power', 'LogOut', 'ExternalLink']
    },
    {
        id: 'status',
        label: 'Status & Help',
        icons: ['HelpCircle', 'Info', 'AlertCircle', 'AlertTriangle', 'Shield', 'ShieldCheck', 'ShieldAlert', 'CheckCircle2', 'PlusCircle', 'XCircle', 'Circle', 'Lock', 'Unlock']
    }
]
