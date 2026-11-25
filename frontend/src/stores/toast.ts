import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface ToastItem {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message?: string;
}

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<ToastItem[]>([]);

  function show(
    title: string,
    type: ToastItem['type'] = 'info',
    message?: string,
    durationMs = 4200
  ): void {
    const id = `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
    toasts.value.push({ id, type, title, message });

    if (durationMs > 0) {
      setTimeout(() => {
        dismiss(id);
      }, durationMs);
    }
  }

  function success(title: string, message?: string): void {
    show(title, 'success', message);
  }

  function error(title: string, message?: string): void {
    show(title, 'error', message, 6000);
  }

  function warning(title: string, message?: string): void {
    show(title, 'warning', message);
  }

  function dismiss(id: string): void {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }

  return {
    toasts,
    show,
    success,
    error,
    warning,
    dismiss,
  };
});
