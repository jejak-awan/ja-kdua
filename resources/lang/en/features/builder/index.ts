import messages from './messages.json';
import fields from './fields.json';
import modals from './modals.json';
import panels from './panels.json';
import settings from './settings.json';
import toolbar from './toolbar.json';
import inserts from './inserts.json';
import help from './help.json';
import modules from './modules.json';

/**
 * Builder translations (English)
 * 
 * This aggregates all builder-related translations and exports them
 * with a structure that maintains backward compatibility with the
 * existing `builder.*` namespace used throughout the codebase.
 * 
 * File structure:
 * - messages.json: Loading, error, confirm messages
 * - fields.json: Field controls (background, border, shadow, etc.)
 * - modals.json: Modal dialogs (color picker, confirm, etc.)
 * - panels.json: Sidebar panels (layers, pages, history, etc.)
 * - settings.json: Settings groups and field options
 * - toolbar.json: Toolbar, sidebars, tabs, breakpoints
 * - inserts.json: Insert modals, categories, canvas actions
 * - help.json: Contextual help texts (tooltips)
 * - modules.json: Module type names
 */
export default {
    // Spread all parts at root level to maintain builder.* namespace
    ...messages,
    ...fields,
    ...modals,
    ...panels,
    ...settings,
    ...toolbar,
    ...inserts,
    // info is nested under 'info' key for builder.info.* usage
    info: help,
    // modules for builder.modules.* usage
    modules
};
