import { ref, computed, watch, triggerRef } from 'vue'
import ModuleRegistry from './ModuleRegistry'
import api from '@/services/api'
import { useTheme } from '@/composables/useTheme'

export default function useBuilder(initialData = { blocks: [] }) {
    const {
        activeTheme: globalActiveTheme,
        themeSettings: globalThemeSettings,
        themeAssets: globalThemeAssets,
        applyThemeStyles
    } = useTheme()
    // ============================================
    // STATE
    // ============================================

    // Content State
    const canvases = ref([
        {
            id: 'canvas-1',
            title: 'Main Canvas',
            blocks: initialData.blocks || [],
            isMain: true
        }
    ])
    const activeCanvasId = ref('canvas-1')

    // Current blocks proxy (backward compatibility and reactive source for editor)
    const blocks = computed({
        get: () => {
            const canvas = canvases.value.find(c => c.id === activeCanvasId.value)
            return canvas ? canvas.blocks : []
        },
        set: (val) => {
            const canvas = canvases.value.find(c => c.id === activeCanvasId.value)
            if (canvas) {
                canvas.blocks = val
            }
        }
    })

    // Selection State
    const selectedModuleId = ref(null)
    const hoveredModuleId = ref(null)

    // UI State
    const activeTab = ref('content') // content | design | advanced
    const device = ref('desktop') // desktop | tablet | mobile
    const deviceModeType = ref('manual') // auto | manual - default to manual (desktop)
    const customViewportWidth = ref(null) // null | number (px)
    const zoom = ref(100)
    const wireframeMode = ref(false)
    const gridViewMode = ref(false)
    const isFullscreen = ref(false)
    const activeTheme = ref('janari') // Default theme
    const themeData = ref(null)
    const themeSettings = ref({})
    const responsiveModal = ref(null) // { label, baseKey, type, module, settings }

    // Content Metadata State (for integration with CMS)
    const content = ref({
        id: null,
        title: '',
        slug: '',
        excerpt: '',
        status: 'draft',
        type: 'post',
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

    // History State
    const history = ref([])
    const historyIndex = ref(-1)
    const maxHistory = 50

    // Pages State (Live)
    const pages = ref([])
    const currentPageId = ref(null)
    const pagesLoading = ref(false)

    // Metadata for selection
    const categories = ref([])
    const availableTags = ref([])
    const menus = ref([])

    // ============================================
    // COMPUTED
    // ============================================

    const selectedModule = computed(() => {
        if (!selectedModuleId.value) return null
        return findModuleById(blocks.value, selectedModuleId.value)
    })

    const canUndo = computed(() => historyIndex.value > 0)
    const canRedo = computed(() => historyIndex.value < history.value.length - 1)

    const modulePath = computed(() => {
        if (!selectedModuleId.value) return []
        return getModulePath(blocks.value, selectedModuleId.value)
    })

    const currentPage = computed(() => {
        return pages.value.find(p => p.id === currentPageId.value) || pages.value[0]
    })

    // ============================================
    // METHODS - Page Operations (Live)
    // ============================================

    async function fetchPages() {
        pagesLoading.value = true
        try {
            const response = await api.get('/admin/ja/contents', {
                params: { type: 'page', per_page: 100 }
            })
            const data = response.data?.data || response.data
            pages.value = (data.data || data || []).map(p => ({
                id: p.id,
                title: p.title,
                slug: p.slug,
                status: p.status
            }))
        } catch (error) {
            console.error('Failed to fetch pages:', error)
        } finally {
            pagesLoading.value = false
        }
    }

    async function setCurrentPage(id) {
        if (currentPageId.value === id) return

        clearSelection()
        try {
            await loadContent(id)
            currentPageId.value = id
        } catch (error) {
            console.error('Failed to switch page:', error)
        }
    }

    async function addPage(title) {
        try {
            const payload = {
                title,
                type: 'page',
                status: 'draft',
                editor_type: 'builder',
                blocks: []
            }
            const response = await api.post('/admin/ja/contents', payload)
            const newPage = response.data?.data || response.data

            if (newPage) {
                await fetchPages()
                await setCurrentPage(newPage.id)
            }
            return newPage
        } catch (error) {
            const message = error.response?.data?.message || error.message || 'Failed to create page'
            console.error('Failed to create page:', error)
            // If we had a toast system, we'd use it here
            throw new Error(message)
        }
    }

    async function loadContent(id) {
        try {
            const response = await api.get(`/admin/ja/contents/${id}`)
            const data = response.data?.data || response.data

            if (data) {
                content.value = {
                    id: data.id,
                    title: data.title || '',
                    slug: data.slug || '',
                    excerpt: data.excerpt || '',
                    status: data.status || 'draft',
                    type: data.type || 'post',
                    category_id: data.category_id || null,
                    featured_image: data.featured_image || null,
                    published_at: data.published_at || null,
                    meta_title: data.meta_title || '',
                    meta_description: data.meta_description || '',
                    meta_keywords: data.meta_keywords || '',
                    og_image: data.og_image || null,
                    comment_status: data.comment_status !== undefined ? data.comment_status : true,
                    is_featured: !!data.is_featured,
                    tags: data.tags || [],
                    menu_item: {
                        add_to_menu: false,
                        menu_id: '',
                        parent_id: null,
                        title: ''
                    }
                }

                if (data.menu_items && data.menu_items.length > 0) {
                    const menuItem = data.menu_items[0]
                    content.value.menu_item = {
                        add_to_menu: true,
                        menu_id: menuItem.menu_id,
                        parent_id: menuItem.parent_id,
                        title: menuItem.title
                    }
                }

                if (data.blocks) {
                    blocks.value = data.blocks
                    triggerRef(blocks)
                    takeSnapshot()
                }
            }
            return data
        } catch (error) {
            console.error('Failed to load content for builder:', error)
            throw error
        }
    }

    async function saveContent() {
        if (!content.value.id) return false

        try {
            const payload = {
                ...content.value,
                blocks: blocks.value
            }

            if (content.value.tags) {
                payload.tags = content.value.tags.filter(t => t.id).map(t => t.id)
                payload.new_tags = content.value.tags.filter(t => !t.id).map(t => t.name)
            }

            const response = await api.put(`/admin/ja/contents/${content.value.id}`, payload)
            return response.data
        } catch (error) {
            console.error('Failed to save content from builder:', error)
            throw error
        }
    }

    async function fetchMetadata() {
        try {
            const [catsRes, tagsRes, menusRes] = await Promise.all([
                api.get('/admin/ja/categories'),
                api.get('/admin/ja/tags'),
                api.get('/admin/ja/menus')
            ])

            const ensureArray = (response) => {
                const data = response?.data?.data || response?.data || []
                return Array.isArray(data) ? data : []
            }

            categories.value = ensureArray(catsRes)
            availableTags.value = ensureArray(tagsRes)
            menus.value = ensureArray(menusRes)
        } catch (error) {
            console.error('Failed to fetch builder metadata:', error)
        }
    }

    function selectModule(id) {
        selectedModuleId.value = id
    }

    function hoverModule(id) {
        hoveredModuleId.value = id
    }

    function clearSelection() {
        selectedModuleId.value = null
    }

    // ============================================
    // METHODS - Module Operations
    // ============================================

    function insertModule(type, parentId = null, index = -1) {
        const instance = ModuleRegistry.createInstance(type)
        if (!instance) return null

        if (parentId) {
            const parent = findModuleById(blocks.value, parentId)
            if (parent && parent.children) {
                if (index >= 0) {
                    parent.children.splice(index, 0, instance)
                } else {
                    parent.children.push(instance)
                }
            }
        } else {
            if (index >= 0) {
                blocks.value.splice(index, 0, instance)
            } else {
                blocks.value.push(instance)
            }
        }

        takeSnapshot()
        selectModule(instance.id)
        return instance
    }

    function removeModule(id) {
        const removed = removeModuleById(blocks.value, id)
        if (removed) {
            takeSnapshot()
            if (selectedModuleId.value === id) {
                clearSelection()
            }
        }
        return removed
    }

    function duplicateModule(id) {
        const module = findModuleById(blocks.value, id)
        if (!module) return null

        const duplicate = JSON.parse(JSON.stringify(module))
        duplicate.id = ModuleRegistry.generateId()
        regenerateIds(duplicate)

        // Find parent and insert after original
        const parent = findParentById(blocks.value, id)
        const siblings = parent ? parent.children : blocks.value
        const index = siblings.findIndex(m => m.id === id)

        siblings.splice(index + 1, 0, duplicate)
        takeSnapshot()
        selectModule(duplicate.id)

        return duplicate
    }

    function moveModule(id, direction) {
        const parent = findParentById(blocks.value, id)
        const siblings = parent ? parent.children : blocks.value
        const index = siblings.findIndex(m => m.id === id)

        if (index < 0) return false

        const newIndex = direction === 'up' ? index - 1 : index + 1
        if (newIndex < 0 || newIndex >= siblings.length) return false

        const [module] = siblings.splice(index, 1)
        siblings.splice(newIndex, 0, module)
        takeSnapshot()

        return true
    }

    function updateModuleSettings(id, settings) {
        const module = findModuleById(blocks.value, id)
        if (!module) return false

        // Create a new settings object to trigger Vue reactivity
        module.settings = { ...module.settings, ...settings }

        // Force Vue to detect the change in nested objects
        triggerRef(blocks)

        takeSnapshot()
        return true
    }

    // ============================================
    // METHODS - Clipboard
    // ============================================

    const clipboard = ref(null) // { type: 'module' | 'styles', data: any }

    function copyModule(id) {
        const module = findModuleById(blocks.value, id)
        if (!module) return

        // Deep clone to avoid reference issues
        clipboard.value = {
            type: 'module',
            data: JSON.parse(JSON.stringify(module))
        }

        return true
    }

    function pasteModule(parentId, index = -1) {
        if (!clipboard.value || clipboard.value.type !== 'module') return null

        const duplicate = JSON.parse(JSON.stringify(clipboard.value.data))
        duplicate.id = ModuleRegistry.generateId()
        regenerateIds(duplicate)

        if (parentId) {
            const parent = findModuleById(blocks.value, parentId)
            if (parent && parent.children) {
                if (index >= 0) {
                    parent.children.splice(index, 0, duplicate)
                } else {
                    parent.children.push(duplicate)
                }
            }
        } else {
            if (index >= 0) {
                blocks.value.splice(index, 0, duplicate)
            } else {
                blocks.value.push(duplicate)
            }
        }

        takeSnapshot()
        return duplicate
    }

    function copyStyles(id) {
        const module = findModuleById(blocks.value, id)
        if (!module || !module.settings) return

        clipboard.value = {
            type: 'styles',
            sourceType: module.type,
            data: JSON.parse(JSON.stringify(module.settings.design || {}))
        }
        return true
    }

    function pasteStyles(id) {
        if (!clipboard.value || clipboard.value.type !== 'styles') return false

        const module = findModuleById(blocks.value, id)
        if (!module) return false

        if (!module.settings.design) module.settings.design = {}

        module.settings.design = {
            ...module.settings.design,
            ...clipboard.value.data
        }

        triggerRef(blocks)
        takeSnapshot()
        return true
    }

    function resetLayout() {
        blocks.value = []
        clearSelection()
        takeSnapshot()
    }

    // ============================================
    // METHODS - History
    // ============================================

    function takeSnapshot() {
        const snapshot = JSON.stringify(blocks.value)

        // Remove future history if we're not at the end
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1)
        }

        // Add new snapshot
        history.value.push(snapshot)
        historyIndex.value++

        // Limit history size
        if (history.value.length > maxHistory) {
            history.value.shift()
            historyIndex.value--
        }
    }

    function undo() {
        if (!canUndo.value) return false
        historyIndex.value--
        blocks.value = JSON.parse(history.value[historyIndex.value])
        return true
    }

    function redo() {
        if (!canRedo.value) return false
        historyIndex.value++
        blocks.value = JSON.parse(history.value[historyIndex.value])
        return true
    }

    async function loadTheme(slug = null) {
        const themeSlug = slug || activeTheme.value
        try {
            const response = await api.get(`/cms/themes/active?type=frontend`)
            const data = response.data?.data || response.data

            if (data) {
                themeData.value = data
                themeSettings.value = data.settings || {}
                activeTheme.value = data.slug

                globalActiveTheme.value = data
                globalThemeSettings.value = data.settings || {}
                if (data.assets) {
                    globalThemeAssets.value = data.assets
                }

                applyThemeStyles()
            }
        } catch (error) {
            console.error('Failed to load theme for builder:', error)
        }
    }

    function addCanvas(title = 'New Canvas') {
        const id = `canvas-${Date.now()}`
        canvases.value.push({
            id,
            title,
            blocks: [],
            isMain: false
        })
        activeCanvasId.value = id
        clearSelection()
        return id
    }

    function removeCanvas(id) {
        const index = canvases.value.findIndex(c => c.id === id)
        if (index === -1 || canvases.value[index].isMain) return false

        const isDeletingActive = activeCanvasId.value === id
        canvases.value.splice(index, 1)

        if (isDeletingActive) {
            activeCanvasId.value = canvases.value[0].id
            clearSelection()
        }
        return true
    }

    function switchCanvas(id) {
        const canvas = canvases.value.find(c => c.id === id)
        if (!canvas) return

        activeCanvasId.value = id
        gridViewMode.value = false
        clearSelection()
    }

    function renameCanvas(id, title) {
        const canvas = canvases.value.find(c => c.id === id)
        if (canvas) {
            canvas.title = title
        }
    }

    function duplicateCanvas(id) {
        const canvas = canvases.value.find(c => c.id === id)
        if (!canvas) return null

        const newId = `canvas-${Date.now()}`
        const newCanvas = {
            ...JSON.parse(JSON.stringify(canvas)),
            id: newId,
            title: `${canvas.title} (Copy)`,
            isMain: false
        }
        canvases.value.push(newCanvas)
        return newId
    }

    function setMainCanvas(id) {
        canvases.value.forEach(c => {
            c.isMain = c.id === id
        })
    }

    function exportCanvas(id) {
        const canvas = canvases.value.find(c => c.id === id)
        if (!canvas) return

        const data = JSON.stringify(canvas, null, 2)
        const blob = new Blob([data], { type: 'application/json' })
        const url = URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `${canvas.title.toLowerCase().replace(/\s+/g, '-')}.json`
        a.click()
        URL.revokeObjectURL(url)
    }

    function setDeviceMode(mode) {
        device.value = mode
        deviceModeType.value = 'manual'
        customViewportWidth.value = null
    }

    function setDeviceModeAuto() {
        deviceModeType.value = 'auto'
    }

    function openResponsiveModal(config) {
        responsiveModal.value = config
    }

    function closeResponsiveModal() {
        responsiveModal.value = null
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================

    function findModuleById(items, id) {
        for (const item of items) {
            if (item.id === id) return item
            if (item.children) {
                const found = findModuleById(item.children, id)
                if (found) return found
            }
        }
        return null
    }

    function findParentById(items, id, parent = null) {
        for (const item of items) {
            if (item.id === id) return parent
            if (item.children) {
                const found = findParentById(item.children, id, item)
                if (found !== null) return found
            }
        }
        return null
    }

    function removeModuleById(items, id) {
        const index = items.findIndex(item => item.id === id)
        if (index >= 0) {
            items.splice(index, 1)
            return true
        }
        for (const item of items) {
            if (item.children && removeModuleById(item.children, id)) {
                return true
            }
        }
        return false
    }

    function getModulePath(items, id, path = []) {
        for (const item of items) {
            if (item.id === id) {
                return [...path, item]
            }
            if (item.children) {
                const found = getModulePath(item.children, id, [...path, item])
                if (found.length > 0) return found
            }
        }
        return []
    }

    function regenerateIds(module) {
        module.id = ModuleRegistry.generateId()
        if (module.children) {
            module.children.forEach(child => regenerateIds(child))
        }
    }

    // ============================================
    // INITIALIZATION
    // ============================================

    // Take initial snapshot
    takeSnapshot()

    // ============================================
    // RETURN
    // ============================================

    return {
        // State
        blocks,
        selectedModuleId,
        hoveredModuleId,
        activeTab,
        device,
        customViewportWidth,
        zoom,
        wireframeMode,
        gridViewMode,
        isFullscreen,
        deviceModeType,
        setDeviceMode,
        setDeviceModeAuto,
        activeTheme,
        themeData,
        themeSettings,
        content,
        responsiveModal,
        history,
        historyIndex,
        pages,
        currentPageId,
        pagesLoading,
        categories,
        availableTags,
        menus,

        // Computed
        selectedModule,
        canUndo,
        canRedo,
        modulePath,
        currentPage,

        // Page Operations
        fetchPages,
        setCurrentPage,
        addPage,

        // Selection
        selectModule,
        hoverModule,
        clearSelection,

        // Module Operations
        insertModule,
        removeModule,
        duplicateModule,
        moveModule,
        updateModuleSettings,
        resetLayout,

        // Clipboard
        clipboard,
        copyModule,
        pasteModule,
        copyStyles,
        pasteStyles,

        takeSnapshot,
        undo,
        redo,

        // Helpers
        findModule: (id) => findModuleById(blocks.value, id),
        findParentById: (blocks, id) => findParentById(blocks, id),
        getModuleDefinition: (type) => ModuleRegistry.get(type),

        // Multi-Canvas Management
        canvases,
        activeCanvasId,
        addCanvas,
        removeCanvas,
        switchCanvas,
        renameCanvas,
        duplicateCanvas,
        setMainCanvas,
        loadTheme,
        loadContent,
        saveContent,
        exportCanvas,
        fetchMetadata,

        // Responsive Modal
        openResponsiveModal,
        closeResponsiveModal
    }
}
