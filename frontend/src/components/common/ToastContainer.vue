<script setup lang="ts">
import { useToastStore } from '@/stores/toast';
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next';

const toastStore = useToastStore();
</script>

<template>
  <div
    aria-live="polite"
    class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2.5"
  >
    <div
      v-for="toast in toastStore.toasts"
      :key="toast.id"
      class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-4 shadow-elevated transition-all dark:bg-zinc-900"
      :class="{
        'border-emerald-200 dark:border-emerald-800': toast.type === 'success',
        'border-rose-200 dark:border-rose-800': toast.type === 'error',
        'border-amber-200 dark:border-amber-800': toast.type === 'warning',
        'border-zinc-200 dark:border-zinc-700': toast.type === 'info',
      }"
    >
      <CheckCircle2 v-if="toast.type === 'success'" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
      <AlertCircle v-else-if="toast.type === 'error'" class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
      <AlertTriangle v-else-if="toast.type === 'warning'" class="h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" />
      <Info v-else class="h-5 w-5 shrink-0 text-zinc-900 dark:text-zinc-100" />

      <div class="flex-1 text-sm">
        <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ toast.title }}</p>
        <p v-if="toast.message" class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ toast.message }}</p>
      </div>
      <button
        type="button"
        class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
        aria-label="Dismiss notification"
        @click="toastStore.dismiss(toast.id)"
      >
        <X class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>
