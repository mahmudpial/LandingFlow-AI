import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
  plugins: [vue()],
  css: {
    postcss: {
      plugins: [
        tailwindcss({
          darkMode: 'selector',
          content: [
            './index.html',
            './src/**/*.{vue,js,ts,jsx,tsx}',
          ],
          theme: {
            extend: {
              colors: {
                brand: {
                  primary: '#09090B',
                  'primary-hover': '#18181B',
                  secondary: '#E11D48',
                  accent: '#059669',
                  bg: '#FAFAFA',
                  surface: '#FFFFFF',
                  subtle: '#F4F4F5',
                  text: '#09090B',
                  muted: '#52525B',
                  border: '#E4E4E7',
                  success: '#059669',
                  warning: '#D97706',
                  error: '#E11D48',
                },
              },
              fontFamily: {
                sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'sans-serif'],
                display: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                serif: ['"Instrument Serif"', 'Georgia', 'serif'],
                mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
              },
              boxShadow: {
                card: '0 1px 2px 0 rgba(9, 9, 11, 0.03), 0 0 0 1px rgba(9, 9, 11, 0.04)',
                elevated: '0 20px 40px -15px rgba(9, 9, 11, 0.14), 0 0 0 1px rgba(9, 9, 11, 0.08)',
                tactile: '0 1px 2px 0 rgba(9, 9, 11, 0.25), inset 0 1px 0 0 rgba(255, 255, 255, 0.16)',
                subtle: '0 1px 1px 0 rgba(9, 9, 11, 0.03)',
              },
            },
          },
        }),
        autoprefixer(),
      ],
    },
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
    host: 'localhost',
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
      '/sanctum': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
      '/storage': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
  },
  test: {
    environment: 'happy-dom',
    globals: true,
  },
});
