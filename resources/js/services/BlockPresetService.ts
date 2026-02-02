import { logger } from '@/utils/logger';
import { ref, type Ref } from 'vue';

export interface BlockPreset {
    id: number;
    name: string;
    settings: Record<string, any>;
    createdAt: string;
}

export type PresetMap = Record<string, BlockPreset[]>;

class BlockPresetService {
    presets: Ref<PresetMap>;

    constructor() {
        this.presets = ref(this.loadFromStorage());
    }

    loadFromStorage(): PresetMap {
        try {
            const stored = localStorage.getItem('ja_builder_presets');
            return stored ? JSON.parse(stored) : {};
        } catch (e) {
            logger.error('Failed to load presets', e);
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
    savePreset(type: string, name: string, settings: Record<string, any>) {
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
    getPresets(type: string): BlockPreset[] {
        return this.presets.value[type] || [];
    }

    /**
     * Delete a preset
     * @param {string} type 
     * @param {number} id 
     */
    deletePreset(type: string, id: number) {
        if (!this.presets.value[type]) return;
        this.presets.value[type] = this.presets.value[type].filter((p: BlockPreset) => p.id !== id);
        this.saveToStorage();
    }
}

export const blockPresets = new BlockPresetService();
