import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/divina-providencia/backend',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/index.php'),
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req, res) => {
            // Asegurar que la cabecera Authorization se pase
            if (req.headers.authorization) {
              proxyReq.setHeader('Authorization', req.headers.authorization);
            }
          });
        }
      }
    }
  }
})
