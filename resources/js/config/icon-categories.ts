export const iconCategories: Record<string, string[]> = {
    'General': [
        'Home', 'Menu', 'Search', 'Settings', 'User', 'Users', 'Bell', 'Calendar',
        'Clock', 'Heart', 'Star', 'MoreHorizontal', 'MoreVertical', 'Plus', 'Minus',
        'X', 'Check', 'Trash', 'Edit', 'Filter', 'Sort'
    ],
    'Communication': [
        'Mail', 'MessageSquare', 'MessageCircle', 'Phone', 'Send', 'Share', 'Share2',
        'Inbox', 'BellRing', 'Megaphone', 'AtSign'
    ],
    'Media': [
        'Image', 'Video', 'Music', 'Camera', 'Mic', 'Headphones', 'Play', 'Pause',
        'SkipForward', 'SkipBack', 'Volume2', 'VolumeX', 'Film', 'Disc', 'Speaker'
    ],
    'Editor': [
        'Type', 'Bold', 'Italic', 'Underline', 'AlignLeft', 'AlignCenter', 'AlignRight',
        'List', 'ListOrdered', 'Link', 'Image', 'Table', 'Code', 'Quote', 'Heading'
    ],
    'Arrows': [
        'ArrowRight', 'ArrowLeft', 'ArrowUp', 'ArrowDown',
        'ChevronRight', 'ChevronLeft', 'ChevronUp', 'ChevronDown',
        'ChevronRightSquare', 'ChevronLeftSquare',
        'ArrowUpRight', 'ArrowDownRight', 'RefreshCw', 'RotateCw'
    ],
    'Files': [
        'File', 'FileText', 'Folder', 'FolderOpen', 'Save', 'Download', 'Upload',
        'Clipboard', 'Copy', 'Scissors', 'Archive', 'Paperclip'
    ],
    'Commerce': [
        'ShoppingCart', 'ShoppingBag', 'CreditCard', 'Wallet', 'DollarSign', 'Percent',
        'Tag', 'Gift', 'Package', 'Truck'
    ],
    'Devices': [
        'Monitor', 'Smartphone', 'Tablet', 'Laptop', 'Watch', 'Printer', 'Wifi',
        'Battery', 'HardDrive', 'Cpu', 'Server'
    ],
    'Weather': [
        'Sun', 'Moon', 'Cloud', 'CloudRain', 'CloudSnow', 'CloudLightning',
        'Wind', 'Umbrella', 'Thermometer'
    ]
};

// Icons that should appear in "General" even if not explicitly listed above, 
// usually populating from a base common list if we had one.
// For now, the implementation will default to showing "All" if no category matches,
// but we want Tabs: [All, General, Communication, ...]
