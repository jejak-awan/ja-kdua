import { ref, computed, watch } from 'vue';
import { blockRegistry } from '../components/builder/BlockRegistry';

export function useBuilder() {
    // State
    const deviceMode = ref('desktop');
    const editingIndex = ref(null);
    const activeTab = ref('content');
    const isSidebarOpen = ref(true); // Default open (left sidebar)
    // History State
    const history = ref([]);
    const historyIndex = ref(-1);
    const isUndoing = ref(false);

    // History Methods
    const takeSnapshot = () => {
        if (isUndoing.value) return;

        // Remove any future history if we're in the middle of the stack
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }

        history.value.push(JSON.parse(JSON.stringify(blocks.value)));
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

    const cloneBlock = (block) => {
        const definition = blockRegistry.get(block.type);
        // Fallback to current settings if no definition found (shouldn't happen)
        const defaults = definition ? definition.defaultSettings : {};

        return {
            id: crypto.randomUUID(),
            type: block.type,
            settings: JSON.parse(JSON.stringify({ ...defaults, ...block.settings }))
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
        // takeSnapshot(); // Handled by watcher
    };

    const removeBlock = (index) => {
        blocks.value.splice(index, 1);
        if (editingIndex.value === index) editingIndex.value = null;
    };

    const duplicateBlock = (index) => {
        const original = blocks.value[index];
        const clone = {
            ...JSON.parse(JSON.stringify(original)),
            id: crypto.randomUUID()
        };
        blocks.value.splice(index + 1, 0, clone);
    };

    const updateBlock = (index, newBlock) => {
        blocks.value[index] = newBlock;
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
        activeRightSidebarTab,
        activeMediaField,
        activeBlockId,

        // History
        canUndo,
        canRedo,
        undo,
        redo,
        takeSnapshot, // Export for manual snapshots if needed (e.g. initial load)

        // Expose Registry Data
        availableBlocks: blockRegistry.getAll(), // For Sidebar list

        // Methods
        getBlockLabel,
        getBlockComponent,
        cloneBlock,
        addBlock,
        removeBlock,
        duplicateBlock,
        updateBlock
    };
}
