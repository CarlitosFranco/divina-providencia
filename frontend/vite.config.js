import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

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
        rewrite: (path) => {
          // Separar la ruta base de los parámetros de consulta (ej. /api/asistencia?fecha=...)
          const [base, query] = path.split('?');
          // Eliminar el prefijo /api/
          const route = base.replace(/^\/api\//, '');
          // Construir la nueva URL: index.php?route=...&parametros
          let newPath = `/index.php?route=${route}`;
          if (query) newPath += `&${query}`;
          return newPath;
        },
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req, res) => {
            if (req.headers.authorization) {
              proxyReq.setHeader('Authorization', req.headers.authorization);
            }
          });
        }
      }
    }
  }
})
