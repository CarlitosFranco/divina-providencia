<template>
  <div v-if="isAuthenticated" class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 13c-2.33 0-4.31-1.46-5.11-3.5h10.22c-.8 2.04-2.78 3.5-5.11 3.5z" fill="currentColor"/>
          </svg>
          <span>Divina Providencia</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-5v-7H9v7H5a2 2 0 0 1-2-2z"/>
          </svg>
          <span>Pacientes</span>
        </router-link>

        <router-link v-if="rolId !== 5" to="/personal" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Personal</span>
        </router-link>

        <router-link v-if="rolId === 1" to="/turnos" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>Turnos</span>
        </router-link>

        <router-link v-if="rolId === 1 || rolId === 3" to="/actividades" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <span>Actividades</span>
        </router-link>

        <router-link v-if="rolId === 1 || rolId === 3" to="/citas" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <span>Citas</span>
        </router-link>

        <router-link v-if="rolId === 1 || rolId === 3" to="/asistencias" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <span>Asistencias</span>
        </router-link>

        <router-link to="/dietas" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 13c-2.33 0-4.31-1.46-5.11-3.5h10.22c-.8 2.04-2.78 3.5-5.11 3.5z" fill="currentColor"/>
          </svg>
          <span>Dietas</span>
        </router-link>

        <router-link v-if="rolId === 1" to="/usuarios" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Usuarios</span>
        </router-link>

        <!-- Reporte de Asistencias (solo administrador) -->
        <router-link v-if="rolId === 1" to="/reporte-asistencias" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          <span>Reporte Asistencias</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button @click="logout" class="logout-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Cerrar Sesión</span>
        </button>
        <div class="user-info" v-if="usuario">
          <span class="user-name">{{ usuario.nombre }}</span>
          <span class="user-email">{{ usuario.email }}</span>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <router-view />
    </main>

    <!-- Botón flotante de asistencia (global) -->
    <button v-if="personalIdGlobal" class="btn-flotante" @click="abrirAsistenciaGlobal">
      ⏱️ Marcar Asistencia
    </button>
  </div>

  <router-view v-else />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Swal from 'sweetalert2'

const token = ref(localStorage.getItem('token'))
const usuario = ref(null)
const rolId = ref(null)
const personalIdGlobal = ref(null)
const router = useRouter()

const isAuthenticated = computed(() => !!token.value)

onMounted(() => {
  const userStr = localStorage.getItem('usuario')
  if (userStr && userStr !== 'undefined') {
    try {
      usuario.value = JSON.parse(userStr)
      rolId.value = usuario.value.rol_id
      personalIdGlobal.value = usuario.value.personal_id || localStorage.getItem('personal_id') || null
      console.log('personal_id desde localStorage:', localStorage.getItem('personal_id')); // Para depurar
    } catch (error) {
      console.error(error)
      logout()
    }
  } else {
    personalIdGlobal.value = localStorage.getItem('personal_id') || null
  }
})

const abrirAsistenciaGlobal = async () => {
  if (!personalIdGlobal.value) {
    Swal.fire('Error', 'No tienes un empleado asociado. Contacta al administrador.', 'error')
    return
  }
  const hoy = new Date().toISOString().slice(0,10)
  try {
    const res = await axios.get(`/api/asistencia?fecha=${hoy}&personal_id=${personalIdGlobal.value}`)
    const asistencia = res.data
    const entradaRegistrada = asistencia && asistencia.hora_entrada
    const salidaRegistrada = asistencia && asistencia.hora_salida

    let mensaje = `Fecha: ${hoy}<br>`
    mensaje += `Entrada: ${asistencia?.hora_entrada || 'No registrada'}<br>`
    mensaje += `Salida: ${asistencia?.hora_salida || 'No registrada'}<br>`
    if (asistencia?.horas_trabajadas) mensaje += `Horas trabajadas: ${asistencia.horas_trabajadas}`

    if (!entradaRegistrada) {
      const result = await Swal.fire({
        title: 'Registrar Entrada',
        html: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Marcar Entrada',
        cancelButtonText: 'Cancelar'
      })
      if (result.isConfirmed) {
        await axios.post('/api/asistencia/entrada', { personal_id: personalIdGlobal.value, fecha: hoy })
        Swal.fire('Éxito', 'Entrada registrada correctamente', 'success')
      }
    } else if (!salidaRegistrada) {
      const result = await Swal.fire({
        title: 'Registrar Salida',
        html: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Marcar Salida',
        cancelButtonText: 'Cancelar'
      })
      if (result.isConfirmed) {
        await axios.post('/api/asistencia/salida', { personal_id: personalIdGlobal.value, fecha: hoy })
        Swal.fire('Éxito', 'Salida registrada correctamente', 'success')
      }
    } else {
      Swal.fire('Info', 'Ya has registrado entrada y salida hoy.', 'info')
    }
  } catch (err) {
    Swal.fire('Error', err.response?.data?.error || 'Error al consultar asistencia', 'error')
  }
}

const logout = () => {
  localStorage.clear()
  delete axios.defaults.headers.common['Authorization']
  token.value = null
  router.push('/login')
}
</script>


<style>
/* ESTILOS GLOBALES MEJORADOS */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f4f7fc;
}

/* Layout principal */
.app-layout {
  display: flex;
  min-height: 100vh;
}

/* Sidebar moderno */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1e2b3c 0%, #0f1a24 100%);
  color: #e2e8f0;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  transition: all 0.3s ease;
  z-index: 10;
  box-shadow: 2px 0 12px rgba(0,0,0,0.08);
}

.sidebar-header {
  padding: 32px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.25rem;
  font-weight: 600;
  color: white;
}

.logo svg {
  color: #a78bfa;
}

.sidebar-nav {
  flex: 1;
  padding: 24px 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  border-radius: 12px;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s ease;
  font-weight: 500;
  font-size: 0.9rem;
}

.nav-item:hover {
  background: rgba(255,255,255,0.08);
  color: white;
  transform: translateX(4px);
}

.nav-item.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.nav-item svg {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
}

.sidebar-footer {
  padding: 20px 16px;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  color: #fca5a5;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
  margin-bottom: 16px;
}

.logout-btn:hover {
  background: rgba(220,38,38,0.2);
  border-color: #ef4444;
  color: #fecaca;
}

.user-info {
  text-align: center;
  padding-top: 12px;
}

.user-name {
  display: block;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.user-email {
  display: block;
  font-size: 0.7rem;
  color: #94a3b8;
}

/* Contenido principal */
.main-content {
  flex: 1;
  margin-left: 280px;
  padding: 28px;
  background: #f4f7fc;
  min-height: 100vh;
}

/* Botón flotante moderno */
.btn-flotante {
  position: fixed;
  bottom: 30px;
  right: 30px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 12px 24px;
  font-size: 1rem;
  font-weight: bold;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  cursor: pointer;
  z-index: 1000;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-flotante:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 20px rgba(102,126,234,0.4);
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 80px;
  }
  .sidebar-header span,
  .nav-item span,
  .logout-btn span,
  .user-info {
    display: none;
  }
  .nav-item {
    justify-content: center;
  }
  .main-content {
    margin-left: 80px;
  }
  .btn-flotante {
    padding: 8px 16px;
    font-size: 0.8rem;
  }
}

/* Ajustes para tarjetas y tablas (puedes agregarlos en cada vista) */
.card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -8px rgba(0,0,0,0.1);
}
</style>
