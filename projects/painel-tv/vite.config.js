import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  base: '/painel/',
  build: {
    outDir: '/opt/novosga/public/painel',
    emptyOutDir: true,
  },
})
