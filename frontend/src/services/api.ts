import axios, { type AxiosError, type AxiosInstance } from 'axios';

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '');

/**
 * Stateful Sanctum Axios client.
 * Uses relative paths (/api/v1 and /sanctum/csrf-cookie) by default so same-origin
 * Vite proxy (in dev) and Nginx (in prod) handle cookies and CSRF without cross-host mismatch.
 */
export const apiClient: AxiosInstance = axios.create({
  baseURL: `${API_BASE_URL}/api/v1`,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

let csrfInitialized = false;

export async function ensureCsrfCookie(): Promise<void> {
  if (csrfInitialized && typeof document !== 'undefined' && document.cookie.includes('XSRF-TOKEN=')) {
    return;
  }
  await axios.get(`${API_BASE_URL}/sanctum/csrf-cookie`, {
    withCredentials: true,
  });
  csrfInitialized = true;
}

export function extractErrorMessage(error: unknown, fallback = 'An unexpected error occurred.'): string {
  if (axios.isAxiosError(error)) {
    const axiosErr = error as AxiosError<{ message?: string; errors?: Record<string, string[]> }>;
    const data = axiosErr.response?.data;
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0];
      if (firstKey && data.errors[firstKey]?.[0]) {
        return data.errors[firstKey][0];
      }
    }
    if (data?.message) {
      return data.message;
    }
  }
  if (error instanceof Error && error.message) {
    return error.message;
  }
  return fallback;
}

export function extractFieldErrors(error: unknown): Record<string, string> {
  const result: Record<string, string> = {};
  if (axios.isAxiosError(error)) {
    const errors = error.response?.data?.errors as Record<string, string[]> | undefined;
    if (errors) {
      for (const [key, messages] of Object.entries(errors)) {
        if (messages && messages.length > 0) {
          result[key] = messages[0];
        }
      }
    }
  }
  return result;
}
