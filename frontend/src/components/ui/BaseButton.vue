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
    'inline-flex items-center justify-center gap-2 font-semibold tracking-tight rounded-lg transition-all duration-200 active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100';

  const variants: Record<string, string> = {
    primary:
      'bg-brand-primary text-[#FAF9F5] hover:bg-brand-primary-hover shadow-tactile border border-brand-primary',
    secondary:
      'bg-brand-secondary text-white hover:bg-[#C2411F] shadow-tactile border border-[#C2411F]',
    outline:
      'border border-brand-border bg-white text-brand-text hover:bg-[#F3F1EC] hover:border-[#CFCBC2] shadow-2xs',
    ghost:
      'text-brand-muted hover:text-brand-text hover:bg-[#EFECE6]',
    danger:
      'bg-brand-error text-white hover:bg-rose-800 shadow-tactile border border-rose-800',
  };

  const sizes: Record<string, string> = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-[13px]',
    lg: 'px-5 py-2.5 text-sm',
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
