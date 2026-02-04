import { ref, computed, watch, nextTick, type Ref } from 'vue';
import ModuleRegistry from '../components/builder/core/ModuleRegistry';
import { useTheme } from './useTheme';
import { generateUUID } from '../shared/utils/uuid';
import type { BlockInstance, BlockDefinition, BuilderHistoryEntry, GlobalSettings } from '../types/builder';

export function useBuilder() {
    const { activeTheme, themeSettings, loadActiveTheme } = useTheme();

    // State
    const deviceMode = ref('desktop');
    const editingIndex: Ref<number | null> = ref(null);
    const activeTab = ref('content');
    const isSidebarOpen = ref(true); // Default open (left sidebar)
    // History State
    const history: Ref<BuilderHistoryEntry[]> = ref([]);
    const historyIndex = ref(-1);
    const isUndoing = ref(false);

    // Global Layout Settings
    const globalSettings = ref<GlobalSettings>({
        container_max_width: 'max-w-7xl mx-auto px-4',
        block_spacing: 'mb-4',
    });

    const getGlobalSetting = (key: string) => globalSettings.value[key];
    const setGlobalSetting = (key: string, value: unknown) => {
        (globalSettings.value as Record<string, unknown>)[key] = value;
        takeSnapshot();
    };

    // Data
    const blocks: Ref<BlockInstance[]> = ref([]); // We will sync this with props in the root component

    // Context Menu State
    const contextMenu = ref({
        visible: false,
        x: 0,
        y: 0,
        type: null as string | null,
        index: null as number | null,
        blockId: null as string | null
    });

    // Media Picker Context
    const activeMediaField: Ref<string | null> = ref(null);
    const activeBlockId: Ref<string | null> = ref(null);
    const hoveredBlockId: Ref<string | null> = ref(null); // For tracking deepest hovered block (toolbar stacking fix)

    // History Methods
    const takeSnapshot = () => {
        if (isUndoing.value) return;

        const currentState = JSON.stringify({
            blocks: blocks.value,
            globalSettings: globalSettings.value,
            editingIndex: editingIndex.value,
            activeBlockId: activeBlockId.value
        });

        const lastState = history.value.length > 0 ? JSON.stringify(history.value[historyIndex.value]) : null;

        if (currentState === lastState) return;

        // Remove any future history if we're in the middle of the stack
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }

        history.value.push(JSON.parse(currentState));
        historyIndex.value++;

        // Limit history size
        if (history.value.length > 100) {
            history.value.shift();
            historyIndex.value--;
        }
    };

    const undo = () => {
        if (historyIndex.value > 0) {
            isUndoing.value = true;
            historyIndex.value--;
            const prevState = history.value[historyIndex.value];
            blocks.value = JSON.parse(JSON.stringify(prevState.blocks));
            globalSettings.value = JSON.parse(JSON.stringify(prevState.globalSettings));
            editingIndex.value = prevState.editingIndex;
            activeBlockId.value = prevState.activeBlockId;
            setTimeout(() => isUndoing.value = false, 0);
        }
    };

    const redo = () => {
        if (historyIndex.value < history.value.length - 1) {
            isUndoing.value = true;
            historyIndex.value++;
            const nextState = history.value[historyIndex.value];
            blocks.value = JSON.parse(JSON.stringify(nextState.blocks));
            globalSettings.value = JSON.parse(JSON.stringify(nextState.globalSettings));
            editingIndex.value = nextState.editingIndex;
            activeBlockId.value = nextState.activeBlockId;
            setTimeout(() => isUndoing.value = false, 0);
        }
    };

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    const activeRightSidebarTab = ref('properties'); // 'properties' | 'layers' | 'presets' | 'layout' | 'visibility'
    const isRightSidebarOpen = ref(true);
    const showLayersPanel = ref(false);
    const widgetSearch = ref('');
    const showMediaPicker = ref(false);
    const isPreview = ref(false);

    // Watchers
    // Watch blocks for changes to push to history
    // We use a debounce or check for isUndoing to avoid loops
    // Ideally, we take a snapshot ONLY on user user action commit, but deep watch is easier for now.
    // However, deep watch triggers on every character type in inputs. 
    // Optimization: Debounce the snapshot.
    let snapshotTimeout: ReturnType<typeof setTimeout>;
    watch(blocks, () => {
        if (isUndoing.value) return;

        clearTimeout(snapshotTimeout);
        snapshotTimeout = setTimeout(() => {
            takeSnapshot();
        }, 500); // 500ms debounce
    }, { deep: true });

    // Helpers
    const getBlockLabel = (type: string) => {
        const block = ModuleRegistry.get(type);
        return block ? block.label : 'Unknown Block';
    };

    const getBlockComponent = (type: string) => {
        const block = ModuleRegistry.get(type);
        return block; // The registry returns the full definition which includes the icon/component info for UI
    };

    /**
     * Create a new block instance with default settings
     */
    const createBlockInstance = (type: string): BlockInstance | null => {
        const definition = ModuleRegistry.get(type);
        if (!definition) return null;

        return {
            id: generateUUID(),
            type: definition.name || type,
            settings: JSON.parse(JSON.stringify(definition.defaultSettings))
        };
    };

    const cloneBlock = (blockOrDef: BlockInstance | BlockDefinition): BlockInstance | null => {
        const type = ('type' in blockOrDef ? blockOrDef.type : blockOrDef.name) || '';
        const definition = ModuleRegistry.get(type);

        // If no definition found, try minimal fallback if we have a type
        if (!definition && !type) return null;

        const defaults = definition ? definition.defaultSettings : {};
        const existingSettings = (blockOrDef as BlockInstance).settings || {};

        return {
            id: generateUUID(),
            type: type,
            // Merge defaults with existing settings (if any)
            // If it's a fresh definition from sidebar, existingSettings will be empty/undefined, 
            // so we get fresh defaults.
            settings: JSON.parse(JSON.stringify({ ...defaults, ...existingSettings }))
        };
    };

    // Actions
    const addBlock = (blockOrType: string | BlockDefinition, index: number | null = null) => {
        let block: BlockInstance | null;
        if (typeof blockOrType === 'string') {
            block = createBlockInstance(blockOrType);
        } else {
            // It's a definition object (from sidebar drag)
            block = createBlockInstance(blockOrType.name || blockOrType.type || '');
        }

        if (!block) return;

        if (index !== null) {
            blocks.value.splice(index, 0, block);
        } else {
            blocks.value.push(block);
        }
        takeSnapshot();
    };

    const removeBlock = (index: number) => {
        blocks.value.splice(index, 1);
        if (editingIndex.value === index) editingIndex.value = null;
        takeSnapshot();
    };

    const duplicateBlock = (index: number) => {
        const original = blocks.value[index];
        const clone = {
            ...JSON.parse(JSON.stringify(original)),
            id: generateUUID()
        };
        blocks.value.splice(index + 1, 0, clone);
        takeSnapshot();
    };

    const updateBlock = (index: number, newBlock: BlockInstance) => {
        blocks.value[index] = newBlock;
    };

    // Clipboard for copy/paste
    const clipboard: Ref<BlockInstance | null> = ref(null);

    const copyBlock = (index: number) => {
        const block = blocks.value[index];
        if (block) {
            clipboard.value = JSON.parse(JSON.stringify(block));
        }
    };

    const cutBlock = (index: number) => {
        copyBlock(index);
        removeBlock(index);
    };

    const pasteBlock = (afterIndex: number | null = null) => {
        if (!clipboard.value) return;

        const newBlock = {
            ...JSON.parse(JSON.stringify(clipboard.value)),
            id: generateUUID()
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
    const moveBlockUp = (index: number) => {
        if (index <= 0) return;
        const block = blocks.value.splice(index, 1)[0];
        blocks.value.splice(index - 1, 0, block);
        if (editingIndex.value === index) editingIndex.value = index - 1;
        takeSnapshot();
    };

    const moveBlockDown = (index: number) => {
        if (index >= blocks.value.length - 1) return;
        const block = blocks.value.splice(index, 1)[0];
        blocks.value.splice(index + 1, 0, block);
        if (editingIndex.value === index) editingIndex.value = index + 1;
        takeSnapshot();
    };

    const findBlockById = (id: string, items: BlockInstance[] = blocks.value): BlockInstance | null => {
        if (!id || !Array.isArray(items)) return null;
        for (const block of items) {
            if (block.id === id) return block;
            // Search in Columns (column.blocks)
            if (block.settings && Array.isArray(block.settings.columns)) {
                for (const column of (block.settings.columns as Array<Record<string, unknown>>)) {
                    const found = findBlockById(id, column.blocks as BlockInstance[] || []);
                    if (found) return found;
                }
            }
            // Search in Section nested blocks (settings.blocks)
            if (block.settings && Array.isArray(block.settings.blocks)) {
                const found = findBlockById(id, block.settings.blocks as BlockInstance[]);
                if (found) return found;
            }
        }
        return null;
    };

    /**
     * Get the currently selected block using ID-based selection
     * This is the single source of truth for block selection
     */
    const selectedBlock = computed(() => {
        if (!activeBlockId.value) return null;
        return findBlockById(activeBlockId.value);
    });

    const getBlockPath = (id: string) => {
        if (!id) return [];
        interface PathEntry { id: string; type: string; label?: string; }
        const path: PathEntry[] = [];
        const find = (items: BlockInstance[], targetId: string, currentPath: PathEntry[] = []): boolean => {
            if (!Array.isArray(items)) return false;
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
                    for (const column of (block.settings.columns as Array<Record<string, unknown>>)) {
                        if (column && Array.isArray(column.blocks) && find(column.blocks as BlockInstance[], targetId, newPath)) return true;
                    }
                }
                // Handle Section nested blocks
                if (block.settings && Array.isArray(block.settings.blocks)) {
                    if (find(block.settings.blocks as BlockInstance[], targetId, newPath)) return true;
                }
            }
            return false;
        };
        find(blocks.value, id);
        return path;
    };

    const handleMediaSelect = (media: { url?: string } | null) => {
        if (!activeMediaField.value || !activeBlockId.value || !media?.url) return;

        const block = findBlockById(activeBlockId.value);
        if (!block) return;

        const path = activeMediaField.value; // e.g., "bgImage" or "slides[0].image"

        // Helper to set nested value by path
        const setByPath = (obj: Record<string, unknown>, path: string, value: unknown) => {
            const parts = path.split(/[.\[\]]+/).filter(Boolean);
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            let current = obj as any;
            for (let i = 0; i < parts.length - 1; i++) {
                const part = parts[i];
                if (!(part in current)) {
                    // Try to guess if next part is an array index
                    const nextIsNum = !isNaN(Number(parts[i + 1]));
                    current[part] = nextIsNum ? [] : {};
                }
                current = current[part];
            }
            current[parts[parts.length - 1]] = value;
        };

        if (!block.settings) block.settings = {};
        setByPath(block.settings, path, media.url);

        // Re-assign blocks to trigger deep reactivity
        blocks.value = [...blocks.value];

        // Reset context and take snapshot after Vue update
        activeMediaField.value = null;
        showMediaPicker.value = false;

        nextTick(() => {
            takeSnapshot();
        });
    };

    return {
        // State
        blocks,
        deviceMode,
        editingIndex,
        activeTab,
        widgetSearch,
        showMediaPicker,
        isPreview,
        isSidebarOpen,
        isRightSidebarOpen,
        showLayersPanel,
        activeRightSidebarTab,
        activeMediaField,
        activeBlockId,
        hoveredBlockId,
        selectedBlock,
        clipboard,
        handleMediaSelect,

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
        availableBlocks: ModuleRegistry.getAll(),

        // Methods
        getBlockLabel,
        getBlockComponent,
        getBlockPath,
        findBlockById,
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
        setGlobalSetting,

        // UI Helpers
        contextMenu
    };
}
