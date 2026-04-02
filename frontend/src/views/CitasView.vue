<template>
  <div class="container">
    <div class="page-header">
      <h1>Citas Médicas</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nueva Cita</button>
    </div>

    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando citas...</p>
    </div>
    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarCitas" class="btn-retry">Reintentar</button>
    </div>
    <div v-else-if="citas.length === 0" class="empty-state">
      <p>📭 No hay citas programadas.</p>
      <button @click="abrirFormulario()" class="btn-primary">Agendar la primera</button>
    </div>
    <div v-else class="cards-grid">
      <div v-for="cita in citas" :key="cita.id" class="cita-card">
        <div class="card-header">
          <h3>{{ cita.paciente_nombres }} {{ cita.paciente_apellidos }}</h3>
          <span :class="['estado-badge', cita.estado.toLowerCase()]">{{ cita.estado }}</span>
        </div>
        <div class="card-body">
          <p><strong>Personal:</strong> {{ cita.personal_nombres }} {{ cita.personal_apellidos }}</p>
          <p><strong>Fecha:</strong> {{ formatFecha(cita.fecha) }} a las {{ cita.hora }}</p>
          <p><strong>Motivo:</strong> {{ cita.motivo || 'No especificado' }}</p>
          <p v-if="cita.observaciones"><strong>Observaciones:</strong> {{ cita.observaciones }}</p>
        </div>
        <div class="card-actions">
          <button @click="editar(cita)" class="btn-edit">Editar</button>
          <button @click="confirmarEliminar(cita.id)" class="btn-delete">Eliminar</button>
        </div>
      </div>
    </div>

    <CitaForm
      :show="mostrarForm"
      :cita="citaSeleccionada"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import CitaForm from '@/components/CitaForm.vue'
import Swal from 'sweetalert2'

const citas = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const citaSeleccionada = ref(null)
const modoEdicion = ref(false)

onMounted(() => {
  cargarCitas()
})

const cargarCitas = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/citas')
    citas.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

const formatFecha = (fecha) => {
  if (!fecha) return ''
  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

const abrirFormulario = () => {
  citaSeleccionada.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (cita) => {
  citaSeleccionada.value = { ...cita }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  citaSeleccionada.value = null
}

const recargarLista = () => {
  cargarCitas()
}

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
        await axios.delete(`/api/citas/${id}`)
        Swal.fire('Eliminado', 'La cita ha sido eliminada', 'success')
        cargarCitas()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar la cita', 'error')
      }
    }
  })
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
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 20px;
}
.cita-card {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}
.cita-card:hover {
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
.estado-badge {
  padding: 4px 10px;
  border-radius: 40px;
  font-size: 0.7rem;
  font-weight: 600;
}
.estado-badge.programada { background: #fff3e3; color: #b45309; }
.estado-badge.confirmada { background: #e0e7ff; color: #4338ca; }
.estado-badge.atendida { background: #dcfce7; color: #15803d; }
.estado-badge.cancelada { background: #fee2e2; color: #b91c1c; }
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
.btn-edit, .btn-delete {
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
