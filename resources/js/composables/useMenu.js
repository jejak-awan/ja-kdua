import { ref, computed, watch, provide, inject } from 'vue';
import api from '../services/api';
import { parseResponse, ensureArray, parseSingleResponse } from '../utils/responseParser';

const MENU_CONTEXT_KEY = Symbol('menuBuilder');

/**
 * Generate a unique ID for new menu items
 */
const generateId = () => 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

/**
 * Deep clone an object
 */
const deepClone = (obj) => JSON.parse(JSON.stringify(obj));

/**
 * useMenu composable - Centralized state management for Menu Builder
 * Inspired by useBuilder.js pattern
 */
// Global state for frontend/shared usage
const menus = ref({});

const fetchMenuByLocation = async (location) => {
    try {
        const response = await api.get(`/cms/menus/location/${location}`);
        menus.value[location] = parseSingleResponse(response);
    } catch (err) {
        // Silent fail for frontend menus to avoid crashing app
        console.warn(`Failed to fetch menu for location: ${location}`, err);
    }
};

export function useMenu(menuId) {
    // ==================== STATE ====================
    const menu = ref(null);
    const items = ref([]);
    const selectedItemId = ref(null);
    const isLoading = ref(false);
    const isSaving = ref(false);
    const error = ref(null);

    // Initial state for dirty tracking
    const initialState = ref(null);

    // ==================== HISTORY ====================
    const history = ref([]);
    const historyIndex = ref(-1);
    const isUndoing = ref(false);
    const MAX_HISTORY = 50;

    /**
     * Take a snapshot of current state for history
     */
    const takeSnapshot = () => {
        if (isUndoing.value) return;

        const currentState = deepClone({
            items: items.value,
            selectedItemId: selectedItemId.value
        });

        // Don't add duplicate states
        const lastState = history.value[historyIndex.value];
        if (lastState && JSON.stringify(currentState) === JSON.stringify(lastState)) {
            return;
        }

        // Remove future history if we're in the middle
        if (historyIndex.value < history.value.length - 1) {
            history.value = history.value.slice(0, historyIndex.value + 1);
        }

        history.value.push(currentState);
        historyIndex.value++;

        // Limit history size
        if (history.value.length > MAX_HISTORY) {
            history.value.shift();
            historyIndex.value--;
        }
    };

    const canUndo = computed(() => historyIndex.value > 0);
    const canRedo = computed(() => historyIndex.value < history.value.length - 1);

    const undo = () => {
        if (!canUndo.value) return;

        isUndoing.value = true;
        historyIndex.value--;
        const prevState = history.value[historyIndex.value];
        items.value = deepClone(prevState.items);
        selectedItemId.value = prevState.selectedItemId;
        setTimeout(() => isUndoing.value = false, 0);
    };

    const redo = () => {
        if (!canRedo.value) return;

        isUndoing.value = true;
        historyIndex.value++;
        const nextState = history.value[historyIndex.value];
        items.value = deepClone(nextState.items);
        selectedItemId.value = nextState.selectedItemId;
        setTimeout(() => isUndoing.value = false, 0);
    };

    // ==================== CLIPBOARD ====================
    const clipboard = ref(null);
    const clipboardAction = ref(null); // 'copy' | 'cut'

    const canPaste = computed(() => clipboard.value !== null);

    const copyItem = (item) => {
        clipboard.value = deepClone(item);
        clipboardAction.value = 'copy';
    };

    const cutItem = (item) => {
        clipboard.value = deepClone(item);
        clipboardAction.value = 'cut';
        removeItem(item.id || item._temp_id);
    };

    const pasteItem = (parentId = null) => {
        if (!clipboard.value) return;

        const newItem = deepClone(clipboard.value);
        // Reset IDs for the pasted item and all children
        const resetIds = (item) => {
            item.id = null;
            item._temp_id = generateId();
            if (item.children) {
                item.children.forEach(resetIds);
            }
        };
        resetIds(newItem);

        if (parentId) {
            // Add as child of specified parent
            const parent = findItemById(parentId);
            if (parent) {
                if (!parent.children) parent.children = [];
                parent.children.push(newItem);
            }
        } else {
            // Add to root
            items.value.push(newItem);
        }

        if (clipboardAction.value === 'cut') {
            clipboard.value = null;
            clipboardAction.value = null;
        }

        takeSnapshot();
    };

    // ==================== DIRTY STATE ====================
    const isDirty = computed(() => {
        if (!initialState.value) return false;
        return JSON.stringify(items.value) !== JSON.stringify(initialState.value);
    });

    const markClean = () => {
        initialState.value = deepClone(items.value);
    };

    // ==================== ITEM HELPERS ====================

    /**
     * Build nested tree from flat items array
     */
    const buildTree = (flatItems, parentId = null) => {
        return flatItems
            .filter(item => item.parent_id === parentId)
            .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
            .map(item => ({
                ...item,
                children: buildTree(flatItems, item.id)
            }));
    };

    /**
     * Flatten tree to array for API
     */
    const flattenTree = (treeItems, parentId = null) => {
        let result = [];
        treeItems.forEach((item, index) => {
            result.push({
                id: item.id,
                parent_id: parentId,
                sort_order: index,
                title: item.title,
                type: item.type,
                target_id: item.target_id,
                url: item.url,
                icon: item.icon || null,
                css_class: item.css_class || null,
                description: item.description || null,
                badge: item.badge || null,
                badge_color: item.badge_color || 'primary',
                mega_menu_layout: item.mega_menu_layout || 'default',
                mega_menu_column: item.mega_menu_column || 0,
                open_in_new_tab: item.open_in_new_tab || false,
                image: item.image || null,
                image_size: item.image_size || 'auto',
                mega_menu_show_dividers: item.mega_menu_show_dividers || false,
                hide_label: item.hide_label || false,
                heading: item.heading || null,
                show_heading_line: item.show_heading_line || false,
            });
            if (item.children && item.children.length > 0) {
                result = result.concat(flattenTree(item.children, item.id));
            }
        });
        return result;
    };

    /**
     * Find item by ID recursively
     */
    const findItemById = (id, searchItems = items.value) => {
        for (const item of searchItems) {
            const itemId = item.id || item._temp_id;
            if (itemId === id) return item;
            if (item.children) {
                const found = findItemById(id, item.children);
                if (found) return found;
            }
        }
        return null;
    };

    /**
     * Find parent of an item
     */
    const findParent = (id, searchItems = items.value, parent = null) => {
        for (const item of searchItems) {
            const itemId = item.id || item._temp_id;
            if (itemId === id) return parent;
            if (item.children) {
                const found = findParent(id, item.children, item);
                if (found !== undefined) return found;
            }
        }
        return undefined;
    };

    /**
     * Get selected item
     */
    const selectedItem = computed(() => {
        if (!selectedItemId.value) return null;
        return findItemById(selectedItemId.value);
    });

    // ==================== ITEM ACTIONS ====================

    const addItem = (itemData, parentId = null) => {
        const newItem = {
            id: null,
            _temp_id: generateId(),
            title: itemData.title || 'New Item',
            type: itemData.type || 'custom',
            target_id: itemData.target_id || null,
            url: itemData.url || '#',
            children: [],
            is_active: 1,
            ...itemData
        };

        if (parentId) {
            const parent = findItemById(parentId);
            if (parent) {
                if (!parent.children) parent.children = [];
                parent.children.push(newItem);
            }
        } else {
            items.value.push(newItem);
        }

        takeSnapshot();
        return newItem;
    };

    const removeItem = (id) => {
        const removeFromList = (list) => {
            const index = list.findIndex(i => (i.id || i._temp_id) === id);
            if (index > -1) {
                list.splice(index, 1);
                return true;
            }
            for (const item of list) {
                if (item.children && removeFromList(item.children)) {
                    return true;
                }
            }
            return false;
        };

        removeFromList(items.value);

        if (selectedItemId.value === id) {
            selectedItemId.value = null;
        }

        takeSnapshot();
    };

    const updateItem = (id, updates) => {
        const item = findItemById(id);
        if (item) {
            Object.assign(item, updates);
            takeSnapshot();
        }
    };

    const duplicateItem = (id) => {
        const item = findItemById(id);
        if (!item) return;

        const parent = findParent(id);
        const targetList = parent ? parent.children : items.value;
        const index = targetList.findIndex(i => (i.id || i._temp_id) === id);

        const clone = deepClone(item);
        const resetIds = (i) => {
            i.id = null;
            i._temp_id = generateId();
            if (i.children) i.children.forEach(resetIds);
        };
        resetIds(clone);
        clone.title = `${clone.title} (Copy)`;

        targetList.splice(index + 1, 0, clone);
        takeSnapshot();
    };

    const moveItem = (id, newParentId, newIndex = null) => {
        const item = findItemById(id);
        if (!item) return;

        // Remove from current location
        const currentParent = findParent(id);
        const currentList = currentParent ? currentParent.children : items.value;
        const currentIndex = currentList.findIndex(i => (i.id || i._temp_id) === id);
        if (currentIndex > -1) {
            currentList.splice(currentIndex, 1);
        }

        // Add to new location
        const targetList = newParentId
            ? (findItemById(newParentId)?.children || [])
            : items.value;

        if (newParentId && !findItemById(newParentId).children) {
            findItemById(newParentId).children = [];
        }

        if (newIndex !== null) {
            targetList.splice(newIndex, 0, item);
        } else {
            targetList.push(item);
        }

        takeSnapshot();
    };

    const selectItem = (id) => {
        selectedItemId.value = id;
    };

    const clearSelection = () => {
        selectedItemId.value = null;
    };

    // ==================== API INTEGRATION ====================

    const fetchMenu = async () => {
        if (!menuId.value) return;

        isLoading.value = true;
        error.value = null;

        try {
            // Fetch menu details
            const menuResponse = await api.get(`/admin/ja/menus/${menuId.value}`);
            menu.value = parseSingleResponse(menuResponse) || {};

            // Fetch menu items
            const itemsResponse = await api.get(`/admin/ja/menus/${menuId.value}/items`);
            const { data } = parseResponse(itemsResponse);
            const flatItems = ensureArray(data);
            items.value = buildTree(flatItems);

            // Mark initial state
            markClean();

            // Initialize history
            history.value = [];
            historyIndex.value = -1;
            takeSnapshot();

        } catch (err) {
            console.error('Failed to fetch menu:', err);
            error.value = err;
        } finally {
            isLoading.value = false;
        }
    };

    const saveMenu = async (menuData = {}) => {
        if (!menuId.value) return;

        isSaving.value = true;
        error.value = null;

        try {
            // Update menu settings if provided
            if (Object.keys(menuData).length > 0) {
                await api.put(`/admin/ja/menus/${menuId.value}`, {
                    ...menu.value,
                    ...menuData
                });
            }

            // Save new items first
            await saveNewItems(items.value, null);

            // Reorder all items
            const flatItems = flattenTree(items.value);
            await api.post(`/admin/ja/menus/${menuId.value}/reorder`, { items: flatItems });

            // Refresh to get updated IDs
            await fetchMenu();

            return true;
        } catch (err) {
            console.error('Failed to save menu:', err);
            error.value = err;
            return false;
        } finally {
            isSaving.value = false;
        }
    };

    /**
     * Recursively save new items (items without real IDs)
     */
    const saveNewItems = async (itemsList, parentId) => {
        for (let i = 0; i < itemsList.length; i++) {
            const item = itemsList[i];

            if (!item.id || item.id.toString().startsWith('temp_')) {
                const payload = {
                    menu_id: menuId.value,
                    parent_id: parentId,
                    title: item.title,
                    type: item.type,
                    target_id: item.target_id,
                    url: item.url,
                    icon: item.icon || null,
                    css_class: item.css_class || null,
                    description: item.description || null,
                    badge: item.badge || null,
                    badge_color: item.badge_color || 'primary',
                    open_in_new_tab: item.open_in_new_tab || false,
                    is_active: item.is_active ?? 1,
                    image: item.image || null,
                    image_size: item.image_size || 'auto',
                    mega_menu_layout: item.mega_menu_layout || 'default',
                    mega_menu_column: item.mega_menu_column || 0,
                    mega_menu_show_dividers: item.mega_menu_show_dividers || false,
                    hide_label: item.hide_label || false,
                    heading: item.heading || null,
                    show_heading_line: item.show_heading_line || false,
                    sort_order: i,
                };

                const response = await api.post(`/admin/ja/menus/${menuId.value}/items`, payload);
                const newItem = response.data?.data || response.data;
                item.id = newItem.id;
                delete item._temp_id;
            }

            if (item.children && item.children.length > 0) {
                await saveNewItems(item.children, item.id);
            }
        }
    };

    const deleteItem = async (id) => {
        const item = findItemById(id);
        if (!item) return;

        // If item has a real ID, delete from server
        if (item.id && !item.id.toString().startsWith('temp_')) {
            await api.delete(`/admin/ja/menus/${menuId.value}/items/${item.id}`);
        }

        removeItem(id);
    };

    // ==================== WATCHERS ====================

    // Debounced snapshot on items change
    let snapshotTimeout;
    watch(items, () => {
        if (isUndoing.value) return;
        clearTimeout(snapshotTimeout);
        snapshotTimeout = setTimeout(() => {
            takeSnapshot();
        }, 500);
    }, { deep: true });

    // Fetch menu when ID changes
    watch(menuId, (newId) => {
        if (newId) fetchMenu();
    }, { immediate: true });

    // ==================== RETURN ====================
    return {
        // State
        menu,
        items,
        selectedItem,
        selectedItemId,
        isLoading,
        isSaving,
        error,
        isDirty,

        // History
        canUndo,
        canRedo,
        undo,
        redo,
        takeSnapshot,

        // Clipboard
        clipboard,
        canPaste,
        copyItem,
        cutItem,
        pasteItem,

        // Item Helpers
        findItemById,
        findParent,
        buildTree,
        flattenTree,

        // Item Actions
        addItem,
        removeItem,
        updateItem,
        duplicateItem,
        moveItem,
        selectItem,
        clearSelection,

        // API
        fetchMenu,
        saveMenu,
        deleteItem,
        markClean,

        // Frontend / Shared
        menus,
        fetchMenuByLocation
    };
}

/**
 * Provide menu context for child components
 */
export function provideMenu(menuState) {
    provide(MENU_CONTEXT_KEY, menuState);
}

/**
 * Inject menu context in child components
 */
export function useMenuContext() {
    const context = inject(MENU_CONTEXT_KEY);
    if (!context) {
        throw new Error('useMenuContext must be used within a MenuBuilder that provides the context');
    }
    return context;
}
