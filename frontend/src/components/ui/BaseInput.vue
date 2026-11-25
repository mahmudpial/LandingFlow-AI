<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string | number;
    id?: string;
    label?: string;
    type?: string;
    placeholder?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
  }>(),
  {
    type: 'text',
    placeholder: '',
    required: false,
    disabled: false,
  }
);

defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="id"
      class="mb-1.5 block text-xs font-medium text-zinc-700 dark:text-zinc-300"
    >
      {{ label }}
      <span v-if="required" class="ml-0.5 text-rose-600 dark:text-rose-400" aria-hidden="true">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :aria-invalid="Boolean(error)"
      class="h-8 w-full rounded-md border px-2.5 text-[13px] text-zinc-900 placeholder-zinc-400 shadow-subtle transition-colors duration-150 focus:outline-none focus:ring-2 disabled:bg-zinc-100 disabled:text-zinc-500 dark:text-zinc-100 dark:placeholder-zinc-500 dark:disabled:bg-zinc-800 dark:disabled:text-zinc-500"
      :class="
        error
          ? 'border-rose-500 bg-rose-50/20 focus:border-rose-600 focus:ring-rose-500/15 dark:border-rose-500 dark:bg-rose-950/30'
          : 'border-zinc-200 bg-white hover:border-zinc-300 focus:border-zinc-900 focus:ring-zinc-900/10 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:border-zinc-700 dark:focus:border-zinc-300 dark:focus:ring-zinc-100/10'
      "
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" role="alert" class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">
      {{ error }}
    </p>
    <p v-else-if="hint" class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">
      {{ hint }}
    </p>
  </div>
</template>
