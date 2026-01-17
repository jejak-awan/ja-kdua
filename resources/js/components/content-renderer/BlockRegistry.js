import { shallowRef } from 'vue';
import blockDefinitions from './definitions';

class BlockRegistry {
    constructor() {
        this.blocks = shallowRef(new Map());
        this.registerAll();
    }

    registerAll() {
        blockDefinitions.forEach(def => this.register(def));
    }

    /**
     * Register a new block definition
     * @param {Object} definition 
     */
    register(definition) {
        if (!definition.name) {
            console.error('Block definition must have a name');
            return;
        }
        this.blocks.value.set(definition.name, definition);
    }

    /**
     * Get a block definition by name
     * @param {String} name 
     * @returns {Object|undefined}
     */
    get(name) {
        return this.blocks.value.get(name);
    }

    /**
     * Get all registered blocks as an array
     * @returns {Array}
     */
    getAll() {
        return Array.from(this.blocks.value.values());
    }

    /**
     * Get block component for rendering
     * @param {String} name 
     */
    getComponent(name) {
        const block = this.get(name);
        return block?.component;
    }
}

// Export a singleton instance
export const blockRegistry = new BlockRegistry();
