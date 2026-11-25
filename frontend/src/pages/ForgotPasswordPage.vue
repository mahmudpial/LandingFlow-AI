<script setup lang="ts">
import { reactive, ref } from 'vue';
import { apiClient, ensureCsrfCookie, extractErrorMessage } from '@/services/api';
import { useToastStore } from '@/stores/toast';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import { Sparkles } from 'lucide-vue-next';

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
  <div class="flex min-h-screen flex-col justify-center bg-brand-bg px-4 py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <RouterLink to="/" class="flex items-center justify-center gap-2.5">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary text-white shadow-sm"
        >
          <Sparkles class="h-5 w-5" />
        </div>
        <span class="text-xl font-extrabold tracking-tight text-brand-text">LandingFlow AI</span>
      </RouterLink>
      <h1 class="mt-6 text-center text-2xl font-bold text-brand-text">
        {{ step === 'request' ? 'Reset your password' : 'Enter new password' }}
      </h1>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="rounded-2xl border border-brand-border bg-white px-6 py-8 shadow-card sm:px-10">
        <div
          v-if="statusMessage"
          class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-xs font-medium text-emerald-800"
        >
          {{ statusMessage }}
        </div>
        <div
          v-if="errorMessage"
          role="alert"
          class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-xs font-medium text-brand-error"
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
          <RouterLink to="/login" class="font-semibold text-brand-primary hover:underline">
            Back to Sign In
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
