/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
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
        'card': '0 1px 2px 0 rgba(9, 9, 11, 0.03), 0 0 0 1px rgba(9, 9, 11, 0.04)',
        'elevated': '0 20px 40px -15px rgba(9, 9, 11, 0.1), 0 0 0 1px rgba(9, 9, 11, 0.06)',
        'tactile': '0 1px 2px 0 rgba(9, 9, 11, 0.25), inset 0 1px 0 0 rgba(255, 255, 255, 0.16)',
        'subtle': '0 1px 1px 0 rgba(9, 9, 11, 0.03)',
      },
      keyframes: {
        'fade-up': {
          '0%': { opacity: '0', transform: 'translateY(8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'scale-in': {
          '0%': { opacity: '0', transform: 'scale(0.98)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },
      animation: {
        'fade-up': 'fade-up 0.28s cubic-bezier(0.16, 1, 0.3, 1) both',
        'fade-in': 'fade-in 0.18s ease-out both',
        'scale-in': 'scale-in 0.2s cubic-bezier(0.16, 1, 0.3, 1) both',
      },
    },
  },
  plugins: [],
};
