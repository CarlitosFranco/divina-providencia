import axios from 'axios';

const API_BASE = import.meta.env.VITE_API_URL || '';
if (API_BASE) {
    axios.defaults.baseURL = API_BASE;
}

// Interceptor para transformar /api/* en /index.php?route=* (si lo necesitas)
axios.interceptors.request.use(config => {
    if (API_BASE && config.url && config.url.startsWith('/api/')) {
        const route = config.url.slice(5);
        config.url = `/index.php?route=${route}`;
    }
    return config;
});