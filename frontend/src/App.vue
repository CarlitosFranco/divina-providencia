<template>
  <div id="app">
    <!-- Barra de navegación -->
    <nav class="navbar">
      <div class="nav-brand">
        <span>Divina Providencia</span>
      </div>
      <ul class="nav-links" v-if="token">
        <li><router-link to="/">Pacientes</router-link></li>
        <li><router-link to="/personal">Personal</router-link></li>
        <li><router-link to="/turnos">Turnos</router-link></li>
        <li><router-link to="/actividades">Actividades</router-link></li>
        <li><button @click="logout" class="btn-logout">Cerrar Sesión</button></li>
      </ul>
      <div v-else class="nav-auth">
        <router-link to="/login">Iniciar Sesión</router-link>
      </div>
    </nav>

    <!-- Contenido principal -->
    <main>
      <router-view />
    </main>S
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const token = ref(localStorage.getItem('token'))
const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('usuario')
  delete axios.defaults.headers.common['Authorization']
  router.push('/login')
  token.value = null
}
</script>

<style scoped>
#app {
  font-family: Arial, sans-serif;
}

.navbar {
  background-color: #2c3e50;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.nav-brand {
  font-size: 1.2rem;
  font-weight: bold;
}

.nav-links {
  list-style: none;
  display: flex;
  gap: 1.5rem;
  margin: 0;
  padding: 0;
  align-items: center;
}

.nav-links li a {
  color: white;
  text-decoration: none;
  transition: opacity 0.3s;
}

.nav-links li a:hover {
  opacity: 0.7;
}

.btn-logout {
  background-color: #e74c3c;
  border: none;
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s;
}

.btn-logout:hover {
  background-color: #c0392b;
}

.nav-auth a {
  color: white;
  text-decoration: none;
}

main {
  padding: 1rem;
}
</style>
