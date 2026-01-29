/**
 * Module Definitions - Complete Registry
 * Total: 82 modules
 */

import ModuleRegistry from '../core/ModuleRegistry'

// Bulk import all modules using glob
const moduleFiles = import.meta.glob([
    './structure/*.ts',
    './basic/*.ts',
    './media/*.ts',
    './content/*.ts',
    './interactive/*.ts',
    './forms/*.ts',
    './dynamic/*.ts',
    './fullwidth/*.ts'
], { eager: true }) as Record<string, { default: any }>;

const modules: any[] = [];

// Process and register each module
Object.keys(moduleFiles).forEach((path) => {
    const mod = moduleFiles[path].default;
    if (mod && mod.name) {
        modules.push(mod);
        ModuleRegistry.register(mod);
    }
});

export default modules;
