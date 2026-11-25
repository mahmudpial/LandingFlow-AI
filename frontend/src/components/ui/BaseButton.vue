<script setup lang="ts">
import { computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

const props = withDefaults(
  defineProps<{
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    loading?: boolean;
    disabled?: boolean;
    fullWidth?: boolean;
  }>(),
  {
    variant: 'primary',
    size: 'md',
    type: 'button',
    loading: false,
    disabled: false,
    fullWidth: false,
  }
);

const classes = computed(() => {
  const base =
    'inline-flex items-center justify-center gap-1.5 font-medium tracking-tight rounded-md transition-all duration-150 active:scale-[0.99] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-900 dark:focus-visible:ring-zinc-100 focus-visible:ring-offset-1 disabled:opacity-45 disabled:cursor-not-allowed disabled:active:scale-100 select-none';

  const variants: Record<string, string> = {
    primary:
      'bg-zinc-900 text-white hover:bg-zinc-800 border border-zinc-950 shadow-tactile dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-white dark:border-white',
    secondary:
      'bg-rose-600 text-white hover:bg-rose-500 border border-rose-700 shadow-tactile dark:bg-rose-600 dark:text-white dark:hover:bg-rose-500 dark:border-rose-500',
    outline:
      'border border-zinc-200 bg-white text-zinc-800 hover:bg-zinc-50 hover:border-zinc-300 shadow-subtle dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:bg-zinc-800 dark:hover:border-zinc-700',
    ghost:
      'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800',
    danger:
      'bg-rose-600 text-white hover:bg-rose-700 shadow-tactile border border-rose-700 dark:bg-rose-600 dark:text-white dark:hover:bg-rose-500',
  };

  const sizes: Record<string, string> = {
    sm: 'h-7 px-2.5 text-xs',
    md: 'h-8 px-3 text-[13px]',
    lg: 'h-9 px-4 text-[13px]',
  };

  return [
    base,
    variants[props.variant],
    sizes[props.size],
    props.fullWidth ? 'w-full' : '',
  ].join(' ');
});
</script>

<template>
  <button :type="type" :class="classes" :disabled="disabled || loading">
    <Loader2 v-if="loading" class="h-3.5 w-3.5 animate-spin" aria-hidden="true" />
    <slot />
  </button>
</template>
