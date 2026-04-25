import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// Tomar la URL base de la variable de entorno o vacío en desarrollo
const API_BASE = import.meta.env.VITE_API_URL || ''

if (API_BASE) {
    axios.defaults.baseURL = API_BASE
}

// Interceptor para transformar /api/... a /proxy.php?route=...
axios.interceptors.request.use(config => {
    if (API_BASE && config.url && config.url.startsWith('/api/')) {
        const route = config.url.slice(5) // quita '/api/'
        config.url = `/proxy.php?route=${route}`
    }
    return config
})

// Opcional: hacer axios global
const app = createApp(App)
app.config.globalProperties.$axios = axios

app.use(router)
app.mount('#app')
