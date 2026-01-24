export interface SiteSettings {
    site_name: string;
    site_description: string;
    site_url: string;
    admin_email: string;
    site_version: string;
    site_logo: string;
    site_favicon: string;
    [key: string]: any;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    parent_id?: number | null;
    [key: string]: any;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
    [key: string]: any;
}

export interface Media {
    id: number;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    url: string;
    thumbnail_url?: string;
    folder_id?: number | null;
    alt?: string;
    description?: string;
    is_shared?: boolean;
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface MediaFolder {
    id: number;
    name: string;
    parent_id?: number | null;
    children?: MediaFolder[];
    children_count?: number;
    is_trashed?: boolean;
    is_shared?: boolean;
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface Content {
    id: number;
    title: string;
    slug: string;
    content?: string;
    excerpt?: string;
    status?: string;
    author_id?: number;
    created_at?: string;
    updated_at?: string;
    [key: string]: any;
}

export interface CMSState {
    contents: Content[];
    categories: Category[];
    tags: Tag[];
    media: any[];
    settings: Record<string, any>;
    siteSettings: SiteSettings;
    currentContent: Content | null;
    loading: boolean;
    loadingGroups: Record<string, boolean>;
    settingsPromises: Record<string, Promise<any>>;
    themeMode: 'light' | 'dark' | 'system';
    isDarkMode: boolean;
}
