import { describe, it, expect, vi, beforeEach } from 'vitest'
import { ref, reactive, computed } from 'vue'
import { useBuilderModules, type HistoryManager } from '@/components/builder/core/composables/useBuilderModules'
import ModuleRegistry from '@/components/builder/core/ModuleRegistry'
import type { BuilderState } from '@/types/builder'

// Mock dependencies
vi.mock('@/components/builder/core/ModuleRegistry', () => ({
    default: {
        getAll: vi.fn(() => []),
        get: vi.fn(),
        register: vi.fn(),
        createInstance: vi.fn((type) => ({
            id: 'mock-id-' + Math.random(),
            type,
            settings: {},
            children: []
        })),
        generateId: vi.fn(() => 'generated-id-' + Math.random())
    }
}))

vi.mock('@/utils/logger', () => ({
    logger: {
        debug: vi.fn(),
        error: vi.fn()
    }
}))

describe('useBuilderModules', () => {
    let state: BuilderState
    let historyManager: HistoryManager
    let composable: ReturnType<typeof useBuilderModules>

    beforeEach(() => {
        // Mock State
        state = {
            blocks: ref([]),
            selectedModuleId: ref(null),
            hoveredModuleId: ref(null),
            device: ref('desktop'),
            clipboard: ref(null),
            markAsDirty: vi.fn(),
            // ... other properties not strictly needed for this test
        } as unknown as BuilderState

        // Mock History Manager
        historyManager = {
            takeSnapshot: vi.fn(),
            flushSnapshot: vi.fn(),
            undo: vi.fn(),
            redo: vi.fn(),
            canUndo: computed(() => false),
            canRedo: computed(() => false)
        }

        composable = useBuilderModules(state, historyManager)
    })

    it('should insert a module into the root', () => {
        const module = composable.insertModule('section')

        expect(module).toBeDefined()
        expect(state.blocks.value.length).toBe(1)
        expect(state.blocks.value[0].type).toBe('section')
        expect(historyManager.takeSnapshot).toHaveBeenCalled()
        expect(state.selectedModuleId.value).toBe(module?.id)
    })

    it('should remove a module', () => {
        const module = composable.insertModule('section')!
        expect(state.blocks.value.length).toBe(1)

        composable.removeModule(module.id)

        expect(state.blocks.value.length).toBe(0)
        expect(state.markAsDirty).toHaveBeenCalled()
    })

    it('should select a module', () => {
        const module = composable.insertModule('section')!
        composable.selectModule(module.id)
        expect(state.selectedModuleId.value).toBe(module.id)
        expect(composable.selectedModule.value?.id).toBe(module.id)
    })

    it('should clear selection', () => {
        const module = composable.insertModule('section')!
        composable.selectModule(module.id)
        composable.clearSelection()
        expect(state.selectedModuleId.value).toBeNull()
    })

    it('should copy and paste styles', () => {
        const module = composable.insertModule('section')!
        module.settings.design = { color: 'red' }

        // Copy
        const copyResult = composable.copyStyles(module.id)
        expect(copyResult).toBe(true)
        expect(state.clipboard.value).toEqual({
            type: 'styles',
            sourceType: 'section',
            data: { color: 'red' }
        })

        // Paste
        const target = composable.insertModule('column')!
        const pasteResult = composable.pasteStyles(target.id)

        expect(pasteResult).toBe(true)
        expect((target.settings.design as any).color).toBe('red')
    })
})
