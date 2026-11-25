<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { extractErrorMessage } from '@/services/api';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';

const authStore = useAuthStore();
const toastStore = useToastStore();

const profileForm = reactive({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
  company_name: authStore.user?.company_name || '',
  job_title: authStore.user?.job_title || '',
});

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const savingProfile = ref(false);
const savingPassword = ref(false);
const verifyingEmail = ref(false);
const profileError = ref('');
const passwordError = ref('');

async function handleSaveProfile(): Promise<void> {
  savingProfile.value = true;
  profileError.value = '';
  try {
    await authStore.updateProfile(profileForm);
    toastStore.success('Profile updated', 'Workspace identity saved.');
  } catch (err) {
    profileError.value = extractErrorMessage(err);
  } finally {
    savingProfile.value = false;
  }
}

async function handleUpdatePassword(): Promise<void> {
  savingPassword.value = true;
  passwordError.value = '';
  try {
    await authStore.updatePassword(passwordForm);
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
    toastStore.success('Password rotated', 'Your credentials have been updated.');
  } catch (err) {
    passwordError.value = extractErrorMessage(err);
  } finally {
    savingPassword.value = false;
  }
}

async function handleVerifyEmail(): Promise<void> {
  verifyingEmail.value = true;
  try {
    await authStore.verifyEmail();
    toastStore.success('Email verified', 'Account email marked verified.');
  } catch (err) {
    toastStore.error('Verification failed', extractErrorMessage(err));
  } finally {
    verifyingEmail.value = false;
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="mx-auto max-w-3xl space-y-6">
      <div>
        <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">Account & Security</h2>
        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
          Manage your studio profile, organization details, and session credentials.
        </p>
      </div>

      <!-- Profile Section -->
      <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3.5 dark:border-zinc-800">
          <div>
            <h3 class="text-[13px] font-semibold text-zinc-900 dark:text-zinc-100">Operator Profile</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Identity displayed across workspace audit logs.</p>
          </div>
          <div class="flex items-center gap-2">
            <BaseBadge :variant="authStore.user?.email_verified ? 'published' : 'draft'">
              {{ authStore.user?.email_verified ? 'Verified' : 'Unverified' }}
            </BaseBadge>
            <BaseButton
              v-if="!authStore.user?.email_verified"
              size="sm"
              variant="outline"
              :loading="verifyingEmail"
              @click="handleVerifyEmail"
            >
              Verify Email
            </BaseButton>
          </div>
        </div>

        <form class="p-5 space-y-4" @submit.prevent="handleSaveProfile">
          <div
            v-if="profileError"
            role="alert"
            class="rounded-md border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300"
          >
            {{ profileError }}
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <BaseInput
              id="set-name"
              v-model="profileForm.name"
              label="Full Name"
              required
            />
            <BaseInput
              id="set-email"
              v-model="profileForm.email"
              label="Work Email"
              type="email"
              required
            />
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <BaseInput
              id="set-company"
              v-model="profileForm.company_name"
              label="Workspace / Company Name"
            />
            <BaseInput
              id="set-role"
              v-model="profileForm.job_title"
              label="Role"
            />
          </div>
          <div class="flex justify-end border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <BaseButton type="submit" variant="primary" :loading="savingProfile">
              Save Profile
            </BaseButton>
          </div>
        </form>
      </div>

      <!-- Password Rotation -->
      <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-subtle dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-5 py-3.5 dark:border-zinc-800">
          <h3 class="text-[13px] font-semibold text-zinc-900 dark:text-zinc-100">Credential Rotation</h3>
          <p class="text-xs text-zinc-500 dark:text-zinc-400">
            Session protected via HTTP-only Sanctum cookies and CSRF tokens.
          </p>
        </div>

        <form class="p-5 space-y-4" @submit.prevent="handleUpdatePassword">
          <div
            v-if="passwordError"
            role="alert"
            class="rounded-md border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-800 dark:bg-rose-950/60 dark:text-rose-300"
          >
            {{ passwordError }}
          </div>

          <BaseInput
            id="set-cur-pass"
            v-model="passwordForm.current_password"
            label="Current Password"
            type="password"
            required
          />
          <div class="grid gap-4 sm:grid-cols-2">
            <BaseInput
              id="set-new-pass"
              v-model="passwordForm.password"
              label="New Password"
              type="password"
              required
            />
            <BaseInput
              id="set-conf-pass"
              v-model="passwordForm.password_confirmation"
              label="Confirm New Password"
              type="password"
              required
            />
          </div>
          <div class="flex justify-end border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <BaseButton type="submit" variant="outline" :loading="savingPassword">
              Rotate Password
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>
