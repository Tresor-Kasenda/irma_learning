import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useUiStore = defineStore('ui', () => {
    const sidebarOpen = ref(false);
    const searchOpen = ref(false);

    function toggleSidebar(): void {
        sidebarOpen.value = !sidebarOpen.value;
    }

    function closeSidebar(): void {
        sidebarOpen.value = false;
    }

    function toggleSearch(): void {
        searchOpen.value = !searchOpen.value;
    }

    return { sidebarOpen, searchOpen, toggleSidebar, closeSidebar, toggleSearch };
});
