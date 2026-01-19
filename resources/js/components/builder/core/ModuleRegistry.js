/**
 * Module Registry
 * Manages module definitions and their Vue components
 */

class ModuleRegistry {
    constructor() {
        this.modules = new Map()
        this.components = new Map()
    }

    /**
     * Register a module definition
     * @param {Object} definition - Module definition object
     */
    register(definition) {
        if (!definition.name) {
            console.error('Module definition must have a name')
            return
        }
        this.modules.set(definition.name, definition)
    }

    /**
     * Register a Vue component for a module
     * @param {string} name - Module name
     * @param {Component} component - Vue component
     */
    registerComponent(name, component) {
        this.components.set(name, component)
    }

    /**
     * Get a module definition by name
     * @param {string} name - Module name
     * @returns {Object|undefined}
     */
    get(name) {
        return this.modules.get(name)
    }

    /**
     * Get a Vue component by module name
     * @param {string} name - Module name
     * @returns {Component|undefined}
     */
    getComponent(name) {
        return this.components.get(name)
    }

    /**
     * Get all registered modules
     * @returns {Array}
     */
    getAll() {
        return Array.from(this.modules.values())
    }

    /**
     * Get modules by category
     * @param {string} category - Category ID
     * @returns {Array}
     */
    getByCategory(category) {
        return this.getAll().filter(m => m.category === category)
    }

    /**
     * Get structure modules (Section, Row, Column)
     * @returns {Array}
     */
    getStructureModules() {
        return this.getByCategory('structure')
    }

    /**
     * Get content modules (non-structure)
     * @returns {Array}
     */
    getContentModules() {
        return this.getAll().filter(m => m.category !== 'structure')
    }

    /**
     * Check if a module exists
     * @param {string} name - Module name
     * @returns {boolean}
     */
    has(name) {
        return this.modules.has(name)
    }

    /**
     * Create a new module instance with default settings
     * @param {string} name - Module name
     * @param {Object} overrides - Settings overrides
     * @returns {Object|null}
     */
    createInstance(name, overrides = {}) {
        const definition = this.get(name)
        if (!definition) {
            console.error(`Module "${name}" not found in registry`)
            return null
        }

        const instance = {
            id: this.generateId(),
            type: name,
            settings: {
                ...JSON.parse(JSON.stringify(definition.defaults || {})),
                ...overrides
            }
        }

        if (definition.children) {
            instance.children = []
            if (definition.defaultChildren && Array.isArray(definition.defaultChildren)) {
                definition.defaultChildren.forEach(childType => {
                    const child = this.createInstance(childType)
                    if (child) instance.children.push(child)
                })
            }
        }

        return instance
    }

    /**
     * Generate unique ID
     * @returns {string}
     */
    generateId() {
        return `module-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
    }
}

// Singleton instance
export default new ModuleRegistry()
