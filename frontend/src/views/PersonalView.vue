<template>
  <div class="container">
    <div class="page-header">
      <h1>Personal</h1>
      <button @click="abrirFormulario()" class="btn-primary">+ Nuevo Personal</button>
    </div>

    <div v-if="cargando" class="loading-state">
      <div class="spinner"></div>
      <p>Cargando personal...</p>
    </div>
    <div v-else-if="error" class="error-state">
      <p>❌ {{ errorMessage }}</p>
      <button @click="cargarPersonal" class="btn-retry">Reintentar</button>
    </div>
    <div v-else-if="personal.length === 0" class="empty-state">
      <p>📭 No hay personal registrado.</p>
      <button @click="abrirFormulario()" class="btn-primary">Agregar el primero</button>
    </div>
    <div v-else class="cards-grid">
      <div v-for="persona in personal" :key="persona.id" class="personal-card">
        <div class="card-header">
          <h3>{{ persona.nombres }} {{ persona.apellidos }}</h3>
          <span :class="['status-badge', persona.activo ? 'activo' : 'inactivo']">
            {{ persona.activo ? 'Activo' : 'Inactivo' }}
          </span>
        </div>
        <div class="card-body">
          <p><strong>Documento:</strong> {{ persona.documento_identidad || 'N/A' }}</p>
          <p><strong>Cargo:</strong> {{ persona.cargo }}</p>
          <p><strong>Especialidad:</strong> {{ persona.especialidad || 'N/A' }}</p>
          <p><strong>Teléfono:</strong> {{ persona.telefono || 'N/A' }}</p>
          <p><strong>Email:</strong> {{ persona.email || 'N/A' }}</p>
        </div>
        <div class="card-actions">
          <button @click="editar(persona)" class="btn-edit">Editar</button>
          <button @click="confirmarEliminar(persona.id)" class="btn-delete">Eliminar</button>
        </div>
      </div>
    </div>

    <PersonalForm
      :show="mostrarForm"
      :personal="personalSeleccionado"
      :isEditing="modoEdicion"
      @close="cerrarFormulario"
      @saved="recargarLista"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import PersonalForm from '@/components/PersonalForm.vue'
import Swal from 'sweetalert2'

const personal = ref([])
const cargando = ref(true)
const error = ref(false)
const errorMessage = ref('')
const mostrarForm = ref(false)
const personalSeleccionado = ref(null)
const modoEdicion = ref(false)

onMounted(() => {
  cargarPersonal()
})

const cargarPersonal = async () => {
  cargando.value = true
  try {
    const response = await axios.get('/api/personal')
    personal.value = response.data
    error.value = false
  } catch (err) {
    error.value = true
    errorMessage.value = err.response?.data?.message || err.message
  } finally {
    cargando.value = false
  }
}

const abrirFormulario = () => {
  personalSeleccionado.value = null
  modoEdicion.value = false
  mostrarForm.value = true
}

const editar = (persona) => {
  personalSeleccionado.value = { ...persona }
  modoEdicion.value = true
  mostrarForm.value = true
}

const cerrarFormulario = () => {
  mostrarForm.value = false
  personalSeleccionado.value = null
}

const recargarLista = () => {
  cargarPersonal()
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
        await axios.delete(`/api/personal/${id}`)
        Swal.fire('Eliminado', 'El personal ha sido eliminado', 'success')
        cargarPersonal()
      } catch (error) {
        console.error('Error al eliminar:', error)
        Swal.fire('Error', error.response?.data?.error || 'No se pudo eliminar el personal', 'error')
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
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}
.personal-card {
  background: white;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.2s;
  border: 1px solid #e2e8f0;
}
.personal-card:hover {
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
