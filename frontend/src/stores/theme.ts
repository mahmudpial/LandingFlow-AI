import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export type ThemeMode = 'light' | 'dark';

const STORAGE_KEY = 'landingflow_theme_mode';

export const useThemeStore = defineStore('theme', () => {
  const mode = ref<ThemeMode>('light');

  const isDark = computed(() => mode.value === 'dark');

  function applyDomTheme(target: ThemeMode): void {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;
    if (target === 'dark') {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  }

  function initTheme(): void {
    if (typeof window === 'undefined') return;
    const saved = localStorage.getItem(STORAGE_KEY) as ThemeMode | null;
    if (saved === 'dark' || saved === 'light') {
      mode.value = saved;
    } else {
      const prefersDark =
        typeof window.matchMedia === 'function' &&
        window.matchMedia('(prefers-color-scheme: dark)').matches;
      mode.value = prefersDark ? 'dark' : 'light';
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
