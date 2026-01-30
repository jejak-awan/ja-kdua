import { describe, it, expect, vi } from 'vitest'
import { shallowMount } from '@vue/test-utils'
import { ref, computed } from 'vue'
import Builder from '@/components/builder/Builder.vue'

// Mocking dependencies
vi.mock('@/stores/cms', () => ({
    useCmsStore: () => ({
        isDarkMode: false
    })
}))

// Mock vue-i18n
vi.mock('vue-i18n', async (importOriginal) => {
    const actual = await importOriginal() as any
    return {
        ...actual,
        useI18n: () => ({
            t: (key: string) => key
        }),
        createI18n: vi.fn(() => ({
            global: { t: (key: string) => key }
        }))
    }
})

// Mock useBuilder core with real refs
vi.mock('@/components/builder/core', () => {
    return {
        useBuilder: () => ({
            isFullscreen: ref(false),
            wireframeMode: ref(false),
            device: ref('desktop'),
            zoom: ref(100),
            customViewportWidth: ref(1200),
            blocks: ref([]),
            selectedModule: ref(null),
            canvases: ref([]),
            deviceModeType: ref('auto'),
            confirmModal: ref({ visible: false }),
            inputModal: ref({ visible: false }),
            savePresetModal: ref({ visible: false }),
            responsiveModal: ref({ visible: false }),
            markAsSaved: vi.fn(),
            loadTheme: vi.fn(),
            fetchMetadata: vi.fn(),
            closeResponsiveModal: vi.fn(),
            closeSavePresetModal: vi.fn(),
            closeConfirmModal: vi.fn(),
            closeInputModal: vi.fn(),
            autoSave: ref(false)
        }),
        registerBlockComponents: vi.fn()
    }
})

vi.mock('@/composables/useToast', () => ({
    useToast: () => ({
        success: { load: vi.fn(), action: vi.fn() },
        error: { load: vi.fn(), action: vi.fn() }
    })
}))

describe('Builder.vue', () => {
    it('renders correctly', () => {
        const wrapper = shallowMount(Builder, {
            global: {
                mocks: {
                    $t: (key: string) => key
                },
                stubs: {
                    Teleport: {
                        template: '<div><slot /></div>'
                    }
                }
            }
        })
        expect(wrapper.exists()).toBe(true)
        // Find the main div inside the teleport stub
        const mainDiv = wrapper.find('.ja-builder')
        expect(mainDiv.exists()).toBe(true)
        expect(mainDiv.classes()).toContain('ja-builder-main')
    })
})
