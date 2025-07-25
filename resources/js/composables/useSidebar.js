import { ref, watch, onMounted } from 'vue';

// Singleton state untuk sidebar
let sidebarState = null;

export function useSidebar() {
    // Jika state sudah ada, return yang existing
    if (sidebarState) {
        return sidebarState;
    }

    // Key untuk localStorage
    const STORAGE_KEY = 'mini-pos-sidebar-collapse';
    
    // Reactive state
    const isCollapse = ref(true);
    const isInitialized = ref(false);

    // Load state dari localStorage
    const loadState = () => {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved !== null) {
                isCollapse.value = JSON.parse(saved);
            }
        } catch (error) {
            console.warn('Failed to load sidebar state from localStorage:', error);
        }
        isInitialized.value = true;
    };

    // Save state ke localStorage
    const saveState = (value) => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
        } catch (error) {
            console.warn('Failed to save sidebar state to localStorage:', error);
        }
    };

    // Toggle sidebar
    const toggleSidebar = () => {
        isCollapse.value = !isCollapse.value;
    };

    // Set sidebar state
    const setSidebarCollapse = (value) => {
        isCollapse.value = value;
    };

    // Watch untuk auto-save
    watch(isCollapse, (newValue) => {
        if (isInitialized.value) {
            saveState(newValue);
        }
    });

    // Initialize on mount
    onMounted(() => {
        loadState();
    });

    // Create singleton state object
    sidebarState = {
        isCollapse,
        isInitialized,
        toggleSidebar,
        setSidebarCollapse,
        loadState,
        saveState
    };

    return sidebarState;
}

// Helper untuk reset state (jika diperlukan)
export function resetSidebarState() {
    sidebarState = null;
    localStorage.removeItem('mini-pos-sidebar-collapse');
}
