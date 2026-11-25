<script setup lang="ts">
import { useThemeStore } from '@/stores/theme';
import { Sun, Moon } from 'lucide-vue-next';

withDefaults(
  defineProps<{
    showLabel?: boolean;
  }>(),
  {
    showLabel: false,
  }
);

const themeStore = useThemeStore();
</script>

<template>
  <div
    class="inline-flex h-8 items-center rounded-md border border-zinc-200 bg-zinc-100/90 p-0.5 shadow-subtle dark:border-zinc-800 dark:bg-zinc-900"
    role="group"
    aria-label="Theme Switcher"
  >
    <button
      type="button"
      class="inline-flex h-7 items-center gap-1 rounded px-2 text-xs font-medium transition-all"
      :class="
        !themeStore.isDark
          ? 'bg-white text-zinc-950 shadow-sm'
          : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100'
      "
      title="Light Mode"
      aria-label="Activate Light Mode"
      :aria-pressed="!themeStore.isDark"
      @click="themeStore.setTheme('light')"
    >
      <Sun class="h-3.5 w-3.5 text-amber-500" />
      <span v-if="showLabel">Light</span>
    </button>

    <button
      type="button"
      class="inline-flex h-7 items-center gap-1 rounded px-2 text-xs font-medium transition-all"
      :class="
        themeStore.isDark
          ? 'bg-zinc-800 text-white shadow-sm'
          : 'text-zinc-500 hover:text-zinc-900'
      "
      title="Dark Mode"
      aria-label="Activate Dark Mode"
      :aria-pressed="themeStore.isDark"
      @click="themeStore.setTheme('dark')"
    >
      <Moon class="h-3.5 w-3.5" />
      <span v-if="showLabel">Dark</span>
    </button>
  </div>
</template>
