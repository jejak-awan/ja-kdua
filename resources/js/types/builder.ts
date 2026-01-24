import type { Ref, ComputedRef } from 'vue';

export interface BuilderOptions {
    mode?: 'site' | 'page';
}

export interface ModuleSettings {
    _label?: string;
    disabled?: boolean;
    [key: string]: any; // Keep flexible for module-specific settings, but define known common keys
}

export interface BlockInstance {
    id: string;
    type: string;
    settings: ModuleSettings;
    children?: BlockInstance[];
}

export interface Canvas {
    id: string;
    title: string;
    blocks: BlockInstance[];
    isMain: boolean;
    isGlobal?: boolean;
    append?: boolean;
}

export interface PageMetadata {
    id: number | null;
    title: string;
    slug: string;
    status: string;
    excerpt?: string;
    body?: string;
    type?: string;
    editor_type?: string;
    category_id?: number | null;
    featured_image?: string | null;
    published_at?: string | null;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    og_image?: string | null;
    comment_status?: boolean;
    is_featured?: boolean;
    tags?: any[];
    menu_item?: {
        add_to_menu: boolean;
        menu_id: string;
        parent_id: number | null;
        title: string;
    };
    [key: string]: any;
}

export interface HistorySnapshot {
    blocks: string; // JSON string for now
    timestamp: number;
}

export interface BuilderState {
    mode: Ref<string>;
    canvases: Ref<Canvas[]>;
    activeCanvasId: Ref<string>;
    blocks: Ref<BlockInstance[]>; // Computable refs usually typed as Ref in consumers
    history: Ref<string[]>;
    historyIndex: Ref<number>;
    maxHistory: number;
    selectedModuleId: Ref<string | null>;
    hoveredModuleId: Ref<string | null>;
    activeTab: Ref<string>;
    device: Ref<'desktop' | 'tablet' | 'mobile'>;
    deviceModeType: Ref<'auto' | 'manual'>;
    customViewportWidth: Ref<number | null>;
    zoom: Ref<number>;
    wireframeMode: Ref<boolean>;
    gridViewMode: Ref<boolean>;
    isFullscreen: Ref<boolean>;
    activeTheme: Ref<string>;
    themeData: Ref<any>;
    themeSettings: Ref<Record<string, any>>;
    responsiveModal: Ref<any>;
    savePresetModal: Ref<any>;
    confirmModal: Ref<any>;
    inputModal: Ref<any>;
    content: Ref<PageMetadata>;
    showGrid: Ref<boolean>;
    snapToObjects: Ref<boolean>;
    autoSave: Ref<boolean>;
    pages: Ref<any[]>;
    currentPageId: Ref<number | null>;
    pagesLoading: Ref<boolean>;
    categories: Ref<any[]>;
    availableTags: Ref<any[]>;
    menus: Ref<any[]>;
    availableThemes: Ref<any[]>;
    loadingThemes: Ref<boolean>;
    clipboard: Ref<any>;
    lastSavedBlocks: Ref<string>;
    isDirty: ComputedRef<boolean>;
}

// Module Management Interface
export interface ModuleManager {
    selectedModule: ComputedRef<BlockInstance | null>;
    modulePath: ComputedRef<BlockInstance[]>;
    selectModule: (id: string | null) => void;
    hoverModule: (id: string | null) => void;
    clearSelection: () => void;
    insertModule: (type: string, parentId?: string | null, index?: number) => BlockInstance | null;
    insertFromPreset: (preset: any, parentId?: string | null, index?: number) => BlockInstance | null;
    removeModule: (id: string) => boolean;
    duplicateModule: (id: string) => BlockInstance | null;
    moveModule: (id: string, direction: 'up' | 'down') => boolean;
    updateModuleSettings: (id: string, settings: ModuleSettings) => boolean;
    updateModule: (id: string, data: Partial<BlockInstance>) => boolean;
    updateModuleSetting: (id: string, key: string, value: any) => boolean;
    applyPreset: (id: string, preset: any) => boolean;
    resetModuleStyles: (id: string) => boolean;
    updateRowLayout: (rowId: string, layout: any) => boolean;
    copyModule: (id: string) => boolean;
    pasteModule: (parentId: string | null, index?: number) => BlockInstance | null;
    copyStyles: (id: string) => boolean;
    pasteStyles: (id: string) => boolean;
    resetLayout: () => void;
    findModule: (id: string) => BlockInstance | null;
    findParentById: (items: BlockInstance[], id: string) => BlockInstance | null;
}

