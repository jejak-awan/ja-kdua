import { ref, computed, watch, type Ref } from 'vue'
import type { BlockInstance, Canvas, PageMetadata, BuilderOptions } from '../../../../types/builder'

export interface ModalState {
    visible: boolean;
    title: string;
    message: string;
    resolve: ((value: any) => void) | null;
}

export interface ConfirmModalState extends ModalState {
    confirmText: string;
    cancelText: string;
    type: 'warning' | 'info' | 'error';
}

export interface InputModalState extends ModalState {
    placeholder: string;
    initialValue: string;
    confirmText: string;
    cancelText: string;
}

export function useBuilderState(initialData = { blocks: [] as BlockInstance[] }, options: BuilderOptions = {}) {
    const mode = ref(options.mode || 'site')

    // Content State
    const canvases = ref<Canvas[]>([
        {
            id: 'canvas-1',
            title: 'Main Canvas',
            blocks: initialData.blocks || [],
            isMain: true
        }
    ])
    const activeCanvasId = ref('canvas-1')

    // Current blocks proxy
    const blocks = computed({
        get: () => {
            const canvas = canvases.value.find((c: Canvas) => c.id === activeCanvasId.value)
            return canvas ? canvas.blocks : []
        },
        set: (val: BlockInstance[]) => {
            const canvas = canvases.value.find((c: Canvas) => c.id === activeCanvasId.value)
            if (canvas) {
                canvas.blocks = val
            }
        }
    })

    // Selection State
    const selectedModuleId = ref<string | null>(null)
    const hoveredModuleId = ref<string | null>(null)

    // UI State
    const activeTab = ref('content') // content | design | advanced
    const device = ref<'desktop' | 'tablet' | 'mobile'>('desktop')
    const deviceModeType = ref<'auto' | 'manual'>('manual')
    const customViewportWidth = ref<number | null>(null)
    const zoom = ref(100)
    const wireframeMode = ref(false)
    const gridViewMode = ref(false)
    const isFullscreen = ref(false)
    const activeTheme = ref('janari')
    const selectedThemeSlug = ref<string | null>(null)
    const themeData = ref<any>(null)
    const themeSettings = ref<Record<string, any>>({})

    const responsiveModal = ref<any>(null)
    const savePresetModal = ref({
        visible: false,
        moduleId: null as string | null,
        loading: false
    })

    const confirmModal = ref<ConfirmModalState>({
        visible: false,
        title: 'Confirm',
        message: 'Are you sure?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'warning',
        resolve: null
    })

    const inputModal = ref<InputModalState>({
        visible: false,
        title: 'Input',
        message: '',
        placeholder: '',
        initialValue: '',
        confirmText: 'OK',
        cancelText: 'Cancel',
        resolve: null
    })

    // Content Metadata
    const content = ref<PageMetadata>({
        id: null,
        title: '',
        slug: '',
        excerpt: '',
        body: '',
        status: 'draft',
        type: 'post',
        editor_type: 'builder',
        category_id: null,
        featured_image: null,
        published_at: null,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        og_image: null,
        comment_status: true,
        is_featured: false,
        tags: [],
        menu_item: {
            add_to_menu: false,
            menu_id: '',
            parent_id: null,
            title: ''
        }
    })

    // Preferences
    const PREFS_STORAGE_KEY = 'ja-builder-preferences'
    const loadPreferences = () => {
        try {
            const stored = localStorage.getItem(PREFS_STORAGE_KEY)
            return stored ? JSON.parse(stored) : {}
        } catch { return {} }
    }
    const storedPrefs = loadPreferences()

    const showGrid = ref(storedPrefs.showGrid ?? false)
    const snapToObjects = ref(storedPrefs.snapToObjects ?? true)
    const autoSave = ref(storedPrefs.autoSave ?? true)

    watch([showGrid, snapToObjects, autoSave], () => {
        localStorage.setItem(PREFS_STORAGE_KEY, JSON.stringify({
            showGrid: showGrid.value,
            snapToObjects: snapToObjects.value,
            autoSave: autoSave.value
        }))
    })

    // History
    const history = ref<string[]>([])
    const historyIndex = ref(-1)
    const maxHistory = 50

    // Pages
    const pages = ref<any[]>([])
    const currentPageId = ref<number | null>(null)
    const pagesLoading = ref(false)

    // Metadata
    const categories = ref<any[]>([])
    const availableTags = ref<any[]>([])
    const menus = ref<any[]>([])
    const availableThemes = ref<any[]>([])
    const loadingThemes = ref(false)

    // Clipboard State
    const clipboard = ref<any>(null) // { type: 'module' | 'styles', data: any }

    // Dirty State
    const lastSavedBlocks = ref(JSON.stringify(initialData.blocks || []))
    const isDirty = computed(() => JSON.stringify(blocks.value) !== lastSavedBlocks.value)

    return {
        mode,
        canvases,
        activeCanvasId,
        blocks,
        selectedModuleId,
        hoveredModuleId,
        activeTab,
        device,
        deviceModeType,
        customViewportWidth,
        zoom,
        wireframeMode,
        gridViewMode,
        isFullscreen,
        activeTheme,
        selectedThemeSlug,
        themeData,
        themeSettings,
        responsiveModal,
        savePresetModal,
        confirmModal,
        inputModal,
        content,
        showGrid,
        snapToObjects,
        autoSave,
        history,
        historyIndex,
        maxHistory,
        pages,
        currentPageId,
        pagesLoading,
        categories,
        availableTags,
        menus,
        availableThemes,
        loadingThemes,
        clipboard,
        lastSavedBlocks,
        isDirty
    }
}
