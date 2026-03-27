<template>
  <div class="login-container">
    <h2>Iniciar Sesión</h2>
    <form @submit.prevent="login">
      <div>
        <label>Email:</label>
        <input v-model="email" type="email" required />
      </div>
      <div>
        <label>Contraseña:</label>
        <input v-model="password" type="password" required />
      </div>
      <button type="submit">Ingresar</button>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const email = ref('')
const password = ref('')
const error = ref('')
const router = useRouter()

const login = async () => {
  try {
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value
    })
    const { token, usuario } = response.data
    localStorage.setItem('token', token)
    localStorage.setItem('usuario', JSON.stringify(usuario))
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.error || 'Error al iniciar sesión'
  }
}
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
}
.error {
  color: red;
}
</style>
