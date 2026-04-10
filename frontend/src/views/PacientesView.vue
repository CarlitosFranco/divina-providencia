<template>
  <div class="container">
    <div class="page-header">
      <h1>Pacientes</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nuevo Paciente</button>
    </div>

    <!-- Estado de carga -->
    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando pacientes...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarPacientes" class="btn-retry">Reintentar</button>
    </div>

    <!-- Lista de pacientes -->
    <div v-else>
      <div v-if="pacientes.length === 0" class="empty-state">
        <p>📭 No hay pacientes registrados.</p>
        <button @click="abrirFormulario()" class="btn-primary">Crear el primero</button>
      </div>
      <div v-else class="cards-grid">
        <div v-for="paciente in pacientes" :key="paciente.id" class="patient-card">
          <div class="card-header">
            <h3>{{ paciente.nombres }} {{ paciente.apellidos }}</h3>
            <span :class="['status-badge', (paciente.estado || 'activo').toLowerCase()]">
              {{ paciente.estado || 'Activo' }}
            </span>
          </div>
          <div class="card-body">
            <p><strong>Documento:</strong> {{ paciente.documento_identidad || 'N/A' }}</p>
            <p><strong>Teléfono:</strong> {{ paciente.telefono || 'N/A' }}</p>
          </div>
          <div class="card-actions">
            <button @click="editar(paciente)" class="btn-edit">Editar</button>
            <button @click="verDetalles(paciente.id)" class="btn-view">Ver detalles</button>
            <button @click="confirmarEliminar(paciente.id)" class="btn-delete">Eliminar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de formulario -->
    <PacienteForm
      :show="mostrarForm"
      :paciente="pacienteSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import PacienteForm from '@/components/PacienteForm.vue'
import Swal from 'sweetalert2'

// Estados
const pacientes = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const pacienteSeleccionado = ref(null)
const modoEdicion = ref(false)

const router = useRouter()

// Obtener token
const obtenerToken = () => localStorage.getItem('token')

// Cargar pacientes
const cargarPacientes = async () => {
  cargando.value = true
  error.value = false

  try {
    const token = obtenerToken()
    if (!token) throw new Error('No hay token de autenticación')

    const response = await axios.get('/api/pacientes', {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (Array.isArray(response.data)) {
      pacientes.value = response.data
    } else {
      pacientes.value = []
    }
  } catch (err) {
    console.error(err)
    error.value = true
    errorMessage.value = err.response?.data?.error || err.message
  } finally {
    cargando.value = false
  }
}

// Formulario
const abrirFormulario = () => {
  pacienteSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (paciente) => {
  pacienteSeleccionado.value = { ...paciente }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  pacienteSeleccionado.value = null
}

const recargarLista = () => {
  cargarPacientes()
}

// Eliminar
const confirmarEliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción no se puede revertir',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const token = obtenerToken()
        await axios.delete(`/api/pacientes/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        })
        Swal.fire('Eliminado', 'El paciente ha sido eliminado', 'success')
        cargarPacientes()
      } catch (error) {
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar', 'error')
      }
    }
  })
}

// Ver detalles
const verDetalles = (id) => {
  router.push(`/pacientes/${id}`)
}

onMounted(() => {
  cargarPacientes()
})
</script>

<style scoped>
/* Tus estilos (los que ya tenías) */
.container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
h1 { font-size: 1.8rem; color: #1e293b; }
.btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 10px 20px; border: none; border-radius: 40px; cursor: pointer; font-weight: 600; transition: all 0.2s; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,0.3); }
.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
.patient-card { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: all 0.2s; border: 1px solid #e2e8f0; }
.patient-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -12px rgba(0,0,0,0.15); }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.card-header h3 { font-size: 1.2rem; color: #1e293b; }
.status-badge { padding: 4px 10px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; }
.status-badge.activo { background: #dcfce7; color: #15803d; }
.status-badge.inactivo { background: #fee2e2; color: #b91c1c; }
.status-badge.trasladado { background: #fff3e3; color: #b45309; }
.status-badge.fallecido { background: #e2e3e5; color: #4b5563; }
.card-body { margin: 16px 0; font-size: 0.85rem; color: #475569; }
.card-actions { display: flex; gap: 12px; justify-content: flex-end; }
.btn-edit, .btn-view, .btn-delete { padding: 6px 14px; border: none; border-radius: 40px; font-size: 0.8rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn-edit { background: #e0e7ff; color: #4338ca; }
.btn-edit:hover { background: #c7d2fe; }
.btn-view { background: #e6f4ea; color: #2e7d32; }
.btn-view:hover { background: #c8e6c9; }
.btn-delete { background: #fee2e2; color: #b91c1c; }
.btn-delete:hover { background: #fecaca; }
.loading-state, .error-state, .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 24px; }
.spinner { width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: #667eea; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
.btn-retry { margin-top: 16px; background: #667eea; color: white; border: none; padding: 8px 20px; border-radius: 40px; cursor: pointer; }
</style>
