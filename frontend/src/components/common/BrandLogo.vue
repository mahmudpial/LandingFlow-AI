<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: 'sm' | 'md' | 'lg';
    showTag?: boolean;
    subtitle?: string;
  }>(),
  {
    size: 'md',
    showTag: true,
    subtitle: 'STUDIO',
  }
);
</script>

<template>
  <div class="inline-flex items-center gap-2.5 select-none group">
    <!-- Custom Multi-Layered Architectural Emblem SVG -->
    <div
      class="relative flex shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-950 shadow-sm ring-1 ring-zinc-900/10 transition-transform duration-200 group-hover:scale-[1.03] dark:bg-zinc-900 dark:ring-white/15"
      :class="{
        'h-6 w-6 rounded-md': size === 'sm',
        'h-7 w-7 rounded-lg': size === 'md',
        'h-9 w-9 rounded-xl': size === 'lg',
      }"
    >
      <svg
        viewBox="0 0 40 40"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="h-full w-full"
        aria-hidden="true"
      >
        <defs>
          <linearGradient id="lf-brand-bg" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#27272A" />
            <stop offset="100%" stop-color="#09090B" />
          </linearGradient>
          <linearGradient id="lf-brand-accent" x1="20" y1="8" x2="32" y2="20" gradientUnits="userSpaceOnUse">
            <stop offset="0%" stop-color="#FB7185" />
            <stop offset="100%" stop-color="#E11D48" />
          </linearGradient>
        </defs>
        <rect width="40" height="40" fill="url(#lf-brand-bg)" />
        <!-- Subtle top highlight line -->
        <line x1="4" y1="1" x2="36" y2="1" stroke="#FFFFFF" stroke-opacity="0.22" stroke-width="1.5" />
        <!-- Primary L-Frame -->
        <path
          d="M11 10.5C11 9.67157 11.6716 9 12.5 9H15.5C16.3284 9 17 9.67157 17 10.5V25H27.5C28.3284 25 29 25.6716 29 26.5V29.5C29 30.3284 28.3284 31 27.5 31H12.5C11.6716 31 11 30.3284 11 29.5V10.5Z"
          fill="#FFFFFF"
        />
        <!-- Precision Vermilion Wing -->
        <path
          d="M20 9H28.5C29.3284 9 30 9.67157 30 10.5V13.5C30 14.3284 29.3284 15 28.5 15H20V9Z"
          fill="url(#lf-brand-accent)"
        />
        <!-- Secondary Structural Bar -->
        <path
          d="M20 17.5H26C26.8284 17.5 27.5 18.1716 27.5 19V20.5C27.5 21.3284 26.8284 22 26 22H20V17.5Z"
          fill="#FFFFFF"
          fill-opacity="0.6"
        />
      </svg>
    </div>

    <!-- Wordmark + Precision Badge Lockup -->
    <div class="flex items-center gap-1.5">
      <span
        class="font-bold tracking-tight text-zinc-950 dark:text-white"
        :class="{
          'text-xs': size === 'sm',
          'text-[14px]': size === 'md',
          'text-base': size === 'lg',
        }"
      >
        Landing<span class="font-normal text-zinc-500 dark:text-zinc-400">Flow</span>
      </span>

      <span
        v-if="showTag"
        class="inline-flex items-center rounded border border-zinc-200 bg-zinc-100 px-1.5 py-0.5 font-mono text-[9px] font-semibold uppercase tracking-widest text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
      >
        {{ subtitle }}
      </span>
    </div>
  </div>
</template>
