export interface ThemeOption {
    label: string;
    value: string | number | boolean;
}

export interface ThemeSetting {
    type: string;
    label: string;
    category?: string;
    required?: boolean;
    min?: number;
    max?: number;
    step?: number;
    options?: ThemeOption[];
    placeholder?: string;
    description?: string;
    default?: unknown;
}

export interface ThemeManifest {
    settings_schema?: Record<string, ThemeSetting>;
    menus?: Record<string, string>;
    [key: string]: unknown;
}

export interface Theme {
    id: number | string;
    name: string;
    slug: string;
    manifest?: ThemeManifest;
    settings?: Record<string, unknown>;
    custom_css?: string;
    css_variables?: string;
    assets?: {
        css?: string[];
        js?: string[];
    };
    [key: string]: unknown;
}

export interface ThemeSection {
    id: string;
    label: string;
    settings: (ThemeSetting & { key: string })[];
}

export type ThemeSettings = Record<string, unknown>;
export type ThemeData = Theme;
