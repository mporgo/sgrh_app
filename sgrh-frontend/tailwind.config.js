/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#e8f0fa',
          100: '#c5d8f3',
          500: '#2e75b6',
          600: '#1e5a9a',
          700: '#1e3a5f',
          800: '#162d4a',
          900: '#0e1e33',
        },
      },
      fontFamily: {
        sans: ['Inter', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [],
}