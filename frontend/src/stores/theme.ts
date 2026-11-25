import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export type ThemeMode = 'light' | 'dark';

const STORAGE_KEY = 'landingflow_theme_mode_v2';

export const useThemeStore = defineStore('theme', () => {
  const mode = ref<ThemeMode>('light');

  const isDark = computed(() => mode.value === 'dark');

  function applyDomTheme(target: ThemeMode): void {
    if (typeof document === 'undefined') return;
    const html = document.documentElement;
    const body = document.body;

    if (target === 'dark') {
      html.classList.add('dark');
      html.classList.remove('light');
      html.setAttribute('data-theme', 'dark');
      html.style.colorScheme = 'dark';
      body?.classList.add('dark');
    } else {
      html.classList.remove('dark');
      html.classList.add('light');
      html.setAttribute('data-theme', 'light');
      html.style.colorScheme = 'light';
      body?.classList.remove('dark');
    }
  }

  function initTheme(): void {
    if (typeof window === 'undefined') return;
    const saved = localStorage.getItem(STORAGE_KEY) as ThemeMode | null;
    // Default to Light Mode unless user explicitly chose 'dark'
    if (saved === 'dark') {
      mode.value = 'dark';
    } else {
      mode.value = 'light';
    }
    applyDomTheme(mode.value);
  }

  function setTheme(newMode: ThemeMode): void {
    mode.value = newMode;
    if (typeof window !== 'undefined') {
      localStorage.setItem(STORAGE_KEY, newMode);
    }
    applyDomTheme(newMode);
  }

  function toggleTheme(): void {
    setTheme(mode.value === 'dark' ? 'light' : 'dark');
  }

  return {
    mode,
    isDark,
    initTheme,
    setTheme,
    toggleTheme,
  };
});
