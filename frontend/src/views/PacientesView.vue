<template>
  <div class="container">
    <div class="page-header">
      <h1>Pacientes</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nuevo Paciente</button>
    </div>

    <!-- Estados de carga y error -->
    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando pacientes...</p>
    </div>
    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarPacientes" class="btn-retry">Reintentar</button>
    </div>
    <div v-else-if="pacientes.length === 0" class="empty-state">
      <p>📭 No hay pacientes registrados.</p>
      <button @click="abrirFormulario()" class="btn-primary">Crear el primero</button>
    </div>
    <div v-else class="cards-grid">
      <div v-for="paciente in pacientes" :key="paciente.id" class="patient-card">
        <div class="card-header">
          <h3>{{ paciente.nombres }} {{ paciente.apellidos }}</h3>
          <span :class="['status-badge', paciente.estado.toLowerCase()]">{{ paciente.estado }}</span>
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

// Estado reactivo
const pacientes = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const pacienteSeleccionado = ref(null)
const modoEdicion = ref(false)

const router = useRouter()

// Cargar pacientes al montar el componente
onMounted(() => {
  cargarPacientes()
})

// Obtener lista de pacientes desde el backend
const cargarPacientes = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/pacientes')
    pacientes.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

// Abrir formulario para crear nuevo paciente
const abrirFormulario = () => {
  pacienteSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

// Abrir formulario para editar paciente existente
const editar = (paciente) => {
  pacienteSeleccionado.value = { ...paciente }
  modoEdicion.value = true
  mostrarForm.value = true
}

// Cerrar formulario
const cerrarFormulario = () => {
  mostrarForm.value = false
  pacienteSeleccionado.value = null
}

// Recargar lista después de guardar
const recargarLista = () => {
  cargarPacientes()
}

// Confirmar y eliminar paciente
const confirmarEliminar = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción no se puede revertir",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.delete(`/api/pacientes/${id}`)
        Swal.fire('Eliminado', 'El paciente ha sido eliminado', 'success')
        cargarPacientes()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar el paciente', 'error')
      }
    }
  })
}

// Ver detalles completos del paciente
const verDetalles = (id) => {
  router.push(`/pacientes/${id}`)
}
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

h1 {
  font-size: 1.8rem;
  color: #1e293b;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 40px;
  cursor: pointer;
  font-weight: 600;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102,126,234,0.3);
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.patient-card {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}

.patient-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -12px rgba(0,0,0,0.15);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.card-header h3 {
  font-size: 1.2rem;
  color: #1e293b;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 40px;
  font-size: 0.7rem;
  font-weight: 600;
}

.status-badge.activo { background: #dcfce7; color: #15803d; }
.status-badge.inactivo { background: #fee2e2; color: #b91c1c; }
.status-badge.trasladado { background: #fff3e3; color: #b45309; }
.status-badge.fallecido { background: #e2e3e5; color: #4b5563; }

.card-body {
  margin: 16px 0;
  font-size: 0.85rem;
  color: #475569;
}

.card-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.btn-edit, .btn-view, .btn-delete {
  padding: 6px 14px;
  border: none;
  border-radius: 40px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-edit { background: #e0e7ff; color: #4338ca; }
.btn-edit:hover { background: #c7d2fe; }

.btn-view { background: #e6f4ea; color: #2e7d32; }
.btn-view:hover { background: #c8e6c9; }

.btn-delete { background: #fee2e2; color: #b91c1c; }
.btn-delete:hover { background: #fecaca; }

.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 24px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
