<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <div class="logo">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 13c-2.33 0-4.31-1.46-5.11-3.5h10.22c-.8 2.04-2.78 3.5-5.11 3.5z" fill="currentColor"/>
          </svg>
        </div>
        <h1>Divina Providencia</h1>
        <p>Casa de Reposo</p>
      </div>

      <form @submit.prevent="login" class="login-form">
        <div class="input-group">
          <label for="email">Correo electrónico</label>
          <div class="input-icon">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="admin@divinaprovidencia.com"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <div class="input-group">
          <label for="password">Contraseña</label>
          <div class="input-icon">
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
          </div>
        </div>

        <button type="submit" class="btn-login" :disabled="cargando">
          <span v-if="cargando" class="spinner"></span>
          <span v-else>Iniciar Sesión</span>
        </button>

        <div v-if="error" class="error-message">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          {{ error }}
        </div>
      </form>

      <div class="login-footer">
        <p>Sistema de Gestión de Pacientes</p>
        <p class="version">v1.0</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const email = ref('')
const password = ref('')
const error = ref('')
const cargando = ref(false)
const router = useRouter()

const login = async () => {
  cargando.value = true
  error.value = ''

  try {
    // Usa la URL absoluta (como está) o cambia a '/api/login' si usas proxy
    const response = await axios.post('http://localhost/divina-providencia/backend/login', {
      email: email.value,
      password: password.value
    })

    const { token, usuario } = response.data
    localStorage.setItem('token', token)
    localStorage.setItem('usuario', JSON.stringify(usuario))
    localStorage.setItem('rol_id', usuario.rol_id)   // 👈 Guardar rol
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.error || 'Error al iniciar sesión'
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
/* (tus estilos se mantienen igual) */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.login-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  width: 100%;
  max-width: 440px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.login-card:hover {
  transform: translateY(-5px);
}

.login-header {
  text-align: center;
  padding: 40px 32px 24px;
  background: linear-gradient(to bottom, #f8fafc, white);
}

.logo {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
  color: #667eea;
}

.login-header h1 {
  font-size: 28px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 8px;
}

.login-header p {
  color: #64748b;
  font-size: 14px;
}

.login-form {
  padding: 0 32px 32px;
}

.input-group {
  margin-bottom: 24px;
}

.input-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #334155;
  margin-bottom: 8px;
}

.input-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.icon {
  position: absolute;
  left: 12px;
  color: #94a3b8;
  pointer-events: none;
}

.input-icon input {
  width: 100%;
  padding: 12px 12px 12px 40px;
  font-size: 15px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  transition: all 0.3s ease;
  outline: none;
  font-family: inherit;
}

.input-icon input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-icon input::placeholder {
  color: #cbd5e1;
}

.btn-login {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.4);
}

.btn-login:active:not(:disabled) {
  transform: translateY(0);
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  margin-top: 20px;
  padding: 12px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  color: #dc2626;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.login-footer {
  padding: 20px 32px 32px;
  text-align: center;
  border-top: 1px solid #f1f5f9;
  font-size: 12px;
  color: #94a3b8;
}

.login-footer p:first-child {
  margin-bottom: 4px;
}

.version {
  font-size: 11px;
  opacity: 0.7;
}

@media (max-width: 480px) {
  .login-card {
    border-radius: 20px;
  }
  .login-header {
    padding: 32px 24px 20px;
  }
  .login-form {
    padding: 0 24px 28px;
  }
  .login-footer {
    padding: 20px 24px 28px;
  }
}
</style>
