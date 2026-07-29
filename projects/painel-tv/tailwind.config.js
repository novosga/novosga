/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        pmi: {
          blue: 'rgb(var(--pmi-blue) / <alpha-value>)',
          green: 'rgb(var(--pmi-green) / <alpha-value>)',
          red: 'rgb(var(--pmi-red) / <alpha-value>)',
          'blue-light': '#004fa8',
          'green-light': '#009a4e',
        },
      },
      keyframes: {
        slideIn: { '0%': { transform: 'translateY(100%)', opacity: '0' }, '100%': { transform: 'translateY(0)', opacity: '1' } },
        fadeIn: { '0%': { opacity: '0', transform: 'scale(0.95)' }, '100%': { opacity: '1', transform: 'scale(1)' } },
        blink: { '0%,100%': { opacity: '1' }, '50%': { opacity: '0.4' } },
      },
      animation: {
        slideIn: 'slideIn 0.5s ease-out',
        fadeIn: 'fadeIn 0.4s ease-out',
        calling: 'blink 0.8s ease-in-out 4',
      },
    },
  },
  plugins: [],
}
