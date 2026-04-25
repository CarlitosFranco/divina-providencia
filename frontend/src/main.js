import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// La URL base de tu servidor en InfinityFree
const API_BASE = 'https://divinaprovidencia.infinityfreeapp.com'

axios.defaults.baseURL = API_BASE

// Interceptor para transformar /api/... a /proxy.php?route=...
axios.interceptors.request.use(config => {
    // Asegurarse de que la ruta comience con '/api/' (como tu LoginView)
    if (config.url && config.url.startsWith('/api/')) {
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
