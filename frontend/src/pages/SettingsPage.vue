<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToastStore } from '@/stores/toast';
import { extractErrorMessage } from '@/services/api';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import { CheckCircle2, Shield } from 'lucide-vue-next';

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
    toastStore.success('Profile updated', 'Your account settings have been saved.');
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
    toastStore.success('Password updated', 'Your password has been changed.');
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
    toastStore.success('Email verified', 'Your email address is now marked verified.');
  } catch (err) {
    toastStore.error('Verification failed', extractErrorMessage(err));
  } finally {
    verifyingEmail.value = false;
  }
}
</script>

<template>
  <DashboardLayout>
    <div class="mx-auto max-w-3xl space-y-8">
      <!-- Profile Details -->
      <div class="rounded-2xl border border-brand-border bg-white p-6 shadow-card">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h2 class="text-base font-bold text-brand-text">Account Profile</h2>
            <p class="text-xs text-brand-muted">
              Update your name, organization, and verified contact email.
            </p>
          </div>
          <div class="flex items-center gap-2">
            <BaseBadge :variant="authStore.user?.email_verified ? 'published' : 'draft'">
              {{ authStore.user?.email_verified ? 'Verified Email' : 'Unverified Email' }}
            </BaseBadge>
            <BaseButton
              v-if="!authStore.user?.email_verified"
              size="sm"
              variant="outline"
              :loading="verifyingEmail"
              @click="handleVerifyEmail"
            >
              <CheckCircle2 class="h-3.5 w-3.5" />
              <span>Verify Now</span>
            </BaseButton>
          </div>
        </div>

        <div
          v-if="profileError"
          role="alert"
          class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-brand-error"
        >
          {{ profileError }}
        </div>

        <form class="mt-5 space-y-4" @submit.prevent="handleSaveProfile">
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
              label="Email Address"
              type="email"
              required
            />
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <BaseInput
              id="set-company"
              v-model="profileForm.company_name"
              label="Company / Studio"
            />
            <BaseInput
              id="set-role"
              v-model="profileForm.job_title"
              label="Job Title"
            />
          </div>
          <div class="flex justify-end">
            <BaseButton type="submit" variant="primary" :loading="savingProfile">
              Save Profile Changes
            </BaseButton>
          </div>
        </form>
      </div>

      <!-- Password & Security -->
      <div class="rounded-2xl border border-brand-border bg-white p-6 shadow-card">
        <div class="flex items-center gap-2.5 border-b border-slate-100 pb-4">
          <Shield class="h-5 w-5 text-brand-primary" />
          <div>
            <h2 class="text-base font-bold text-brand-text">Password & Session Security</h2>
            <p class="text-xs text-brand-muted">
              Authenticated via Laravel Sanctum HTTP-only session cookies with CSRF protection.
            </p>
          </div>
        </div>

        <div
          v-if="passwordError"
          role="alert"
          class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-brand-error"
        >
          {{ passwordError }}
        </div>

        <form class="mt-5 space-y-4" @submit.prevent="handleUpdatePassword">
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
          <div class="flex justify-end">
            <BaseButton type="submit" variant="outline" :loading="savingPassword">
              Update Password
            </BaseButton>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>
