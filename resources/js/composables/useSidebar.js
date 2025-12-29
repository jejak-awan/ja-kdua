import { ref, onMounted } from 'vue';

/**
 * Composable for managing sidebar state
 */
export function useSidebar() {
    const sidebarMinimized = ref(false);
    const sidebarOpen = ref(false);

    // Load sidebar minimized state from localStorage
    const saved = localStorage.getItem('sidebarMinimized');
    if (saved !== null) {
        sidebarMinimized.value = saved === 'true';
    }

    const loadSidebarState = () => {
        // Already loaded initially
    };

    // Save sidebar minimized state to localStorage
    const saveSidebarState = () => {
        localStorage.setItem('sidebarMinimized', sidebarMinimized.value.toString());
    };

    // Toggle sidebar minimize
    const toggleSidebarMinimize = () => {
        sidebarMinimized.value = !sidebarMinimized.value;
        saveSidebarState();
    };

    // Toggle sidebar open (mobile)
    const toggleSidebarOpen = () => {
        sidebarOpen.value = !sidebarOpen.value;
    };

    // Close sidebar (mobile)
    const closeSidebar = () => {
        sidebarOpen.value = false;
    };

    // removed onMounted usage for state loading

    return {
        sidebarMinimized,
        sidebarOpen,
        toggleSidebarMinimize,
        toggleSidebarOpen,
        closeSidebar,
    };
}

