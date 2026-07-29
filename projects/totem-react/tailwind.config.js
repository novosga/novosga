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
        },
      },
    },
  },
  plugins: [],
}
