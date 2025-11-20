/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'church-green': {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#2d7a4e', // Primary - 상단 메뉴바 초록
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        'church-light': {
          50: '#fafdf9',
          100: '#f4f9f3',
          200: '#e8f5e9',
          300: '#c5e1a5', // Accent - 연한 초록
          400: '#aed581',
          500: '#8bc34a', // Light Green
          600: '#7cb342',
          700: '#689f38',
          800: '#558b2f',
          900: '#33691e',
        }
      }
    },
  },
  plugins: [],
}
