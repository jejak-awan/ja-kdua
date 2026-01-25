import api from '@/services/api'
import { triggerRef } from 'vue'
import ModuleRegistry from '../ModuleRegistry'
import type { BuilderState, PageMetadata, BlockInstance } from '../../../../types/builder'
import type { HistoryManager } from './useBuilderModules'

export function useBuilderSync(state: BuilderState, historyManager: HistoryManager, globalVariables: any) {
    const {
        blocks,
        content,
        pages,
        currentPageId,
        pagesLoading,
        categories,
        availableTags,
        menus,
        availableThemes,
        loadingThemes,
        lastSavedBlocks,
        autoSave,
        activeTheme,
        themeSettings
    } = state

    const { takeSnapshot } = historyManager

    function markAsSaved(): void {
        lastSavedBlocks.value = JSON.stringify(blocks.value)
    }

    async function fetchPages(): Promise<void> {
        pagesLoading.value = true
        try {
            const response = await api.get('/admin/ja/contents', {
                params: { type: 'page', per_page: 100 }
            })
            const data = response.data?.data || response.data
            pages.value = (data.data || data || []).map((p: any) => ({
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

    async function loadContent(id: number | string): Promise<any> {
        try {
            const response = await api.get(`/admin/ja/contents/${id}`)
            const data = response.data?.data || response.data

            if (data) {
                content.value = {
                    id: data.id,
                    title: data.title || '',
                    slug: data.slug || '',
                    excerpt: data.excerpt || '',
                    body: data.body || '',
                    status: data.status || 'draft',
                    type: data.type || 'post',
                    editor_type: data.editor_type || 'builder',
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

                if (data.blocks && data.blocks.length > 0) {
                    blocks.value = data.blocks
                } else if (data.body) {
                    const richTextBlock = ModuleRegistry.createInstance('richtext')
                    if (richTextBlock) {
                        richTextBlock.settings.content = data.body

                        const column = ModuleRegistry.createInstance('column')
                        if (column) {
                            column.children!.push(richTextBlock)

                            const row = ModuleRegistry.createInstance('row')
                            if (row) {
                                row.children!.push(column)

                                const section = ModuleRegistry.createInstance('section')
                                if (section) {
                                    section.children!.push(row)
                                    blocks.value = [section]
                                }
                            }
                        }
                    }
                }

                if (blocks.value.length > 0) {
                    triggerRef(blocks)
                    takeSnapshot({ immediate: true })
                    markAsSaved()
                }

                if (data.global_variables) {
                    globalVariables.loadVariables(data.global_variables)
                }
            }
            return data
        } catch (error: any) {
            console.error('Failed to load content for builder:', error)
            throw error
        }
    }

    async function setCurrentPage(id: number | string): Promise<void> {
        if (currentPageId.value === id) return
        try {
            await loadContent(id)
            currentPageId.value = typeof id === 'string' ? parseInt(id) : id
        } catch (error) {
            console.error('Failed to switch page:', error)
        }
    }

    async function addPage(title: string): Promise<any> {
        try {
            const payload = {
                title,
                type: 'page',
                status: 'draft',
                editor_type: 'builder',
                body: '',
                excerpt: '',
                category_id: null,
                blocks: []
            }
            const response = await api.post('/admin/ja/contents', payload)
            const newPage = response.data?.data || response.data

            if (newPage) {
                await fetchPages()
                await setCurrentPage(newPage.id)
            }
            return newPage
        } catch (error: any) {
            const message = error.response?.data?.message || error.message || 'Failed to create page'
            console.error('Failed to create page:', error)
            throw new Error(message)
        }
    }

    async function deletePage(id: number | string): Promise<boolean> {
        try {
            await api.delete(`/admin/ja/contents/${id}`)
            await fetchPages()

            const numericId = typeof id === 'string' ? parseInt(id) : id
            if (currentPageId.value === numericId) {
                if (pages.value.length > 0) {
                    await setCurrentPage(pages.value[0].id)
                } else {
                    blocks.value = []
                    currentPageId.value = null
                    content.value = {
                        id: null, title: '', slug: '', excerpt: '', body: '', status: 'draft',
                        type: 'post', editor_type: 'builder', category_id: null, featured_image: null,
                        published_at: null, tags: [], menu_item: { add_to_menu: false, menu_id: '', parent_id: null, title: '' }
                    }
                }
            }
            return true
        } catch (error) {
            console.error('Failed to delete page:', error)
            throw error
        }
    }

    async function saveContent(): Promise<any> {
        if (!content.value.id) return false
        try {
            const payload: any = {
                ...content.value,
                blocks: blocks.value,
                global_variables: globalVariables.getVariables()
            }
            if (content.value.tags) {
                payload.tags = content.value.tags.filter((t: any) => t.id).map((t: any) => t.id)
                payload.new_tags = content.value.tags.filter((t: any) => !t.id).map((t: any) => t.name)
            }
            const response = await api.put(`/admin/ja/contents/${content.value.id}`, payload)
            markAsSaved()
            return response.data
        } catch (error: any) {
            console.error('Failed to save content from builder:', error)
            throw error
        }
    }

    async function saveGlobalVariables(): Promise<any> {
        const vars = globalVariables.getVariables()
        if (content.value.id) {
            try {
                const response = await api.put(`/admin/ja/contents/${content.value.id}`, { global_variables: vars })
                return response.data
            } catch (error: any) {
                console.error('Failed to save global variables to content:', error)
                throw error
            }
        }
        if (activeTheme.value) {
            try {
                const currentSettings = themeSettings.value || {}
                const newSettings = { ...currentSettings, global_variables: vars }
                const response = await api.put(`/admin/ja/themes/${activeTheme.value}/settings`, { settings: newSettings })
                themeSettings.value = newSettings
                return response.data
            } catch (error: any) {
                console.error('Failed to save global variables to theme:', error)
                throw error
            }
        }
        throw new Error('No content or theme available to save global variables')
    }

    async function fetchMetadata(): Promise<void> {
        try {
            const [catsRes, tagsRes, menusRes] = await Promise.all([
                api.get('/admin/ja/categories'),
                api.get('/admin/ja/tags'),
                api.get('/admin/ja/menus')
            ])
            const ensureArray = (res: any) => {
                const data = res?.data?.data || res?.data || []
                return Array.isArray(data) ? data : []
            }
            categories.value = ensureArray(catsRes)
            availableTags.value = ensureArray(tagsRes)
            menus.value = ensureArray(menusRes)
        } catch (error) {
            console.error('Failed to fetch builder metadata:', error)
        }
    }

    async function fetchThemes(): Promise<void> {
        loadingThemes.value = true
        try {
            const response = await api.get('/admin/ja/themes')
            const data = response.data?.data || response.data
            availableThemes.value = Array.isArray(data) ? data : (data.data || [])
        } catch (error) {
            console.error('Failed to fetch themes:', error)
        } finally {
            loadingThemes.value = false
        }
    }

    let autoSaveInterval: ReturnType<typeof setInterval> | null = null
    function startAutoSave(): void {
        if (autoSaveInterval) clearInterval(autoSaveInterval)
        autoSaveInterval = setInterval(async () => {
            if (autoSave.value && content.value.id) {
                try {
                    await saveContent()
                    console.log('[Auto-save] Content saved')
                } catch (e) {
                    console.error('[Auto-save] Failed:', e)
                }
            }
        }, 60000)
    }

    function stopAutoSave(): void {
        if (autoSaveInterval) {
            clearInterval(autoSaveInterval)
            autoSaveInterval = null
        }
    }

    async function updateThemeSettings(themeSlug: string, settings: any): Promise<void> {
        try {
            await api.put(`/admin/ja/themes/${themeSlug}/settings`, { settings })
            themeSettings.value = { ...settings }
        } catch (error) {
            console.error('Failed to update theme settings:', error)
            throw error
        }
    }

    async function fetchTemplates(): Promise<any[]> {
        try {
            const response = await api.get('/admin/ja/contents', {
                params: { type: 'layout', per_page: 100 }
            })
            const data = response.data?.data || response.data
            return data.data || data || []
        } catch (error) {
            console.error('Failed to fetch templates:', error)
            return []
        }
    }

    async function createTemplate(data: { name: string, type: string }): Promise<any> {
        try {
            const payload = {
                title: data.name,
                type: 'layout',
                status: 'published',
                editor_type: 'builder',
                blocks: [],
                meta: { template_type: data.type }
            }
            const response = await api.post('/admin/ja/contents', payload)
            return response.data?.data || response.data
        } catch (error) {
            console.error('Failed to create template:', error)
            throw error
        }
    }

    async function deleteTemplate(id: number | string): Promise<boolean> {
        try {
            await api.delete(`/admin/ja/contents/${id}`)
            return true
        } catch (error) {
            console.error('Failed to delete template:', error)
            throw error
        }
    }

    async function updateContentMeta(id: number | string, meta: any): Promise<any> {
        try {
            const response = await api.put(`/admin/ja/contents/${id}`, { meta })
            return response.data?.data || response.data
        } catch (error) {
            console.error('Failed to update content meta:', error)
            throw error
        }
    }

    return {
        fetchPages,
        setCurrentPage,
        addPage,
        deletePage,
        loadContent,
        saveContent,
        saveGlobalVariables,
        fetchMetadata,
        fetchThemes,
        markAsSaved,
        startAutoSave,
        stopAutoSave,
        updateThemeSettings,
        fetchTemplates,
        createTemplate,
        deleteTemplate,
        updateContentMeta
    }
}
