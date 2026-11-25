import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiClient, ensureCsrfCookie } from '@/services/api';
import type { ApiResponse, User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const initialized = ref(false);
  const loading = ref(false);

  const isAuthenticated = computed(() => user.value !== null);

  async function fetchCurrentUser(): Promise<User | null> {
    try {
      loading.value = true;
      const response = await apiClient.get<ApiResponse<{ user: User }>>('/auth/me');
      user.value = response.data.data.user;
      return user.value;
    } catch {
      user.value = null;
      return null;
    } finally {
      initialized.value = true;
      loading.value = false;
    }
  }

  async function register(payload: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    company_name?: string;
    job_title?: string;
  }): Promise<User> {
    loading.value = true;
    try {
      await ensureCsrfCookie();
      const response = await apiClient.post<ApiResponse<{ user: User }>>('/auth/register', payload);
      user.value = response.data.data.user;
      initialized.value = true;
      return user.value;
    } finally {
      loading.value = false;
    }
  }

  async function login(payload: { email: string; password: string; remember?: boolean }): Promise<User> {
    loading.value = true;
    try {
      await ensureCsrfCookie();
      const response = await apiClient.post<ApiResponse<{ user: User }>>('/auth/login', payload);
      user.value = response.data.data.user;
      initialized.value = true;
      return user.value;
    } finally {
      loading.value = false;
    }
  }

  async function logout(): Promise<void> {
    loading.value = true;
    try {
      await ensureCsrfCookie();
      await apiClient.post('/auth/logout');
    } finally {
      user.value = null;
      loading.value = false;
    }
  }

  async function updateProfile(payload: {
    name: string;
    email: string;
    company_name?: string | null;
    job_title?: string | null;
  }): Promise<User> {
    await ensureCsrfCookie();
    const response = await apiClient.put<ApiResponse<{ user: User }>>('/auth/profile', payload);
    user.value = response.data.data.user;
    return user.value;
  }

  async function updatePassword(payload: {
    current_password: string;
    password: string;
    password_confirmation: string;
  }): Promise<void> {
    await ensureCsrfCookie();
    await apiClient.put('/auth/password', payload);
  }

  async function verifyEmail(): Promise<User> {
    await ensureCsrfCookie();
    const response = await apiClient.post<ApiResponse<{ user: User }>>('/auth/email/verify');
    user.value = response.data.data.user;
    return user.value;
  }

  return {
    user,
    initialized,
    loading,
    isAuthenticated,
    fetchCurrentUser,
    register,
    login,
    logout,
    updateProfile,
    updatePassword,
    verifyEmail,
  };
});
