<template>
  <div v-if="token" class="app-layout">
    <!-- Sidebar (menú izquierdo) -->
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

        <router-link to="/personal" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span>Personal</span>
        </router-link>

        <router-link to="/turnos" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>Turnos</span>
        </router-link>

        <router-link to="/actividades" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
          <span>Actividades</span>
        </router-link>

        <router-link to="/citas" class="nav-item" active-class="active">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          <span>Citas</span>
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

    <!-- Contenido principal -->
    <main class="main-content">
      <router-view />
    </main>
  </div>

  <!-- Pantalla de login (sin sidebar) -->
  <router-view v-else />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const token = ref(localStorage.getItem('token'))
const usuario = ref(null)
const router = useRouter()

onMounted(() => {
  const userStr = localStorage.getItem('usuario')
  if (userStr) {
    usuario.value = JSON.parse(userStr)
  }
})

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('usuario')
  delete axios.defaults.headers.common['Authorization']
  token.value = false
  router.push('/login')
}
</script>

<style>
/* Estilos globales */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  background: #f1f5f9;
}

/* Layout principal */
.app-layout {
  display: flex;
  min-height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: #e2e8f0;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  transition: all 0.3s ease;
  z-index: 10;
}

.sidebar-header {
  padding: 32px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.2rem;
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
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s ease;
  font-weight: 500;
}

.nav-item:hover {
  background: rgba(255,255,255,0.1);
  color: white;
}

.nav-item.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  box-shadow: 0 4px 12px rgba(102,126,234,0.3);
}

.nav-item svg {
  flex-shrink: 0;
}

.sidebar-footer {
  padding: 24px;
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
  margin-bottom: 20px;
}

.logout-btn:hover {
  background: rgba(220,38,38,0.2);
  border-color: #ef4444;
  color: #fecaca;
}

.user-info {
  text-align: center;
  padding-top: 12px;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.user-name {
  display: block;
  font-weight: 600;
  color: white;
  margin-bottom: 4px;
}

.user-email {
  display: block;
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Contenido principal */
.main-content {
  flex: 1;
  margin-left: 280px;
  padding: 24px;
  background: #f1f5f9;
  min-height: 100vh;
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
  /* Ajustes para FullCalendar */
.fc {
  font-family: 'Inter', system-ui, sans-serif;
}
.fc-toolbar-title {
  font-size: 1.4rem;
  font-weight: 600;
  color: #1e293b;
}
.fc-button-primary {
  background: #f1f5f9 !important;
  border-color: #cbd5e1 !important;
  color: #1e293b !important;
  text-transform: capitalize;
}
.fc-button-primary:hover {
  background: #e2e8f0 !important;
}
.fc-button-active {
  background: #667eea !important;
  border-color: #667eea !important;
  color: white !important;
}
.fc-event {
  border-radius: 12px;
  padding: 4px 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
}
.fc-day-today {
  background: rgba(102,126,234,0.05) !important;
}
}
</style>
