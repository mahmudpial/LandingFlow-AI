<script setup lang="ts">
import { reactive, ref } from 'vue';
import { apiClient, ensureCsrfCookie, extractErrorMessage } from '@/services/api';
import { useToastStore } from '@/stores/toast';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import ThemeToggle from '@/components/ui/ThemeToggle.vue';

const toastStore = useToastStore();

const step = ref<'request' | 'reset'>('request');
const loading = ref(false);
const errorMessage = ref('');
const statusMessage = ref('');

const form = reactive({
  email: '',
  token: '',
  password: '',
  password_confirmation: '',
});

async function handleRequestReset(): Promise<void> {
  loading.value = true;
  errorMessage.value = '';
  try {
    await ensureCsrfCookie();
    const response = await apiClient.post('/auth/forgot-password', { email: form.email });
    statusMessage.value = response.data.message;
    if (response.data.data?.debug_reset_token) {
      form.token = response.data.data.debug_reset_token;
    }
    step.value = 'reset';
  } catch (err) {
    errorMessage.value = extractErrorMessage(err);
  } finally {
    loading.value = false;
  }
}

async function handleResetPassword(): Promise<void> {
  loading.value = true;
  errorMessage.value = '';
  try {
    await ensureCsrfCookie();
    await apiClient.post('/auth/reset-password', form);
    toastStore.success('Password reset complete', 'You can now sign in with your new password.');
    statusMessage.value = 'Password updated! Proceed to Sign In.';
  } catch (err) {
    errorMessage.value = extractErrorMessage(err);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="relative flex min-h-screen flex-col justify-center bg-[#FAFAFA] px-4 py-12 text-zinc-900 dark:bg-[#09090B] dark:text-zinc-100 sm:px-6 lg:px-8">
    <div class="absolute right-6 top-6">
      <ThemeToggle :show-label="true" />
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <RouterLink to="/" class="flex items-center justify-center gap-2.5">
        <img src="/logo.svg" alt="LandingFlow" class="h-8 w-8 rounded-md" />
        <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-white">LandingFlow Studio</span>
      </RouterLink>
      <h1 class="mt-6 text-center text-2xl font-bold text-zinc-900 dark:text-white">
        {{ step === 'request' ? 'Reset your password' : 'Enter new password' }}
      </h1>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="rounded-xl border border-zinc-200 bg-white px-6 py-8 shadow-subtle dark:border-zinc-800 dark:bg-zinc-900 sm:px-10">
        <div
          v-if="statusMessage"
          class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs font-medium text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
        >
          {{ statusMessage }}
        </div>
        <div
          v-if="errorMessage"
          role="alert"
          class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs font-medium text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300"
        >
          {{ errorMessage }}
        </div>

        <form v-if="step === 'request'" class="space-y-4" @submit.prevent="handleRequestReset">
          <BaseInput
            id="forgot-email"
            v-model="form.email"
            label="Account Email"
            type="email"
            placeholder="founder@company.com"
            required
          />
          <BaseButton type="submit" variant="primary" full-width :loading="loading">
            Send Recovery Token
          </BaseButton>
        </form>

        <form v-else class="space-y-4" @submit.prevent="handleResetPassword">
          <BaseInput
            id="reset-token"
            v-model="form.token"
            label="Reset Token"
            placeholder="Paste reset token"
            required
          />
          <BaseInput
            id="reset-password"
            v-model="form.password"
            label="New Password"
            type="password"
            required
          />
          <BaseInput
            id="reset-confirm"
            v-model="form.password_confirmation"
            label="Confirm New Password"
            type="password"
            required
          />
          <BaseButton type="submit" variant="primary" full-width :loading="loading">
            Reset Password
          </BaseButton>
        </form>

        <div class="mt-6 text-center text-xs">
          <RouterLink to="/login" class="font-semibold text-zinc-900 hover:underline dark:text-zinc-200">
            ← Back to Sign In
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
