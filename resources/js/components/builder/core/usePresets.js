import { ref } from 'vue'
import api from '@/services/api'
import { useToast } from '@/composables/useToast'

export function usePresets() {
    const presets = ref([])
    const loading = ref(false)
    const toast = useToast()

    /**
     * Fetch presets for a specific module type or all if type is null
     * @param {string|null} type 
     */
    const fetchPresets = async (type = null) => {
        loading.value = true
        try {
            const params = type ? { type } : {}
            const response = await api.get('/admin/ja/builder-presets', { params })
            const data = response.data?.data || response.data
            presets.value = Array.isArray(data) ? data : (data.data || [])
        } catch (err) {
            console.error('Failed to fetch presets:', err)
        } finally {
            loading.value = false
        }
    }

    /**
     * Save a module as a new preset
     * @param {Object} module - The module object (including settings and type)
     * @param {string} name - Name for the preset
     */
    const savePreset = async (module, name) => {
        try {
            const response = await api.post('/admin/ja/builder-presets', {
                type: module.type,
                name: name,
                settings: JSON.parse(JSON.stringify(module.settings))
            })

            if (response.data?.success) {
                toast.success.action('Preset saved successfully')
                const newPreset = response.data.data
                presets.value.push(newPreset)
                return newPreset
            }
        } catch (err) {
            toast.error.action(err)
            throw err
        }
    }

    /**
     * Delete a preset
     * @param {number|string} presetId 
     */
    const deletePreset = async (presetId) => {
        try {
            const response = await api.delete(`/admin/ja/builder-presets/${presetId}`)
            if (response.data?.success) {
                toast.success.action('Preset deleted')
                presets.value = presets.value.filter(p => p.id !== presetId)
                return true
            }
        } catch (err) {
            toast.error.action(err)
            return false
        }
    }

    return {
        presets,
        loading,
        fetchPresets,
        savePreset,
        deletePreset
    }
}
