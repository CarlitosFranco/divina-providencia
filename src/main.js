import axios from 'axios';

const API_BASE = import.meta.env.VITE_API_URL || '';

axios.defaults.baseURL = API_BASE;

// Interceptor para convertir las rutas /api/... en ?route=...
axios.interceptors.request.use(config => {
    // Solo en producción (cuando API_BASE no está vacío)
    if (API_BASE && config.url && config.url.startsWith('/api/')) {
        const route = config.url.slice(5); // quita '/api/'
        config.url = `/index.php?route=${route}`;
    }
    return config;
});