import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import MediaPicker from '@/components/media/MediaPicker.vue'

// Mocking dependencies
vi.mock('@/services/api', () => ({
    default: {
        get: vi.fn(() => Promise.resolve({ data: { data: [] } }))
    }
}))

vi.mock('@/composables/useToast', () => ({
    useToast: () => ({
        success: { action: vi.fn() },
        error: { validation: vi.fn(), action: vi.fn() }
    })
}))

// Mock t for i18n
const mockT = (key: string) => key

const globalMocks = {
    global: {
        mocks: {
            $t: mockT
        },
        stubs: {
            Teleport: true,
            MediaUpload: true,
            // Use a custom object for Button to render slot content
            Button: {
                template: '<button><slot /></button>'
            },
            // Stub every icon that appears in the template
            ImageIcon: true,
            X: true,
            Home: true,
            ChevronRight: true,
            Grid: true,
            ListIcon: true,
            File: true,
            Folder: true,
            Search: true,
            ArrowUp: true,
            AlertCircle: true
        }
    }
}

describe('MediaPicker.vue', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('renders correctly with default trigger', () => {
        const wrapper = mount(MediaPicker, globalMocks)
        // Now it should contain the i18n key since Button renders slot
        expect(wrapper.text()).toContain('features.media.modals.picker.select')
    })

    it('opens modal when clicked', async () => {
        const wrapper = mount(MediaPicker, globalMocks)
        // Find the button (which is now rendered by our stub)
        const btn = wrapper.find('button')
        expect(btn.exists()).toBe(true)
        await btn.trigger('click')
        expect((wrapper.vm as any).showModal).toBe(true)
    })
})
