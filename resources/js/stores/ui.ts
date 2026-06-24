import {defineStore} from 'pinia';
import {ref} from 'vue';

export const useUiStore = defineStore('ui', () => {
    const sidebarOpen = ref(false);
    const searchOpen = ref(false);
    const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');

    function toggleSidebar(): void {
        sidebarOpen.value = !sidebarOpen.value;
    }

    function closeSidebar(): void {
        sidebarOpen.value = false;
    }

    function toggleSearch(): void {
        searchOpen.value = !searchOpen.value;
    }

    function toggleSidebarCollapsed(): void {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString());
    }

    return {
        sidebarOpen,
        searchOpen,
        sidebarCollapsed,
        toggleSidebar,
        closeSidebar,
        toggleSearch,
        toggleSidebarCollapsed
    };
});
