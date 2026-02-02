import type { Category, Tag } from './taxonomy';
import type { Media } from './media';
import type { Menu, MenuItem } from './menu';
import type { SiteSettings } from './settings';
import type { BlockInstance } from './builder';

export * from './taxonomy';
export * from './media';
export * from './settings';
export type { Menu, MenuItem };

export interface ContentForm {
    title: string;
    slug: string;
    type: 'post' | 'page' | 'custom' | string;
    status: 'draft' | 'published' | 'scheduled' | 'archived' | 'pending' | 'trashed' | string;
    excerpt?: string;
    body?: string; // content alias
    content?: string;
    featured_image?: string | null;
    category_id?: number | string | null;
    published_at?: string;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    og_image?: string | null;
    comment_status?: boolean;
    is_featured?: boolean;
    blocks?: BlockInstance[];
    tags?: Tag[];
    menu_item?: MenuItem;
    menu_items?: MenuItem[];
    editor_type?: 'classic' | 'builder' | null;
}

export interface Content extends ContentForm {
    id: number;
    author_id?: number;
    author?: {
        id: number;
        name: string;
        email: string;
    } | null;
    category?: Category | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    lock_status?: {
        is_locked: boolean;
        locked_by?: number;
        locked_at?: string;
    } | null;
}

export interface CMSState {
    contents: Content[];
    categories: Category[];
    tags: Tag[];
    media: Media[];
    settings: Record<string, string | number | boolean | null>;
    siteSettings: SiteSettings;
    currentContent: Content | null;
    loading: boolean;
    loadingGroups: Record<string, boolean>;
    settingsPromises: Record<string, Promise<unknown>>;
    themeMode: 'light' | 'dark' | 'system';
    isDarkMode: boolean;
}

export interface ContentTemplate {
    id: number;
    name: string;
    slug: string;
    description?: string;
    type: 'post' | 'page' | 'custom';
    title_template: string;
    body_template: string;
    excerpt_template: string;
    created_at?: string;
    updated_at?: string;
}
