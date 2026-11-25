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
      class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-4 shadow-elevated transition-all"
      :class="{
        'border-emerald-200': toast.type === 'success',
        'border-red-200': toast.type === 'error',
        'border-amber-200': toast.type === 'warning',
        'border-slate-200': toast.type === 'info',
      }"
    >
      <CheckCircle2 v-if="toast.type === 'success'" class="h-5 w-5 shrink-0 text-brand-success" />
      <AlertCircle v-else-if="toast.type === 'error'" class="h-5 w-5 shrink-0 text-brand-error" />
      <AlertTriangle v-else-if="toast.type === 'warning'" class="h-5 w-5 shrink-0 text-brand-warning" />
      <Info v-else class="h-5 w-5 shrink-0 text-brand-primary" />

      <div class="flex-1 text-sm">
        <p class="font-semibold text-brand-text">{{ toast.title }}</p>
        <p v-if="toast.message" class="mt-0.5 text-xs text-brand-muted">{{ toast.message }}</p>
      </div>
      <button
        type="button"
        class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
        aria-label="Dismiss notification"
        @click="toastStore.dismiss(toast.id)"
      >
        <X class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>
