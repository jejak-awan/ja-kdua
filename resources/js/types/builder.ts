import type { Ref, ComputedRef, Component } from 'vue';
import type { Category, Tag } from './taxonomy';
import type { Menu } from './menu';
import type { ThemeData, ThemeSettings } from './theme';

export interface BuilderOptions {
    mode?: 'site' | 'page';
}

export interface ModuleSettings {
    _label?: string;
    disabled?: boolean;
    [key: string]: any;
}

export interface BlockInstance {
    id: string;
    type: string;
    settings: ModuleSettings;
    children?: BlockInstance[];
    data?: any; // Added for compatibility
    sourceType?: string; // Added for compatibility
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
    tags?: Tag[];
    menu_item?: {
        add_to_menu: boolean;
        menu_id: string;
        parent_id: number | null;
        title: string;
    };
    [key: string]: any;
}

export interface GlobalSettings {
    container_max_width: string;
    block_spacing: string;
    [key: string]: any;
}

export interface BuilderHistoryEntry {
    blocks: BlockInstance[];
    globalSettings: GlobalSettings;
    editingIndex: number | null;
    activeBlockId: string | null;
}

export interface HistorySnapshot {
    blocks: string;
    timestamp: number;
}

export interface BuilderState {
    mode: Ref<string>;
    canvases: Ref<Canvas[]>;
    activeCanvasId: Ref<string>;
    blocks: Ref<BlockInstance[]>;
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
    selectedThemeSlug: Ref<string | null>;
    themeData: Ref<ThemeData | null>;
    themeSettings: Ref<ThemeSettings>;
    responsiveModal: Ref<any>; // Changed back to any to fix cast issues in templates
    savePresetModal: Ref<any>;
    confirmModal: Ref<any>;
    inputModal: Ref<any>;
    content: Ref<PageMetadata>;
    showGrid: Ref<boolean>;
    snapToObjects: Ref<boolean>;
    autoSave: Ref<boolean>;
    pages: Ref<PageMetadata[]>;
    currentPageId: Ref<number | null>;
    pagesLoading: Ref<boolean>;
    categories: Ref<Category[]>;
    availableTags: Ref<Tag[]>;
    menus: Ref<Menu[]>;
    availableThemes: Ref<ThemeData[]>;
    loadingThemes: Ref<boolean>;
    clipboard: Ref<{ type: string; data: any; sourceType?: string } | null>;
    dataVersion: Ref<number>;
    lastSavedVersion: Ref<number>;
    markAsDirty: () => void;
    isDirty: ComputedRef<boolean>;
}

export interface SettingOption {
    label?: string;
    value: any;
    icon?: any;
    group?: string;
}

export interface SettingDefinition {
    key?: string;
    name?: string; // Alias for key
    type: string;
    label: string;
    default?: any;
    options?: SettingOption[] | string;
    tab?: 'content' | 'style' | 'advanced';
    condition?: (settings: any) => boolean;
    show_if?: { field: string; value: any } | any;
    responsive?: boolean;
    multiple?: boolean;
    searchable?: boolean;
    itemLabel?: string;
    units?: string[];
    unit?: string;
    min?: number;
    max?: number;
    step?: number;
    [key: string]: any;
}

export type ModuleField = SettingDefinition;

export interface ModuleGroup {
    id: string;
    label: string;
    description?: string;
    presets?: boolean;
    fields: (ModuleField | any)[];
    condition?: (settings: any) => boolean;
}

export interface BlockDefinition {
    type?: string;
    title?: string;
    name?: string;
    label?: string;
    description?: string;
    icon?: any;
    category?: string;
    component?: Component | any;
    settingsComponent?: Component;
    defaultSettings?: Record<string, any> | null;
    defaults?: Record<string, any> | null; // Legacy alias for defaultSettings
    canHaveChildren?: boolean;
    allowedChildren?: string[];
    children?: string[] | null; // Legacy alias for allowedChildren/canHaveChildren hint
    settings?: SettingDefinition[] | any;
    parent?: string[] | string | null;
}

