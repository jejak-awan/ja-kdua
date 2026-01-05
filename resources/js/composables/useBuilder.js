import { ref, computed, watch } from 'vue';
import { blockRegistry } from '../components/builder/BlockRegistry';
import { useTheme } from './useTheme';

export function useBuilder() {
    const { activeTheme, themeSettings, loadActiveTheme } = useTheme();

    // State
    const deviceMode = ref('desktop');
    const editingIndex = ref(null);
    const activeTab = ref('content');
    const isSidebarOpen = ref(true); // Default open (left sidebar)
    // History State
    const history = ref([]);
    const historyIndex = ref(-1);
    const isUndoing = ref(false);

    // Global Layout Settings
    const globalSettings = ref({
        container_max_width: 'max-w-7xl mx-auto px-4',
        block_spacing: 'mb-4',
    });

    const getGlobalSetting = (key) => globalSettings.value[key];
    const setGlobalSetting = (key, value) => {
        globalSettings.value[key] = value;
        takeSnapshot();
    };

    // History Methods
    const takeSnapshot = () => {
        if (isUndoing.value) return;

        const currentState = JSON.stringify(blocks.value);
        const lastState = history.value.length > 0 ? JSON.stringify(history.value[historyIndex.value]) : null;

        if (currentState === lastState) return;

        // Remove any future history if we're in the middle of the stack
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }

        history.value.push(JSON.parse(currentState));
        historyIndex.value++;

        // Limit history size (optional, e.g. 50 steps)
        if (history.value.length > 50) {
            history.value.shift();
            historyIndex.value--;
        }
    };

    const undo = () => {
        if (historyIndex.value > 0) {
            isUndoing.value = true;
            historyIndex.value--;
            blocks.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]));
            // Use nextTick to reset flag if needed, but synchronous is fine for simple objects
            setTimeout(() => isUndoing.value = false, 0);
        }
    };

    const redo = () => {
        if (historyIndex.value < history.value.length - 1) {
            isUndoing.value = true;
            historyIndex.value++;
            blocks.value = JSON.parse(JSON.stringify(history.value[historyIndex.value]));
            setTimeout(() => isUndoing.value = false, 0);
        }
    };

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    const activeRightSidebarTab = ref('properties'); // 'properties' | 'layers'
    const isRightSidebarOpen = ref(true);
    const showLayersPanel = ref(false);
    const widgetSearch = ref('');
    const showMediaPicker = ref(false);
    const showTemplateLibrary = ref(false);
    const isPreview = ref(false);

    // Media Picker Context
    const activeMediaField = ref(null);
    const activeBlockId = ref(null);

    // Data
    const blocks = ref([]); // We will sync this with props in the root component

    // Watchers
    // Watch blocks for changes to push to history
    // We use a debounce or check for isUndoing to avoid loops
    // Ideally, we take a snapshot ONLY on user user action commit, but deep watch is easier for now.
    // However, deep watch triggers on every character type in inputs. 
    // Optimization: Debounce the snapshot.
    let snapshotTimeout;
    watch(blocks, () => {
        if (isUndoing.value) return;

        clearTimeout(snapshotTimeout);
        snapshotTimeout = setTimeout(() => {
            takeSnapshot();
        }, 500); // 500ms debounce
    }, { deep: true });

    // Initial snapshot
    // watchEffect or onMounted in component could trigger this, but let's do it on first meaningful change or manually?
    // Let's initialize history when blocks are set initially. 
    // Actually, creating a watcher on `blocks` ref assignment (not deep) might be needed if `blocks` is replaced.
    // But `blocks` is a ref, so we watch ref.value.

    // Helpers
    const getBlockLabel = (type) => {
        const block = blockRegistry.get(type);
        return block ? block.label : 'Unknown Block';
    };

    const getBlockComponent = (type) => {
        const block = blockRegistry.get(type);
        return block; // The registry returns the full definition which includes the icon/component info for UI
    };

    /**
     * Create a new block instance with default settings
     */
    const createBlockInstance = (type) => {
        const definition = blockRegistry.get(type);
        if (!definition) return null;

        return {
            id: crypto.randomUUID(),
            type: definition.name,
            settings: JSON.parse(JSON.stringify(definition.defaultSettings))
        };
    };

    const cloneBlock = (blockOrDef) => {
        const type = blockOrDef.type || blockOrDef.name;
        const definition = blockRegistry.get(type);

        // If no definition found, try minimal fallback if we have a type
        if (!definition && !type) return null;

        const defaults = definition ? definition.defaultSettings : {};
        const existingSettings = blockOrDef.settings || {};

        return {
            id: crypto.randomUUID(),
            type: type,
            // Merge defaults with existing settings (if any)
            // If it's a fresh definition from sidebar, existingSettings will be empty/undefined, 
            // so we get fresh defaults.
            settings: JSON.parse(JSON.stringify({ ...defaults, ...existingSettings }))
        };
    };

    // Actions
    const addBlock = (blockOrType, index = null) => {
        let block;
        if (typeof blockOrType === 'string') {
            block = createBlockInstance(blockOrType);
        } else {
            // It's a definition object (from sidebar drag)
            block = createBlockInstance(blockOrType.name);
        }

        if (!block) return;

        if (index !== null) {
            blocks.value.splice(index, 0, block);
        } else {
            blocks.value.push(block);
        }
        takeSnapshot();
    };

    const removeBlock = (index) => {
        blocks.value.splice(index, 1);
        if (editingIndex.value === index) editingIndex.value = null;
        takeSnapshot();
    };

    const duplicateBlock = (index) => {
        const original = blocks.value[index];
        const clone = {
            ...JSON.parse(JSON.stringify(original)),
            id: crypto.randomUUID()
        };
        blocks.value.splice(index + 1, 0, clone);
        takeSnapshot();
    };

    const updateBlock = (index, newBlock) => {
        blocks.value[index] = newBlock;
    };

    // Clipboard for copy/paste
    const clipboard = ref(null);

    const copyBlock = (index) => {
        const block = blocks.value[index];
        if (block) {
            clipboard.value = JSON.parse(JSON.stringify(block));
        }
    };

    const cutBlock = (index) => {
        copyBlock(index);
        removeBlock(index);
    };

    const pasteBlock = (afterIndex = null) => {
        if (!clipboard.value) return;

        const newBlock = {
            ...JSON.parse(JSON.stringify(clipboard.value)),
            id: crypto.randomUUID()
        };

        if (afterIndex !== null) {
            blocks.value.splice(afterIndex + 1, 0, newBlock);
        } else {
            blocks.value.push(newBlock);
        }
        takeSnapshot();
    };

    const canPaste = computed(() => clipboard.value !== null);

    // Move block up/down
    const moveBlockUp = (index) => {
        if (index <= 0) return;
        const block = blocks.value.splice(index, 1)[0];
        blocks.value.splice(index - 1, 0, block);
        if (editingIndex.value === index) editingIndex.value = index - 1;
        takeSnapshot();
    };

    const moveBlockDown = (index) => {
        if (index >= blocks.value.length - 1) return;
        const block = blocks.value.splice(index, 1)[0];
        blocks.value.splice(index + 1, 0, block);
        if (editingIndex.value === index) editingIndex.value = index + 1;
        takeSnapshot();
    };

    const getBlockPath = (id) => {
        if (!id) return [];
        const path = [];
        const find = (items, targetId, currentPath = []) => {
            for (const block of items) {
                const newPath = [...currentPath, {
                    id: block.id,
                    type: block.type,
                    label: getBlockLabel(block.type)
                }];
                if (block.id === targetId) {
                    path.push(...newPath);
                    return true;
                }
                // Handle Columns
                if (block.settings && Array.isArray(block.settings.columns)) {
                    for (const column of block.settings.columns) {
                        if (column && Array.isArray(column.blocks) && find(column.blocks, targetId, newPath)) return true;
                    }
                }
            }
            return false;
        };
        find(blocks.value, id);
        return path;
    };

    return {
        // State
        blocks,
        deviceMode,
        editingIndex,
        activeTab,
        widgetSearch,
        showMediaPicker,
        showTemplateLibrary,
        isPreview,
        isSidebarOpen,
        isRightSidebarOpen,
        showLayersPanel,
        activeRightSidebarTab,
        activeMediaField,
        activeBlockId,
        clipboard,

        // Theme
        activeTheme,
        themeSettings,
        loadActiveTheme,

        // History
        canUndo,
        canRedo,
        undo,
        redo,
        takeSnapshot,

        // Clipboard
        canPaste,
        copyBlock,
        cutBlock,
        pasteBlock,

        // Expose Registry Data
        availableBlocks: blockRegistry.getAll(),

        // Methods
        getBlockLabel,
        getBlockComponent,
        getBlockPath,
        cloneBlock,
        addBlock,
        removeBlock,
        duplicateBlock,
        updateBlock,
        moveBlockUp,
        moveBlockDown,

        // Global Settings
        globalSettings,
        getGlobalSetting,
        setGlobalSetting
    };
}

