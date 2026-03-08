import { fileURLToPath, URL } from 'node:url' // Importez ces outils Node.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      // Cette ligne lie le caractère '@' au dossier 'src' de votre projet
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})
