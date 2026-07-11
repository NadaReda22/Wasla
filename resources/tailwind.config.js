/** @type {import('tailwindcss').Config} */
export default {
  // 👇 UPDATED FOR LARAVEL BLADE 👇
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#1877C9',
          50: '#E8F4FC',
          100: '#D1E9F9',
          200: '#A3D3F3',
          300: '#75BDED',
          400: '#47A7E7',
          500: '#1877C9',
          600: '#1360A0',
          700: '#0E4977',
          800: '#0A3250',
          900: '#0A2540',
        },
        navy: {
          DEFAULT: '#0A2540',
          light: '#14365E',
          dark: '#061A30',
        },
        gold: {
          DEFAULT: '#F5B400',
          light: '#FFD24D',
          dark: '#C99000',
        },
        coral: {
          DEFAULT: '#E63946',
          light: '#FF6B78',
          dark: '#B82A36',
        },
        turquoise: {
          DEFAULT: '#17A2B8',
          light: '#48C4D8',
          dark: '#0E7A8C',
        },
        sand: {
          DEFAULT: '#F0E4D0',
          light: '#F8F1E3',
          dark: '#D9C9A8',
        },
      },
      fontFamily: {
        sans: ['Poppins', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'system-ui', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.6s ease-out',
        'slide-up': 'slideUp 0.5s ease-out',
        'slide-down': 'slideDown 0.4s ease-out',
        'float': 'float 3s ease-in-out infinite',
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'shimmer': 'shimmer 2s linear infinite',
        'scale-in': 'scaleIn 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(30px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideDown: {
          '0%': { transform: 'translateY(-20px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-1000px 0' },
          '100%': { backgroundPosition: '1000px 0' },
        },
        scaleIn: {
          '0%': { transform: 'scale(0.95)', opacity: '0' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
      },
    },
  },
  plugins: [],
}