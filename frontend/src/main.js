import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// Configurar axios globalmente
const token = localStorage.getItem('token')
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Hacer axios disponible globalmente (opcional)
const app = createApp(App)
app.config.globalProperties.$axios = axios

app.use(router)
app.mount('#app')
