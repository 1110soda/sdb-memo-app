import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    const theme = ref(
        localStorage.getItem('theme') || //保存済みのテーマ
        (window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light') //OSの設定
    );

    const isDarkMode = computed(() => theme.value === 'dark');

    const toggleTheme = () => {
        theme.value = theme.value === 'light' ? 'dark' : 'light';
    };

    watch(theme, (newTheme) => {
        localStorage.setItem('theme', newTheme);
        if (newTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }, { immediate: true });

    return {
        theme,
        isDarkMode,
        toggleTheme,
    }
})