export interface BuilderInstance extends BuilderState, ModuleManager {
    // History
    canUndo: ComputedRef<boolean>;
    canRedo: ComputedRef<boolean>;
    takeSnapshot: (options?: { immediate?: boolean; delay?: number }) => void;
    undo: () => boolean;
    redo: () => boolean;

    // UI & Sync
    closeResponsiveModal: () => void;
    closeSavePresetModal: () => void;
    closeConfirmModal: (result: boolean) => void;
    closeInputModal: (result: string | null) => void;

    // Canvas Operations
    addCanvas: (title: string) => void;
    removeCanvas: (id: string) => void;
    renameCanvas: (id: string, title: string) => void;
    switchCanvas: (id: string) => void;
    duplicateCanvas: (id: string) => void;
    exportCanvas: (id: string) => void;
    setMainCanvas: (id: string) => void;

    // Helpers
    getModuleDefinition: (type: string) => any;
    globalVariables: any;
    loadTheme: (slug?: string | null) => Promise<void>;
    handleSavePreset: (name: string) => Promise<void>;
    confirm: (options: any) => Promise<boolean>;
    prompt: (options: any) => Promise<string | null>;

    // External
    markAsSaved: () => void;
    loadContent: (id: string | number) => Promise<void>;
    fetchMetadata: () => Promise<void>;
    setDeviceMode: (mode: 'desktop' | 'tablet' | 'mobile') => void;

    // Modals helpers (runtime injected)
    openIconPickerModal?: (value: string, onSelect: (icon: string) => void) => void;
    openAddCanvasModal?: () => void;
    openImportExportModal?: (id: string) => void;
    openSavePresetModal?: (moduleId: string) => void;
    openInsertModal?: (targetId: string | null, index?: number) => void;
    openInsertRowModal?: (targetId: string | null) => void;
    openUpdateRowModal?: (rowId: string | null) => void;
    openInsertSectionModal?: (index?: number) => void;
    openStructureTemplateModal?: (targetId: string | null, targetType: string | null) => void;
    openContextMenu?: (moduleId: string, event: MouseEvent, title?: string, type?: string, mode?: string) => void;

    // Presets
    presets: Ref<any[]>;
    loadingPresets: Ref<boolean>;
    fetchPresets: () => Promise<void>;
    savePreset: (module: any, name: string) => Promise<any>;
    deletePreset: (id: number | string) => Promise<boolean | undefined>;
}

export interface BlockProps {
    module: BlockInstance;
    mode: 'edit' | 'view';
    device?: 'desktop' | 'tablet' | 'mobile' | null;
}

export interface SettingOption {
    label: string;
    value: any;
    icon?: any;
}

export interface SettingDefinition {
    key?: string;
    type: string;
    label: string;
    default?: any;
    options?: SettingOption[];
    tab?: 'content' | 'style' | 'advanced';
    condition?: (settings: ModuleSettings) => boolean;
    [key: string]: any;
}

export interface BlockDefinition {
    name: string;
    label: string;
    icon?: any;
    component: any;
    settings?: SettingDefinition[];
    defaultSettings?: Record<string, any>;
    category?: string;
    description?: string;
}

export interface ModuleField {
    name: string;
    type: string;
    label: string;
    default?: any;
    responsive?: boolean;
    options?: SettingOption[] | string;
    min?: number;
    max?: number;
    step?: number;
    unit?: string;
    units?: string[];
    placeholder?: string;
    multiple?: boolean;
    searchable?: boolean;
    show_if?: { field: string; value: any | any[] };
    [key: string]: any;
}

export interface ModuleGroup {
    id: string;
    label: string;
    fields: (ModuleField | ModuleGroup | any)[];
    presets?: boolean;
    condition?: (settings: any) => boolean;
}

export interface ModuleDefinition {
    name: string;
    title: string;
    icon: any;
    category: string;
    children?: string[] | null;
    defaults: Record<string, any>;
    settings: {
        content: (ModuleGroup | any)[];
        design: (ModuleGroup | any)[];
        advanced: (ModuleGroup | any)[];
    };
    [key: string]: any;
}