// Alias for BlockDefinition
export type ModuleDefinition = BlockDefinition;

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

    // Management/Registry Parts
    definitions: Ref<Record<string, BlockDefinition>>;
    moduleCategories: Ref<string[]>;
    loadingModules: Ref<boolean>;
    registerModule: (definition: BlockDefinition) => void;
    getModuleDefinition: (type: string) => BlockDefinition | undefined;
    fetchTemplates: () => Promise<any[]>;
    fetchWidgets: () => Promise<any[]>;
}

export interface GlobalVariablesManager {
    globalNumbers: Ref<any[]>;
    globalText: Ref<any[]>;
    globalImages: Ref<any[]>;
    globalLinks: Ref<any[]>;
    globalColors: Ref<any[]>;
    globalFonts: Ref<any[]>;
    addVariable: (type: string) => void;
    deleteVariable: (index: number, type: string) => void;
    loadVariables: (data: any) => void;
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
    globalVariables: GlobalVariablesManager;
    saveGlobalVariables: () => Promise<void>;
    globalAction: Ref<any>;
    loadTheme: (slug?: string | null) => Promise<void>;
    handleSavePreset: (name: string) => Promise<void>;
    confirm: (options: any) => Promise<boolean>;
    prompt: (options: any) => Promise<string | null>;
    openResponsiveModal: (config: any) => void;
    fetchThemes: () => Promise<void>; // Restored
    updateThemeSettings: (themeSlug: string, settings: any) => Promise<void>; // Restored
    fetchTemplates: () => Promise<any[]>;
    createTemplate: (data: { name: string; type: string }) => Promise<any>;
    deleteTemplate: (id: string | number) => Promise<boolean>;
    updateContentMeta: (id: string | number, meta: any) => Promise<any>;
    savePreset: (module: BlockInstance, name: string) => Promise<BuilderPreset>;
    deletePreset: (id: string | number) => Promise<boolean | void>;
    getComponent: (type: string) => any;


    // External
    markAsSaved: () => void;
    loadContent: (id: string | number) => Promise<void>;
    fetchMetadata: () => Promise<void>;
    setDeviceMode: (mode: 'desktop' | 'tablet' | 'mobile') => void;
    applyThemeStyles: () => void;

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

    // Presets & Dynamic Content
    insertFromPreset: (preset: any, parentId?: string | null, index?: number) => BlockInstance | null;
    applyPreset: (id: string, preset: any) => boolean;
    fetchPresets: () => Promise<void>;
    presets: Ref<any[]>;
    loadingPresets: Ref<boolean>;
}

export interface BuilderPreset {
    id: string;
    name: string;
    type: string;
    settings: ModuleSettings;
    category?: string;
    image?: string;
}

export interface PresetManager {
    presets: Ref<BuilderPreset[]>;
    loading: Ref<boolean>;
    fetchPresets: (type?: string) => Promise<void>;
    savePreset: (module: BlockInstance, name: string) => Promise<BuilderPreset>;
    deletePreset: (id: string) => Promise<void>;
}

export interface GlobalVariable {
    id: string;
    name: string;
    slug: string;
    type: string;
    value: string | number | boolean | Record<string, any>;
    group?: string;
}

export interface BuilderContext {
    state: BuilderState;
    availableVariables: Ref<GlobalVariable[]>;
    colors: Ref<GlobalVariable[]>;
    fonts: Ref<GlobalVariable[]>;
    spacings: Ref<GlobalVariable[]>;
    containers: Ref<GlobalVariable[]>;

    // Actions
    save: (data?: any) => Promise<void>;
    undo: () => void;
    redo: () => void;
    preview: () => void;

    // Modals
    confirm: (options: { title: string, message: string, variant?: string }) => Promise<boolean>;
    prompt: (options: { title: string, label: string, defaultValue?: string }) => Promise<string | null>;
    openResponsiveModal: (config: Record<string, any>) => void;
}

export interface DragData {
    type: 'module' | 'block' | 'section';
    id: string;
    data?: BlockInstance | Record<string, any>;
}

export interface BlockProps {
    module: BlockInstance;
    mode: 'edit' | 'view';
    device?: 'desktop' | 'tablet' | 'mobile';
    manualStyles?: boolean;
    settings?: ModuleSettings;
    nestedBlocks?: BlockInstance[];
    context?: any;
    id?: string;
}
