import { shallowRef } from 'vue';

/**
 * MenuItemRegistry - Registry pattern for menu item type definitions
 * Similar to BlockRegistry.js in the visual builder
 */
class MenuItemRegistry {
    constructor() {
        this.items = shallowRef(new Map());
    }

    /**
     * Register a new item type definition
     * @param {Object} definition - Item type definition
     */
    register(definition) {
        if (!definition.name) {
            console.error('Menu item definition must have a name');
            return;
        }
        this.items.value.set(definition.name, definition);
    }

    /**
     * Register multiple definitions at once
     * @param {Array} definitions 
     */
    registerAll(definitions) {
        definitions.forEach(def => this.register(def));
    }

    /**
     * Get an item type definition by name
     * @param {String} name 
     * @returns {Object|undefined}
     */
    get(name) {
        return this.items.value.get(name);
    }

    /**
     * Get all registered item types
     * @returns {Array}
     */
    getAll() {
        return Array.from(this.items.value.values());
    }

    /**
     * Get item types grouped by category
     * @returns {Object}
     */
    getGrouped() {
        const grouped = {};
        this.getAll().forEach(item => {
            const category = item.category || 'other';
            if (!grouped[category]) {
                grouped[category] = [];
            }
            grouped[category].push(item);
        });
        return grouped;
    }

    /**
     * Create a new item instance from a type
     * @param {String} type 
     * @param {Object} overrides - Optional property overrides
     * @returns {Object}
     */
    createInstance(type, overrides = {}) {
        const definition = this.get(type);
        if (!definition) {
            console.warn(`Unknown menu item type: ${type}`);
            return null;
        }

        const defaultValues = {};
        if (definition.settings) {
            definition.settings.forEach(setting => {
                if (setting.default !== undefined) {
                    defaultValues[setting.key] = setting.default;
                }
            });
        }

        return {
            id: null,
            _temp_id: 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
            type: definition.name,
            title: definition.defaultTitle || 'New Item',
            children: [],
            is_active: 1,
            ...defaultValues,
            ...overrides
        };
    }

    /**
     * Get the settings schema for an item type
     * @param {String} type 
     * @returns {Array}
     */
    getSettingsSchema(type) {
        const definition = this.get(type);
        return definition?.settings || [];
    }

    /**
     * Get icon component for a type
     * @param {String} type 
     * @returns {Component}
     */
    getIcon(type) {
        const definition = this.get(type);
        return definition?.icon;
    }

    /**
     * Get color for a type
     * @param {String} type 
     * @returns {String}
     */
    getColor(type) {
        const definition = this.get(type);
        return definition?.color || 'gray';
    }
}

// Export singleton instance
export const menuItemRegistry = new MenuItemRegistry();
