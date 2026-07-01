import {defineStore} from 'pinia';
import {ref} from 'vue';

export type UiTheme = 'light' | 'dark';

function initialTheme(): UiTheme {
    const storedTheme = localStorage.getItem('irma-theme');

    return storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : 'dark';
}

export function initializeUiTheme(): UiTheme {
    const theme = initialTheme();
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.dataset.theme = theme;

    return theme;
}

export const useUiStore = defineStore('ui', () => {
    const sidebarOpen = ref(false);
    const searchOpen = ref(false);
    const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
    const theme = ref<UiTheme>(initializeUiTheme());

    function applyTheme(): void {
        document.documentElement.classList.toggle('dark', theme.value === 'dark');
        document.documentElement.dataset.theme = theme.value;
    }

    function setTheme(value: UiTheme): void {
        theme.value = value;
        localStorage.setItem('irma-theme', value);
        applyTheme();
    }

    function toggleTheme(): void {
        setTheme(theme.value === 'dark' ? 'light' : 'dark');
    }

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
        theme,
        toggleSidebar,
        closeSidebar,
        toggleSearch,
        toggleSidebarCollapsed,
        setTheme,
        toggleTheme,
    };
});
