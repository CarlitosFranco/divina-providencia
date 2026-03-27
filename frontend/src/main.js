import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// Configurar URL base de axios: si existe la variable de entorno, la usa; si no, queda vacía
axios.defaults.baseURL = import.meta.env.VITE_API_URL || ''

const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

createApp(App).use(router).mount('#app')
