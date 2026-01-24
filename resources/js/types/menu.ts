export interface Menu {
    id: number;
    name: string;
    location: string;
    locale?: string;
    is_active: boolean;
    items?: MenuItem[];
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface MenuItem {
    id: number | null;
    _temp_id?: string;
    parent_id?: number | null;
    menu_id?: number;
    title: string;
    type: string; // 'custom', 'post', 'page', 'category', 'system'
    target_id?: number | null;
    url?: string;
    icon?: string | null;
    css_class?: string | null;
    description?: string | null;
    badge?: string | null;
    badge_color?: string | null;
    open_in_new_tab?: boolean;
    is_active?: number | boolean;
    sort_order?: number;

    // Media / Mega Menu
    image?: string | null;
    image_size?: string;
    mega_menu_layout?: string;
    mega_menu_column?: number;
    mega_menu_show_dividers?: boolean;

    // Display
    hide_label?: boolean;
    heading?: string | null;
    show_heading_line?: boolean;

    children?: MenuItem[];
    [key: string]: any;
}

export interface MenuItemDTO {
    id?: number | null;
    parent_id?: number | null;
    menu_id?: number;
    sort_order?: number;
    title: string;
    type: string;
    target_id?: number | null;
    url?: string;
    icon?: string | null;
    css_class?: string | null;
    description?: string | null;
    badge?: string | null;
    badge_color?: string | null;
    open_in_new_tab?: boolean;
    is_active?: number | boolean;
    image?: string | null;
    image_size?: string;
    mega_menu_layout?: string;
    mega_menu_column?: number;
    mega_menu_show_dividers?: boolean;
    hide_label?: boolean;
    heading?: string | null;
    show_heading_line?: boolean;
}

export interface MenuItemSetting {
    key: string;
    type: 'text' | 'textarea' | 'select' | 'boolean' | 'number' | 'color' | 'icon_picker' | 'media' | 'data_select';
    label: string;
    required?: boolean;
    default?: any;
    placeholder?: string;
    description?: string;
    options?: { label: string; value: any }[];
    group?: 'appearance' | 'mega_menu' | 'badge' | string;
    min?: number;
    max?: number;
    source?: string;
    labelField?: string;
    valueField?: string;
}

export interface MenuItemDataSource {
    endpoint: string;
    labelField: string;
    valueField: string;
}

export interface MenuItemDefinition {
    name: string;
    label: string;
    category: 'basic' | 'content' | 'structure' | string;
    icon: any; // Component
    color: string;
    description: string;
    defaultTitle: string;
    dataSource?: MenuItemDataSource;
    settings: MenuItemSetting[];
}
