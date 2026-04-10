<template>
  <div class="container">
    <div class="page-header">
      <h1>Usuarios del Sistema</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nuevo Usuario</button>
    </div>

    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando usuarios...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarUsuarios" class="btn-retry">Reintentar</button>
    </div>

    <div v-else>
      <div v-if="usuarios.length === 0" class="empty-state">
        <p>📭 No hay usuarios registrados.</p>
      </div>
      <div v-else class="table-responsive">
        <table class="users-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in usuarios" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.nombre }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.rol_nombre }}</td>
              <td>
                <span :class="['status-badge', user.activo ? 'activo' : 'inactivo']">
                  {{ user.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="actions">
                <button @click="editar(user)" class="btn-edit">Editar</button>
                <button @click="confirmarEliminar(user.id)" class="btn-delete" :disabled="user.id === 1">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal de usuario -->
    <UsuarioForm
      :show="mostrarForm"
      :usuario="usuarioSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import UsuarioForm from '@/components/UsuarioForm.vue'
import Swal from 'sweetalert2'

const usuarios = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const usuarioSeleccionado = ref(null)
const modoEdicion = ref(false)

const cargarUsuarios = async () => {
  cargando.value = true
  error.value = false
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('/api/usuarios', {
      headers: { Authorization: `Bearer ${token}` }
    })
    usuarios.value = response.data
  } catch (err) {
    console.error(err)
    error.value = true
    errorMessage.value = err.response?.data?.error || err.message
  } finally {
    cargando.value = false
  }
}

const abrirFormulario = () => {
  usuarioSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (user) => {
  usuarioSeleccionado.value = { ...user }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  usuarioSeleccionado.value = null
}

const recargarLista = () => {
  cargarUsuarios()
}

const confirmarEliminar = (id) => {
  if (id === 1) {
    Swal.fire('Advertencia', 'No se puede eliminar al administrador principal', 'warning')
    return
  }
  Swal.fire({
    title: '¿Eliminar usuario?',
    text: 'Esta acción no se puede revertir',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const token = localStorage.getItem('token')
        await axios.delete(`/api/usuarios/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        })
        Swal.fire('Eliminado', 'Usuario eliminado correctamente', 'success')
        cargarUsuarios()
      } catch (err) {
        Swal.fire('Error', err.response?.data?.error || 'No se pudo eliminar', 'error')
      }
    }
  })
}

onMounted(() => {
  cargarUsuarios()
})
</script>

<style scoped>
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 10px 20px; border: none; border-radius: 40px; cursor: pointer; }
.table-responsive { overflow-x: auto; background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.users-table { width: 100%; border-collapse: collapse; }
.users-table th, .users-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
.users-table th { background: #f8fafc; font-weight: 600; color: #1e293b; }
.status-badge { padding: 4px 10px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; }
.status-badge.activo { background: #dcfce7; color: #15803d; }
.status-badge.inactivo { background: #fee2e2; color: #b91c1c; }
.actions { display: flex; gap: 8px; }
.btn-edit, .btn-delete { padding: 4px 12px; border: none; border-radius: 20px; cursor: pointer; }
.btn-edit { background: #e0e7ff; color: #4338ca; }
.btn-delete { background: #fee2e2; color: #b91c1c; }
.loading-state, .error-state, .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 24px; }
.spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
