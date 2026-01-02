import { ref } from 'vue';

class BlockPresetService {
    constructor() {
        this.presets = ref(this.loadFromStorage());
    }

    loadFromStorage() {
        try {
            const stored = localStorage.getItem('ja_builder_presets');
            return stored ? JSON.parse(stored) : {};
        } catch (e) {
            console.error('Failed to load presets', e);
            return {};
        }
    }

    saveToStorage() {
        localStorage.setItem('ja_builder_presets', JSON.stringify(this.presets.value));
    }

    /**
     * Save current settings as a preset
     * @param {string} type Block name/type
     * @param {string} name Preset name
     * @param {object} settings Settings object
     */
    savePreset(type, name, settings) {
        if (!this.presets.value[type]) {
            this.presets.value[type] = [];
        }

        // Avoid saving content fields if possible (Optional optimization)
        // For now, save all.

        this.presets.value[type].push({
            id: Date.now(),
            name,
            settings: JSON.parse(JSON.stringify(settings)),
            createdAt: new Date().toISOString()
        });

        this.saveToStorage();
    }

    /**
     * Get presets for a specific block type
     * @param {string} type 
     */
    getPresets(type) {
        return this.presets.value[type] || [];
    }

    /**
     * Delete a preset
     * @param {string} type 
     * @param {number} id 
     */
    deletePreset(type, id) {
        if (!this.presets.value[type]) return;
        this.presets.value[type] = this.presets.value[type].filter(p => p.id !== id);
        this.saveToStorage();
    }
}

export const blockPresets = new BlockPresetService();
