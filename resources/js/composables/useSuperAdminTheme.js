import { ref, watch } from "vue";

const STORAGE_KEY = "sa_theme_dark";

const readStored = () => {
    try {
        const value = localStorage.getItem(STORAGE_KEY);
        return value === null ? true : value === "true";
    } catch {
        return true;
    }
};

const isDark = ref(readStored());

watch(isDark, (value) => {
    try {
        localStorage.setItem(STORAGE_KEY, String(value));
    } catch {
        
    }
});

export function useSuperAdminTheme() {
    const toggleTheme = () => {
        isDark.value = !isDark.value;
    };

    return { isDark, toggleTheme };
}